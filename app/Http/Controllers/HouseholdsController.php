<?php

namespace App\Http\Controllers;

use App\Support\TdltsBarcodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseholdsController extends Controller
{
    private function findHouseholdByRef(string $ref): ?object
    {
        return DB::table('households')
            ->where('barcode', $ref)
            ->orWhere('household_id', $ref)
            ->orWhere('id', is_numeric($ref) ? (int) $ref : 0)
            ->first();
    }

    private function findPatientByRef(string $patientRef): ?object
    {
        return DB::table('patients')
            ->where('patient_id', $patientRef)
            ->orWhere('barcode', $patientRef)
            ->orWhere('id', is_numeric($patientRef) ? (int) $patientRef : 0)
            ->first();
    }

    private function patientBelongsToHousehold(object $patientRow, object $householdRow): bool
    {
        $patientHouseholdId = (string) ($patientRow->household_id ?? '');
        $householdId = (string) ($householdRow->household_id ?? '');

        if ($patientHouseholdId !== '' && $patientHouseholdId === $householdId) {
            return true;
        }

        $patientHead = mb_strtolower(trim((string) ($patientRow->household_head_of_house ?? '')));
        $householdHead = mb_strtolower(trim((string) ($householdRow->head_of_house ?? '')));

        return $patientHead !== '' && $householdHead !== '' && $patientHead === $householdHead;
    }

    private function generatePatientId(): string
    {
        do {
            $candidate = TdltsBarcodeGenerator::generate('P');
        } while (DB::table('patients')->where('patient_id', $candidate)->exists());

        return $candidate;
    }

    private function rebalanceHouseholdHead(string $householdId, ?int $preferredHeadDbId = null): void
    {
        $members = DB::table('patients')
            ->where('household_id', $householdId)
            ->orderByRaw("CASE WHEN lower(relationship_to_head) = 'head' THEN 0 ELSE 1 END")
            ->orderBy('source_created_at')
            ->orderBy('id')
            ->get();

        if ($members->isEmpty()) {
            DB::table('households')
                ->where('household_id', $householdId)
                ->update([
                    'head_of_house' => null,
                    'updated_at' => now(),
                ]);

            return;
        }

        $selectedHead = null;
        if ($preferredHeadDbId !== null) {
            $selectedHead = $members->firstWhere('id', $preferredHeadDbId);
        }

        if (!$selectedHead) {
            $selectedHead = $members->first();
        }

        $headName = trim((string) ($selectedHead->full_name ?? ''));
        if ($headName === '') {
            $headName = 'Unknown';
        }

        DB::table('households')
            ->where('household_id', $householdId)
            ->update([
                'head_of_house' => $headName,
                'updated_at' => now(),
            ]);

        DB::table('patients')
            ->where('household_id', $householdId)
            ->update([
                'relationship_to_head' => 'Member',
                'household_head_of_house' => $headName,
                'updated_at' => now(),
            ]);

        DB::table('patients')
            ->where('id', $selectedHead->id)
            ->update([
                'relationship_to_head' => 'Head',
                'updated_at' => now(),
            ]);
    }

    public function storeMember(Request $request, string $ref)
    {
        $householdRow = $this->findHouseholdByRef($ref);

        if (!$householdRow) {
            return redirect()->route('households.index')->withErrors([
                'household' => 'Household details were not found for the selected row.',
            ]);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:200',
            'gender' => 'nullable|in:Male,Female,male,female',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'nullable|string|max:30',
            'nrc_number' => 'nullable|string|max:50',
            'relationship_to_head' => 'nullable|in:Head,Member',
        ]);

        $householdId = (string) ($householdRow->household_id ?? '');
        $relationship = (string) ($validated['relationship_to_head'] ?? 'Member');
        $memberDbId = null;

        DB::transaction(function () use ($validated, $householdId, $relationship, &$memberDbId) {
            $patientId = $this->generatePatientId();

            $memberDbId = DB::table('patients')->insertGetId([
                'patient_id' => $patientId,
                'barcode' => TdltsBarcodeGenerator::generate('P', $patientId),
                'full_name' => trim((string) $validated['full_name']),
                'gender' => $validated['gender'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'nrc_number' => $validated['nrc_number'] ?? null,
                'household_id' => $householdId,
                'relationship_to_head' => $relationship,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->rebalanceHouseholdHead($householdId, $relationship === 'Head' ? (int) $memberDbId : null);
        });

        return redirect()
            ->route('households.show', ['ref' => $householdId])
            ->with('success', 'Household member added successfully.');
    }

    public function updateMember(Request $request, string $ref, string $patientRef)
    {
        $householdRow = $this->findHouseholdByRef($ref);

        if (!$householdRow) {
            return redirect()->route('households.index')->withErrors([
                'household' => 'Household details were not found for the selected row.',
            ]);
        }

        $patientRow = $this->findPatientByRef($patientRef);
        if (!$patientRow || !$this->patientBelongsToHousehold($patientRow, $householdRow)) {
            return redirect()
                ->route('households.show', ['ref' => (string) $householdRow->household_id])
                ->withErrors(['household' => 'Selected member was not found in this household.']);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:200',
            'gender' => 'nullable|in:Male,Female,male,female',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'nullable|string|max:30',
            'nrc_number' => 'nullable|string|max:50',
            'relationship_to_head' => 'nullable|in:Head,Member',
        ]);

        $householdId = (string) ($householdRow->household_id ?? '');
        $relationship = (string) ($validated['relationship_to_head'] ?? ($patientRow->relationship_to_head ?: 'Member'));

        DB::transaction(function () use ($validated, $patientRow, $householdId, $relationship) {
            DB::table('patients')
                ->where('id', $patientRow->id)
                ->update([
                    'full_name' => trim((string) $validated['full_name']),
                    'gender' => $validated['gender'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'phone_number' => $validated['phone_number'] ?? null,
                    'nrc_number' => $validated['nrc_number'] ?? null,
                    'household_id' => $householdId,
                    'relationship_to_head' => $relationship,
                    'updated_at' => now(),
                ]);

            $this->rebalanceHouseholdHead($householdId, $relationship === 'Head' ? (int) $patientRow->id : null);
        });

        return redirect()
            ->route('households.show', ['ref' => $householdId])
            ->with('success', 'Household member updated successfully.');
    }

    public function removeMember(Request $request, string $ref, string $patientRef)
    {
        $householdRow = $this->findHouseholdByRef($ref);

        if (!$householdRow) {
            return redirect()->route('households.index')->withErrors([
                'household' => 'Household details were not found for the selected row.',
            ]);
        }

        $patientRow = $this->findPatientByRef($patientRef);
        if (!$patientRow || !$this->patientBelongsToHousehold($patientRow, $householdRow)) {
            return redirect()
                ->route('households.show', ['ref' => (string) $householdRow->household_id])
                ->withErrors(['household' => 'Selected member was not found in this household.']);
        }

        $householdId = (string) ($householdRow->household_id ?? '');

        DB::transaction(function () use ($patientRow, $householdId) {
            DB::table('patients')
                ->where('id', $patientRow->id)
                ->update([
                    'household_id' => null,
                    'relationship_to_head' => null,
                    'household_head_of_house' => null,
                    'updated_at' => now(),
                ]);

            $this->rebalanceHouseholdHead($householdId);
        });

        return redirect()
            ->route('households.show', ['ref' => $householdId])
            ->with('success', 'Member removed from household successfully.');
    }

    public function transferMember(Request $request, string $ref, string $patientRef)
    {
        $householdRow = $this->findHouseholdByRef($ref);

        if (!$householdRow) {
            return redirect()->route('households.index')->withErrors([
                'household' => 'Household details were not found for the selected row.',
            ]);
        }

        $patientRow = $this->findPatientByRef($patientRef);
        if (!$patientRow || !$this->patientBelongsToHousehold($patientRow, $householdRow)) {
            return redirect()
                ->route('households.show', ['ref' => (string) $householdRow->household_id])
                ->withErrors(['household' => 'Selected member was not found in this household.']);
        }

        $validated = $request->validate([
            'target_household_id' => 'required|string|max:100',
            'transfer_as_head' => 'nullable|boolean',
        ]);

        $sourceHouseholdId = (string) ($householdRow->household_id ?? '');
        $targetHouseholdId = trim((string) $validated['target_household_id']);

        if (mb_strtolower($sourceHouseholdId) === mb_strtolower($targetHouseholdId)) {
            return redirect()
                ->route('households.show', ['ref' => $sourceHouseholdId])
                ->withErrors(['household' => 'Target household must be different from the current household.']);
        }

        $targetHousehold = DB::table('households')
            ->where('household_id', $targetHouseholdId)
            ->first();

        if (!$targetHousehold) {
            return redirect()
                ->route('households.show', ['ref' => $sourceHouseholdId])
                ->withErrors(['household' => 'Target household was not found.']);
        }

        $asHead = (bool) ($validated['transfer_as_head'] ?? false);

        DB::transaction(function () use ($patientRow, $sourceHouseholdId, $targetHouseholdId, $asHead) {
            DB::table('patients')
                ->where('id', $patientRow->id)
                ->update([
                    'household_id' => $targetHouseholdId,
                    'relationship_to_head' => $asHead ? 'Head' : 'Member',
                    'updated_at' => now(),
                ]);

            $this->rebalanceHouseholdHead($sourceHouseholdId);
            $this->rebalanceHouseholdHead($targetHouseholdId, $asHead ? (int) $patientRow->id : null);
        });

        return redirect()
            ->route('households.show', ['ref' => $sourceHouseholdId])
            ->with('success', 'Member transferred successfully.');
    }

    public function searchTransferHouseholds(Request $request, string $ref): JsonResponse
    {
        $householdRow = $this->findHouseholdByRef($ref);

        if (!$householdRow) {
            return response()->json(['results' => []]);
        }

        $query = trim((string) $request->input('q', ''));
        $currentHouseholdId = (string) ($householdRow->household_id ?? '');

        $householdsQuery = DB::table('households')
            ->select('household_id', 'head_of_house')
            ->where('household_id', '!=', $currentHouseholdId);

        if ($query !== '') {
            $like = '%' . $query . '%';
            $householdsQuery->where(function ($builder) use ($like) {
                $builder->where('household_id', 'like', $like)
                    ->orWhere('head_of_house', 'like', $like);
            });
        }

        $households = $householdsQuery
            ->orderBy('head_of_house')
            ->orderBy('household_id')
            ->limit(4)
            ->get();

        return response()->json([
            'results' => $households->map(fn ($household) => [
                'id' => (string) ($household->household_id ?? ''),
                'text' => (string) ($household->household_id ?? '') . ' - ' . ((string) ($household->head_of_house ?? '') ?: 'No head set'),
            ])->values(),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeHousehold(array $row): array
    {
        return [
            'id' => (string) ($row['household_id'] ?? ''),
            'householdId' => (string) ($row['household_id'] ?? ''),
            'householdName' => (string) ($row['head_of_house'] ?? '—'),
            'headOfHouseName' => (string) ($row['head_of_house'] ?? '—'),
            'headName' => (string) ($row['head_of_house'] ?? '—'),
            'phoneNumber' => (string) ($row['phone_number'] ?? ''),
            'phone' => (string) ($row['phone_number'] ?? ''),
            'village' => (string) ($row['village'] ?? ''),
            'town' => (string) ($row['town'] ?? ''),
            'householdType' => (string) ($row['household_type'] ?? ''),
            'barcode' => (string) ($row['barcode'] ?? ''),
            'subscriptionPlan' => (string) ($row['subscription_plan'] ?? ''),
            'subscriptionFee' => $row['subscription_fee'] ?? null,
            'paymentMethod' => (string) ($row['payment_method'] ?? ''),
            'paymentStatus' => (string) ($row['payment_status'] ?? 'Active'),
            'status' => (string) ($row['payment_status'] ?? 'Active'),
            'transactionCode' => (string) ($row['transaction_code'] ?? ''),
            'nrcNumber' => (string) ($row['nrc_number'] ?? ''),
            'createdAt' => $row['source_created_at'] ? (string) $row['source_created_at'] : ($row['created_at'] ? (string) $row['created_at'] : null),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeMember(array $row): array
    {
        return [
            'id' => (string) ($row['patient_id'] ?? ''),
            'patientId' => (string) ($row['patient_id'] ?? ''),
            'fullName' => (string) ($row['full_name'] ?? ''),
            'gender' => (string) ($row['gender'] ?? ''),
            'dateOfBirth' => $row['date_of_birth'] ? (string) $row['date_of_birth'] : null,
            'phoneNumber' => (string) ($row['phone_number'] ?? ''),
            'nrcNumber' => (string) ($row['nrc_number'] ?? ''),
            'householdId' => (string) ($row['household_id'] ?? ''),
            'relationshipToHead' => (string) ($row['relationship_to_head'] ?? 'Member'),
            'barcode' => (string) (($row['barcode'] ?? '') ?: ($row['patient_id'] ?? '')),
            'status' => 'Active',
        ];
    }

    public function index(Request $request)
    {
        $search  = trim((string) $request->input('search', ''));
        $limit   = max(1, (int) $request->input('limit', 10));
        $page    = max(1, (int) $request->input('page', 1));

        $error = null;

        $query = DB::table('households');

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('household_id', 'like', $like)
                    ->orWhere('barcode', 'like', $like)
                    ->orWhere('head_of_house', 'like', $like)
                    ->orWhere('phone_number', 'like', $like)
                    ->orWhere('village', 'like', $like)
                    ->orWhere('town', 'like', $like)
                    ->orWhere('payment_status', 'like', $like);
            });
        }

        $total = (clone $query)->count();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($page, $totalPages);

        $rows = $query
            ->orderByDesc('source_created_at')
            ->orderByDesc('id')
            ->forPage($page, $limit)
            ->get()
            ->map(fn ($r) => $this->normalizeHousehold((array) $r))
            ->all();

        $households = $rows;

        $offset = ($page - 1) * $limit;

        $from = $total > 0 ? $offset + 1 : 0;
        $to = $offset + count($households);
        $hasMore = $page < $totalPages;

        return view('households.index', compact(
            'households', 'error', 'search',
            'limit', 'page', 'total', 'totalPages',
            'from', 'to', 'hasMore'
        ));
    }

    public function show(Request $request, string $ref)
    {
        $membersLimit = max(1, (int) $request->input('members_limit', 10));
        $membersPage = max(1, (int) $request->input('members_page', 1));

        $household = null;
        $members = [];
        $error = null;

        $householdRow = DB::table('households')
            ->where('barcode', $ref)
            ->orWhere('household_id', $ref)
            ->orWhere('id', is_numeric($ref) ? (int) $ref : 0)
            ->first();

        if (!$householdRow) {
            return redirect()->route('households.index')->withErrors([
                'household' => 'Household details were not found for the selected row.',
            ]);
        }

        $household = $this->normalizeHousehold((array) $householdRow);
        $householdId = (string) ($household['householdId'] ?? '');
        $headName = trim((string) ($household['headOfHouseName'] ?? ''));

        $members = DB::table('patients')
            ->where('household_id', $householdId)
            ->orderByDesc('source_created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($r) => $this->normalizeMember((array) $r))
            ->all();

        // Imported workbook data can have non-matching household_id values between
        // patients and households; fallback to household head name when needed.
        if ($members === [] && $headName !== '') {
            $members = DB::table('patients')
                ->where('household_head_of_house', $headName)
                ->orderByDesc('source_created_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($r) => $this->normalizeMember((array) $r))
                ->all();
        }

        $membersTotal = count($members);
        $membersTotalPages = max(1, (int) ceil($membersTotal / $membersLimit));
        $membersPage = min($membersPage, $membersTotalPages);
        $membersOffset = ($membersPage - 1) * $membersLimit;
        $pagedMembers = array_slice($members, $membersOffset, $membersLimit);
        $membersFrom = $membersTotal > 0 ? $membersOffset + 1 : 0;
        $membersTo = $membersOffset + count($pagedMembers);

        return view('households.show', compact(
            'household',
            'pagedMembers',
            'membersTotal',
            'membersPage',
            'membersLimit',
            'membersTotalPages',
            'membersFrom',
            'membersTo',
            'error'
        ));
    }
}

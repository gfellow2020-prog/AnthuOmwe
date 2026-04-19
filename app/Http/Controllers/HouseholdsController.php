<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseholdsController extends Controller
{
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
            'householdId' => (string) ($row['household_id'] ?? ''),
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

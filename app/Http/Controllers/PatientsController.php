<?php

namespace App\Http\Controllers;

use App\Support\TdltsBarcodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientsController extends Controller
{
    /**
     * @param array<int, string> $householdIds
     * @param array<int, string> $householdHeads
     * @return array{byId: array<string, array<string, mixed>>, byHead: array<string, array<string, mixed>>}
     */
    private function buildHouseholdLookup(array $householdIds, array $householdHeads = []): array
    {
        $householdIds = array_values(array_unique(array_filter($householdIds, fn ($id) => $id !== '')));
        $householdHeads = array_values(array_unique(array_filter($householdHeads, fn ($head) => $head !== '')));

        if ($householdIds === [] && $householdHeads === []) {
            return ['byId' => [], 'byHead' => []];
        }

        $rows = DB::table('households')
            ->where(function ($q) use ($householdIds, $householdHeads) {
                if ($householdIds !== []) {
                    $q->whereIn('household_id', $householdIds);
                }

                if ($householdHeads !== []) {
                    $q->orWhereIn('head_of_house', $householdHeads);
                }
            })
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        $lookupById = [];
        $lookupByHead = [];

        foreach ($rows as $row) {
            $id = (string) ($row['household_id'] ?? '');
            if ($id !== '') {
                $lookupById[$id] = $row;
            }

            $head = (string) ($row['head_of_house'] ?? '');
            if ($head !== '') {
                $lookupByHead[mb_strtolower($head)] = $row;
            }
        }

        return ['byId' => $lookupById, 'byHead' => $lookupByHead];
    }

    /**
     * @param array<string, mixed> $patient
     * @param array<string, mixed>|null $household
     * @return array<string, mixed>
     */
    private function normalizePatient(array $patient, ?array $household = null): array
    {
        $householdId = (string) ($patient['household_id'] ?? '');
        $phone = (string) ($patient['phone_number'] ?? '');
        if ($phone === '' && is_array($household)) {
            $phone = (string) ($household['phone_number'] ?? '');
        }

        $address = '';
        if (is_array($household)) {
            $address = implode(', ', array_filter([
                $household['village'] ?? null,
                $household['town'] ?? null,
            ]));
        }

        return [
            'id' => (string) ($patient['patient_id'] ?? ''),
            'patientId' => (string) ($patient['patient_id'] ?? ''),
            'fullName' => (string) ($patient['full_name'] ?? ''),
            'gender' => (string) ($patient['gender'] ?? ''),
            'dateOfBirth' => $patient['date_of_birth'] ? (string) $patient['date_of_birth'] : null,
            'nrcNumber' => (string) ($patient['nrc_number'] ?? ''),
            'country' => (string) ($patient['country'] ?? ''),
            'phoneNumber' => $phone,
            'email' => (string) ($patient['email'] ?? ''),
            'otherCellphone' => (string) ($patient['other_cellphone'] ?? ''),
            'landline' => (string) ($patient['landline'] ?? ''),
            'houseNumber' => (string) ($patient['house_number'] ?? ''),
            'roadStreet' => (string) ($patient['road_street'] ?? ''),
            'area' => (string) ($patient['area'] ?? ''),
            'cityTownVillage' => (string) ($patient['city_town_village'] ?? ''),
            'landmarks' => (string) ($patient['landmarks'] ?? ''),
            'maritalStatus' => (string) ($patient['marital_status'] ?? ''),
            'spouseFirstName' => (string) ($patient['spouse_first_name'] ?? ''),
            'spouseSurname' => (string) ($patient['spouse_surname'] ?? ''),
            'homeLanguage' => (string) ($patient['home_language'] ?? ''),
            'bornInZambia' => (string) ($patient['born_in_zambia'] ?? ''),
            'provinceOfBirth' => (string) ($patient['province_of_birth'] ?? ''),
            'districtOfBirth' => (string) ($patient['district_of_birth'] ?? ''),
            'placeOfBirth' => (string) ($patient['place_of_birth'] ?? ''),
            'occupation' => (string) ($patient['occupation'] ?? ''),
            'artNumber' => (string) ($patient['art_number'] ?? ''),
            'nupn' => (string) ($patient['nupn'] ?? ''),
            'bloodGroup' => (string) ($patient['blood_group'] ?? ''),
            'allergies' => (string) ($patient['allergies'] ?? ''),
            'relationshipToHead' => (string) ($patient['relationship_to_head'] ?? ''),
            'householdId' => $householdId,
            'barcode' => (string) (($patient['barcode'] ?? '') ?: ($patient['patient_id'] ?? '')),
            'createdAt' => $patient['source_created_at'] ? (string) $patient['source_created_at'] : ($patient['created_at'] ? (string) $patient['created_at'] : null),
            'status' => 'Active',
            'address' => $address,
        ];
    }

    /**
     * List patients with optional search and pagination.
     */
    public function index(Request $request)
    {
        $search      = trim((string) $request->input('search', ''));
        $limit       = (int) $request->input('limit', 10);
        $page        = (int) $request->input('page', 1);
        $householdId = $request->input('householdId', '');

        $limit = max(1, min($limit, 200));
        $page  = max(1, $page);

        $patients     = [];
        $total        = 0;
        $totalPages   = 1;
        $error        = null;

        $query = DB::table('patients');

        if ($householdId !== '') {
            $query->where('household_id', $householdId);
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('patient_id', 'like', $like)
                    ->orWhere('full_name', 'like', $like)
                    ->orWhere('phone_number', 'like', $like)
                    ->orWhere('nrc_number', 'like', $like)
                    ->orWhere('barcode', 'like', $like)
                    ->orWhere('household_id', 'like', $like);
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
            ->map(fn ($r) => (array) $r)
            ->all();

        $householdLookup = $this->buildHouseholdLookup(
            array_map(
            fn ($row) => (string) ($row['household_id'] ?? ''),
            $rows
            ),
            array_map(
                fn ($row) => (string) ($row['household_head_of_house'] ?? ''),
                $rows
            )
        );

        foreach ($rows as $row) {
            $hid = (string) ($row['household_id'] ?? '');
            $head = mb_strtolower((string) ($row['household_head_of_house'] ?? ''));

            $household = null;
            if ($hid !== '') {
                $household = $householdLookup['byId'][$hid] ?? null;
            }

            if (!$household && $head !== '') {
                $household = $householdLookup['byHead'][$head] ?? null;
            }

            $patients[] = $this->normalizePatient($row, $household);
        }

        $totalIsKnown = true;
        $hasMore = $page < $totalPages;

        $from = count($patients) > 0 ? (($page - 1) * $limit) + 1 : 0;
        $to   = ($page - 1) * $limit + count($patients);

        return view('patients.index', compact(
            'patients', 'total', 'error', 'search',
            'limit', 'page', 'totalPages', 'householdId',
            'from', 'to', 'hasMore', 'totalIsKnown'
        ));
    }

    /**
     * Show a single patient details page.
     */
    public function show(string $ref)
    {
        $patient = null;
        $error = null;

        $row = DB::table('patients')
            ->where('patient_id', $ref)
            ->orWhere('barcode', $ref)
            ->orWhere('id', is_numeric($ref) ? (int) $ref : 0)
            ->first();

        if (!$row) {
            return redirect()->route('patients.index')->withErrors([
                'patient' => 'Patient details were not found for the selected row.',
            ]);
        }

        $patientRow = (array) $row;
        $householdRow = null;

        if (!empty($patientRow['household_id'])) {
            $householdRow = DB::table('households')
                ->where('household_id', (string) $patientRow['household_id'])
                ->first();
        }

        if (!$householdRow && !empty($patientRow['household_head_of_house'])) {
            $householdRow = DB::table('households')
                ->where('head_of_house', (string) $patientRow['household_head_of_house'])
                ->first();
        }

        $household = $householdRow ? [
            'id' => (string) ($householdRow->household_id ?? ''),
            'householdId' => (string) ($householdRow->household_id ?? ''),
            'householdName' => (string) ($householdRow->head_of_house ?? '—'),
            'headOfHouseName' => (string) ($householdRow->head_of_house ?? '—'),
            'barcode' => (string) ($householdRow->barcode ?? ''),
            'phoneNumber' => (string) ($householdRow->phone_number ?? ''),
            'village' => (string) ($householdRow->village ?? ''),
            'town' => (string) ($householdRow->town ?? ''),
        ] : null;

        $patient = $this->normalizePatient($patientRow, $householdRow ? (array) $householdRow : null);
        $patientDbId = $patientRow['id'];

        return view('patients.show', compact('patient', 'household', 'error', 'patientDbId'));
    }

    /**
     * Show the edit form for a patient.
     */
    public function edit(string $ref)
    {
        $row = DB::table('patients')
            ->where('patient_id', $ref)
            ->orWhere('barcode', $ref)
            ->orWhere('id', is_numeric($ref) ? (int) $ref : 0)
            ->first();

        if (!$row) {
            return redirect()->route('patients.index')->withErrors([
                'patient' => 'Patient not found.',
            ]);
        }

        $patient = (array) $row;

        return view('patients.edit', compact('patient'));
    }

    /**
     * Update a patient record.
     */
    public function update(Request $request, string $ref)
    {
        $row = DB::table('patients')
            ->where('patient_id', $ref)
            ->orWhere('barcode', $ref)
            ->orWhere('id', is_numeric($ref) ? (int) $ref : 0)
            ->first();

        if (!$row) {
            return redirect()->route('patients.index')->withErrors([
                'patient' => 'Patient not found.',
            ]);
        }

        $validated = $request->validate([
            'full_name'          => 'required|string|max:200',
            'date_of_birth'      => 'required|date',
            'gender'             => 'required|in:Male,Female',
            'nrc_number'         => 'nullable|string|max:50',
            'country'            => 'nullable|string|max:10',
            'phone_number'       => 'nullable|string|max:30',
            'email'              => 'nullable|email|max:150',
            'other_cellphone'    => 'nullable|string|max:30',
            'landline'           => 'nullable|string|max:30',
            'house_number'       => 'nullable|string|max:50',
            'road_street'        => 'nullable|string|max:100',
            'area'               => 'nullable|string|max:100',
            'city_town_village'  => 'nullable|string|max:100',
            'landmarks'          => 'nullable|string|max:500',
            'marital_status'     => 'nullable|string|max:30',
            'spouse_first_name'  => 'nullable|string|max:100',
            'spouse_surname'     => 'nullable|string|max:100',
            'home_language'      => 'nullable|string|max:50',
            'born_in_zambia'     => 'nullable|string|max:10',
            'province_of_birth'  => 'nullable|string|max:100',
            'district_of_birth'  => 'nullable|string|max:100',
            'place_of_birth'     => 'nullable|string|max:100',
            'occupation'         => 'nullable|string|max:100',
            'art_number'         => 'nullable|string|max:50',
            'nupn'               => 'nullable|string|max:50',
            'blood_group'        => 'nullable|string|max:10',
            'allergies'          => 'nullable|string|max:500',
        ]);

        DB::table('patients')
            ->where('id', $row->id)
            ->update(array_merge($validated, ['updated_at' => now()]));

        return redirect()
            ->route('patients.show', ['ref' => $row->patient_id])
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Show encounter history for a patient.
     */
    public function encounters(string $ref)
    {
        $row = DB::table('patients')
            ->where('patient_id', $ref)
            ->orWhere('barcode', $ref)
            ->orWhere('id', is_numeric($ref) ? (int) $ref : 0)
            ->first();

        if (!$row) {
            return redirect()->route('patients.index')->withErrors([
                'patient' => 'Patient not found.',
            ]);
        }

        $encounters = DB::table('encounters')
            ->where('patient_id', $row->id)
            ->orderByDesc('started_at')
            ->paginate(20);

        $patient = (array) $row;

        return view('patients.encounters', compact('patient', 'encounters'));
    }

    /**
     * Show the patient registration form.
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly registered patient.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'        => 'required|string|max:100',
            'surname'           => 'required|string|max:100',
            'date_of_birth'     => 'required|date',
            'gender'            => 'required|in:Male,Female',
            'nrc_number'        => 'nullable|string|max:50',
            'country'           => 'required|string|max:10',
            'cellphone'         => 'nullable|string|max:30',
            'phone_code'        => 'nullable|string|max:10',
            'email'             => 'nullable|email|max:150',
            'registration_date' => 'required|date',
        ]);

        $fullName = trim($validated['first_name'] . ' ' . $validated['surname']);
        $patientId = 'P-' . strtoupper(substr(md5((string) microtime(true) . random_int(0, 99999)), 0, 8));
        $barcode = TdltsBarcodeGenerator::generate('P', $patientId);

        $phoneNumber = '';
        if (!empty($validated['cellphone'])) {
            $phoneNumber = ($validated['phone_code'] ?? '+260') . ltrim($validated['cellphone'], '0');
        }

        $address = implode(', ', array_filter([
            $request->input('house_number'),
            $request->input('road_street'),
            $request->input('area'),
            $request->input('city_town_village'),
        ]));

        DB::table('patients')->insert([
            'patient_id'             => $patientId,
            'full_name'              => $fullName,
            'gender'                 => $validated['gender'],
            'date_of_birth'          => $validated['date_of_birth'],
            'nrc_number'             => $validated['nrc_number'],
            'country'                => $validated['country'],
            'phone_number'           => $phoneNumber ?: null,
            'email'                  => $request->input('email'),
            'other_cellphone'        => $request->input('other_cellphone'),
            'landline'               => $request->input('landline'),
            'house_number'           => $request->input('house_number'),
            'road_street'            => $request->input('road_street'),
            'area'                   => $request->input('area'),
            'city_town_village'      => $request->input('city_town_village'),
            'landmarks'              => $request->input('landmarks'),
            'marital_status'         => $request->input('marital_status'),
            'spouse_first_name'      => $request->input('spouse_first_name'),
            'spouse_surname'         => $request->input('spouse_surname'),
            'home_language'          => $request->input('home_language'),
            'born_in_zambia'         => $request->input('born_in_zambia'),
            'province_of_birth'      => $request->input('province_of_birth'),
            'district_of_birth'      => $request->input('district_of_birth'),
            'place_of_birth'         => $request->input('place_of_birth'),
            'occupation'             => $request->input('occupation'),
            'art_number'             => $request->input('art_number'),
            'nupn'                   => $request->input('nupn'),
            'blood_group'            => $request->input('blood_group'),
            'allergies'              => $request->input('allergies'),
            'barcode'                => $barcode,
            'source_created_at'      => $validated['registration_date'],
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        return redirect()
            ->route('patients.show', ['ref' => $patientId])
            ->with('success', 'Patient registered successfully.');
    }
}

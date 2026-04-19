<?php

namespace App\Http\Controllers;

use App\Enums\EncounterStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Login successful!');
        }

        return back()->withErrors(['login' => 'Invalid email or password.'])->withInput();
    }

    public function dashboard()
    {
        $totalPatients = DB::table('patients')->count();
        $totalHouseholds = DB::table('households')->count();
        $activePatients = DB::table('patients')->count();

        $todayShiftPatients = (int) DB::table('shift_reports')
            ->whereDate('report_date', now()->toDateString())
            ->sum('total_patients_seen');

        $recentPatients = DB::table('patients')
            ->select([
                'patient_id',
                'full_name',
                'gender',
                'date_of_birth',
                'household_head_of_house',
                'barcode',
                'source_created_at',
            ])
            ->orderByDesc('source_created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $recentShiftReports = DB::table('shift_reports')
            ->select(['report_date', 'shift_type', 'total_patients_seen', 'reported_by', 'source_created_at'])
            ->orderByDesc('source_created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $impactNumbers = DB::table('impact_numbers')
            ->orderBy('metric')
            ->get(['metric', 'value', 'description']);

        // ── Encounter cycle data ─────────────────────────────────────────────
        $encounterStageCounts = DB::table('encounters')
            ->whereNull('deleted_at')
            ->where('current_status', '!=', 'cancelled')
            ->select('current_stage', DB::raw('count(*) as total'))
            ->groupBy('current_stage')
            ->pluck('total', 'current_stage');

        $totalEncounters      = DB::table('encounters')->whereNull('deleted_at')->count();
        $activeEncounters     = DB::table('encounters')
            ->whereNull('deleted_at')
            ->whereNotIn('current_status', ['completed', 'cancelled'])
            ->count();
        $completedEncounters  = DB::table('encounters')
            ->whereNull('deleted_at')
            ->where('current_status', 'completed')
            ->count();

        $recentEncounters = DB::table('encounters as e')
            ->join('patients as p', 'p.id', '=', 'e.patient_id')
            ->whereNull('e.deleted_at')
            ->select([
                'e.id',
                'e.encounter_number',
                'e.current_stage',
                'e.current_status',
                'e.priority_level',
                'e.visit_type',
                'e.started_at',
                'p.full_name',
                'p.patient_id as patient_code',
            ])
            ->orderByDesc('e.started_at')
            ->orderByDesc('e.id')
            ->limit(10)
            ->get();

        return view('auth.dashboard', compact(
            'totalPatients',
            'totalHouseholds',
            'activePatients',
            'todayShiftPatients',
            'recentPatients',
            'recentShiftReports',
            'impactNumbers',
            'encounterStageCounts',
            'totalEncounters',
            'activeEncounters',
            'completedEncounters',
            'recentEncounters'
        ));
    }

    public function receptionDashboard()
    {
        $today = now()->toDateString();

        $patientColumns = Schema::getColumnListing('patients');
        $hasArtNumber = in_array('art_number', $patientColumns, true);
        $hasNupn = in_array('nupn', $patientColumns, true);

        $searchBy = request()->query('search_by', 'nrc');
        $allowedSearchBy = ['barcode', 'nrc', 'art', 'nupn', 'cellphone', 'full_name'];
        if (!in_array($searchBy, $allowedSearchBy, true)) {
            $searchBy = 'nrc';
        }

        $queryValue = trim((string) request()->query('q', ''));
        $firstName = trim((string) request()->query('first_name', ''));
        $lastName = trim((string) request()->query('last_name', ''));
        $dob = trim((string) request()->query('dob', ''));
        $gender = trim((string) request()->query('gender', ''));

        $hasSearch = $searchBy === 'full_name'
            ? ($firstName !== '' || $lastName !== '' || $dob !== '' || $gender !== '')
            : ($queryValue !== '');

        $todayRegistrations = DB::table('patients')
            ->whereDate(DB::raw('COALESCE(source_created_at, created_at)'), $today)
            ->count();

        $todayHouseholds = DB::table('households')
            ->whereDate(DB::raw('COALESCE(source_created_at, created_at)'), $today)
            ->count();

        $todayShiftPatients = (int) DB::table('shift_reports')
            ->whereDate('report_date', $today)
            ->sum('total_patients_seen');

        $recentRegistrations = DB::table('patients')
            ->select([
                'patient_id',
                'full_name',
                'gender',
                'phone_number',
                'nrc_number',
                'date_of_birth',
                'household_head_of_house',
                'barcode',
                DB::raw('COALESCE(source_created_at, created_at) as registered_at'),
            ])
            ->orderByDesc('registered_at')
            ->limit(12)
            ->get();

        $patientSelect = [
            'id',
            'patient_id',
            'full_name',
            'gender',
            'date_of_birth',
            'phone_number',
            'nrc_number',
            'household_head_of_house',
            'barcode',
            'source_created_at',
            'created_at',
        ];

        $patientSelect[] = $hasArtNumber ? 'art_number' : DB::raw('NULL as art_number');
        $patientSelect[] = $hasNupn ? 'nupn' : DB::raw('NULL as nupn');

        $patientSearchQuery = DB::table('patients')->select($patientSelect);

        $results = collect();
        $householdMatches = collect();
        $totalRecords = 0;

        if ($hasSearch) {
            if ($searchBy === 'full_name') {
                if ($firstName !== '') {
                    $patientSearchQuery->where('full_name', 'like', '%' . $firstName . '%');
                }

                if ($lastName !== '') {
                    $patientSearchQuery->where('full_name', 'like', '%' . $lastName . '%');
                }

                if ($dob !== '') {
                    $patientSearchQuery->whereDate('date_of_birth', $dob);
                }

                if ($gender !== '') {
                    $patientSearchQuery->whereRaw('LOWER(gender) = ?', [strtolower($gender)]);
                }
            } elseif ($searchBy === 'barcode') {
                $patientSearchQuery->where(function ($q) use ($queryValue) {
                    $q->where('barcode', 'like', '%' . $queryValue . '%')
                        ->orWhere('patient_id', 'like', '%' . $queryValue . '%');
                });
            } elseif ($searchBy === 'nrc') {
                $patientSearchQuery->where('nrc_number', 'like', '%' . $queryValue . '%');
            } elseif ($searchBy === 'art') {
                if ($hasArtNumber) {
                    $patientSearchQuery->where('art_number', 'like', '%' . $queryValue . '%');
                } else {
                    $patientSearchQuery->whereRaw('1 = 0');
                }
            } elseif ($searchBy === 'nupn') {
                if ($hasNupn) {
                    $patientSearchQuery->where('nupn', 'like', '%' . $queryValue . '%');
                } else {
                    $patientSearchQuery->whereRaw('1 = 0');
                }
            } elseif ($searchBy === 'cellphone') {
                $digitsOnly = preg_replace('/\D+/', '', $queryValue) ?? '';
                $last9 = strlen($digitsOnly) >= 9 ? substr($digitsOnly, -9) : '';

                $phoneVariants = array_values(array_unique(array_filter([
                    $queryValue,
                    $digitsOnly,
                    $last9,
                    $last9 !== '' ? '0' . $last9 : '',
                    $last9 !== '' ? '260' . $last9 : '',
                ], fn ($v) => $v !== '')));

                $patientNormalizedExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone_number, ''), ' ', ''), '+', ''), '-', ''), '(', ''), ')', ''), '.', ''), '/', '')";
                $householdNormalizedExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone_number, ''), ' ', ''), '+', ''), '-', ''), '(', ''), ')', ''), '.', ''), '/', '')";

                $matchedHouseholds = DB::table('households')
                    ->select(['household_id', 'head_of_house'])
                    ->where(function ($q) use ($queryValue, $phoneVariants, $householdNormalizedExpr) {
                        $q->where('phone_number', 'like', '%' . $queryValue . '%');

                        foreach ($phoneVariants as $variant) {
                            $q->orWhereRaw($householdNormalizedExpr . ' LIKE ?', ['%' . $variant . '%']);
                        }
                    })
                    ->get();

                $matchedHouseholdIds = $matchedHouseholds
                    ->pluck('household_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $matchedHouseholdHeads = $matchedHouseholds
                    ->pluck('head_of_house')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $patientSearchQuery->where(function ($q) use ($queryValue, $phoneVariants, $patientNormalizedExpr, $matchedHouseholdIds, $matchedHouseholdHeads) {
                    $q->where('phone_number', 'like', '%' . $queryValue . '%');

                    foreach ($phoneVariants as $variant) {
                        $q->orWhereRaw($patientNormalizedExpr . ' LIKE ?', ['%' . $variant . '%']);
                    }

                    if ($matchedHouseholdIds !== []) {
                        $q->orWhereIn('household_id', $matchedHouseholdIds);
                    }

                    if ($matchedHouseholdHeads !== []) {
                        $q->orWhereIn('household_head_of_house', $matchedHouseholdHeads);
                    }
                });
            }

            $results = $patientSearchQuery
                ->orderByDesc(DB::raw('COALESCE(source_created_at, created_at)'))
                ->orderByDesc('id')
                ->limit(25)
                ->get();

            if ($searchBy === 'barcode' || $searchBy === 'nrc') {
                $householdMatches = DB::table('households')
                    ->select([
                        'id',
                        'household_id',
                        'head_of_house',
                        'phone_number',
                        'village',
                        'town',
                        'barcode',
                        'source_created_at',
                        'created_at',
                    ])
                    ->where(function ($q) use ($queryValue, $searchBy) {
                        if ($searchBy === 'barcode') {
                            $q->where('barcode', 'like', '%' . $queryValue . '%')
                                ->orWhere('household_id', 'like', '%' . $queryValue . '%');
                        } else {
                            $q->where('nrc_number', 'like', '%' . $queryValue . '%');
                        }
                    })
                    ->orderByDesc(DB::raw('COALESCE(source_created_at, created_at)'))
                    ->orderByDesc('id')
                    ->limit(25)
                    ->get();
            }

            $totalRecords = $results->count() + $householdMatches->count();
        }

        $recentHouseholds = DB::table('households')
            ->select([
                'household_id',
                'head_of_house',
                'phone_number',
                'village',
                'town',
                'barcode',
                DB::raw('COALESCE(source_created_at, created_at) as registered_at'),
            ])
            ->orderByDesc('registered_at')
            ->limit(8)
            ->get();

        $shiftSummary = DB::table('shift_reports')
            ->select('shift_type', DB::raw('SUM(total_patients_seen) as total_seen'))
            ->groupBy('shift_type')
            ->orderByDesc('total_seen')
            ->get();

        return view('reception.dashboard', compact(
            'todayRegistrations',
            'todayHouseholds',
            'todayShiftPatients',
            'recentRegistrations',
            'results',
            'totalRecords',
            'searchBy',
            'queryValue',
            'firstName',
            'lastName',
            'dob',
            'gender',
            'hasSearch',
            'householdMatches',
            'recentHouseholds',
            'shiftSummary'
        ));
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}

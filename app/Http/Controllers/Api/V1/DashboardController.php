<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EncounterSummaryResource;
use App\Models\Encounter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $stageCounts = Encounter::query()
            ->whereNull('deleted_at')
            ->where('current_status', '!=', 'cancelled')
            ->select('current_stage', DB::raw('count(*) as total'))
            ->groupBy('current_stage')
            ->pluck('total', 'current_stage');

        $recentEncounters = Encounter::with('patient')
            ->latest('started_at')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'patients' => Schema::hasTable('patients') ? DB::table('patients')->count() : 0,
                'households' => Schema::hasTable('households') ? DB::table('households')->count() : 0,
                'today_shift_patients' => Schema::hasTable('shift_reports')
                    ? (int) DB::table('shift_reports')->whereDate('report_date', now()->toDateString())->sum('total_patients_seen')
                    : 0,
                'encounters' => [
                    'total' => Encounter::count(),
                    'active' => Encounter::whereNotIn('current_status', ['completed', 'cancelled'])->count(),
                    'completed' => Encounter::where('current_status', 'completed')->count(),
                    'by_stage' => $stageCounts,
                    'recent' => EncounterSummaryResource::collection($recentEncounters),
                ],
            ],
        ]);
    }
}

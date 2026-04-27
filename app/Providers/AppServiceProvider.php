<?php

namespace App\Providers;

use App\Enums\EncounterStage;
use App\Models\Encounter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('components.sidebar', function ($view) {
            $counts = Encounter::query()
                ->whereNull('deleted_at')
                ->whereIn('current_status', ['queued', 'in_progress'])
                ->selectRaw('current_stage, count(*) as total')
                ->groupBy('current_stage')
                ->pluck('total', 'current_stage');

            $view->with('stageCounts', [
                'registration'     => $counts[EncounterStage::Registration->value] ?? 0,
                'triage'           => $counts[EncounterStage::Triage->value] ?? 0,
                'screening'        => $counts[EncounterStage::Screening->value] ?? 0,
                'lab'              => $counts[EncounterStage::Lab->value] ?? 0,
                'screening_review' => $counts[EncounterStage::ScreeningReview->value] ?? 0,
                'pharmacy'         => $counts[EncounterStage::Pharmacy->value] ?? 0,
            ]);
        });
    }
}

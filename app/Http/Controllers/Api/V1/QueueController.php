<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EncounterSummaryResource;
use App\Models\Encounter;
use Illuminate\Http\JsonResponse;

class QueueController extends Controller
{
    public function index(string $stage): JsonResponse
    {
        $stageEnum = $this->stageFromRoute($stage);

        $queued = Encounter::with(['patient', 'startedBy'])
            ->where('current_stage', $stageEnum->value)
            ->where('current_status', EncounterStatus::Queued->value)
            ->orderBy('updated_at')
            ->get();

        $inProgress = Encounter::with(['patient', 'startedBy'])
            ->where('current_stage', $stageEnum->value)
            ->where('current_status', EncounterStatus::InProgress->value)
            ->orderBy('updated_at')
            ->get();

        return response()->json([
            'data' => [
                'stage' => $stageEnum->value,
                'queued' => EncounterSummaryResource::collection($queued),
                'in_progress' => EncounterSummaryResource::collection($inProgress),
            ],
        ]);
    }

    private function stageFromRoute(string $stage): EncounterStage
    {
        return match ($stage) {
            'registration' => EncounterStage::Registration,
            'triage' => EncounterStage::Triage,
            'screening' => EncounterStage::Screening,
            'lab' => EncounterStage::Lab,
            'screening-review' => EncounterStage::ScreeningReview,
            'pharmacy' => EncounterStage::Pharmacy,
            default => abort(404, 'Unknown queue stage.'),
        };
    }
}

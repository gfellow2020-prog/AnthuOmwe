<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Encounter\SearchPatientAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Patient::with('activeEncounter');
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('patient_id', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('nrc_number', 'like', "%{$search}%");
            });
        }

        return PatientResource::collection(
            $query->latest('source_created_at')
                ->latest('id')
                ->paginate((int) $request->integer('per_page', 25)),
        );
    }

    public function search(Request $request, SearchPatientAction $searchAction): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'in:male,female'],
        ]);

        return PatientResource::collection(
            $searchAction->handle(
                query: $validated['q'],
                dateOfBirth: $validated['date_of_birth'] ?? null,
                sex: $validated['sex'] ?? null,
            )->load('activeEncounter'),
        );
    }

    public function show(string $ref): PatientResource
    {
        $patient = Patient::with(['activeEncounter', 'encounters.patient'])
            ->where('patient_id', $ref)
            ->orWhere('barcode', $ref)
            ->when(is_numeric($ref), fn ($query) => $query->orWhere('id', (int) $ref))
            ->firstOrFail();

        return new PatientResource($patient);
    }
}

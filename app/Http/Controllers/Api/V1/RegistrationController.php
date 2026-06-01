<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Encounter\SearchPatientAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PatientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function searchPatients(Request $request, SearchPatientAction $searchAction): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'in:male,female'],
        ]);

        $patients = $searchAction->handle(
            query: $validated['q'],
            dateOfBirth: $validated['date_of_birth'] ?? null,
            sex: $validated['sex'] ?? null,
        );

        return response()->json([
            'data' => PatientResource::collection($patients),
            'count' => $patients->count(),
        ]);
    }

    public function searchHouseholds(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $like = '%' . trim($validated['q']) . '%';

        $households = DB::table('households')
            ->select('household_id', 'head_of_house', 'phone_number', 'village', 'town')
            ->where(function ($builder) use ($like): void {
                $builder->where('head_of_house', 'like', $like)
                    ->orWhere('household_id', 'like', $like)
                    ->orWhere('phone_number', 'like', $like);
            })
            ->orderBy('head_of_house')
            ->orderBy('household_id')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $households,
            'count' => $households->count(),
        ]);
    }

    public function villages(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('villages')->orderBy('name')->pluck('name'),
        ]);
    }

    public function storeVillage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:villages,name'],
        ]);

        $name = trim($validated['name']);

        DB::table('villages')->insert([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Village created.',
            'data' => ['name' => $name],
        ], 201);
    }
}

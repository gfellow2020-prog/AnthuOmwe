<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'active' => ['nullable', 'boolean'],
        ]);

        $query = Medication::query();

        if (! $request->has('active') || $request->boolean('active')) {
            $query->active();
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->orderBy('name')->limit(50)->get(),
        ]);
    }

    public function show(Medication $medication): JsonResponse
    {
        return response()->json([
            'data' => $medication,
        ]);
    }
}

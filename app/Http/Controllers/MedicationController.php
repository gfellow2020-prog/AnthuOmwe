<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicationController extends Controller
{
    public function index(Request $request): View
    {
        $search   = $request->query('search');
        $category = $request->query('category');

        $medications = Medication::query()
            ->when($search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('generic_name', 'like', "%{$s}%"))
            ->when($category, fn ($q, $c) => $q->where('category', $c))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Medication::distinct()->orderBy('category')->pluck('category');

        return view('medications.index', compact('medications', 'categories', 'search', 'category'));
    }

    public function create(): View
    {
        $categories = Medication::distinct()->orderBy('category')->pluck('category');
        return view('medications.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'generic_name'      => ['nullable', 'string', 'max:255'],
            'category'          => ['required', 'string', 'max:100'],
            'form'              => ['required', 'string', 'max:100'],
            'strength'          => ['nullable', 'string', 'max:100'],
            'default_route'     => ['nullable', 'string', 'max:50'],
            'default_frequency' => ['nullable', 'string', 'max:50'],
            'is_controlled'     => ['nullable'],
            'is_active'         => ['nullable'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_controlled'] = $request->boolean('is_controlled');
        $validated['is_active']     = $request->boolean('is_active', true);

        Medication::create($validated);

        return redirect()->route('medications.index')->with('success', 'Medication created.');
    }

    public function show(Medication $medication): View
    {
        return view('medications.show', compact('medication'));
    }

    public function edit(Medication $medication): View
    {
        $categories = Medication::distinct()->orderBy('category')->pluck('category');
        return view('medications.edit', compact('medication', 'categories'));
    }

    public function update(Request $request, Medication $medication): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'generic_name'      => ['nullable', 'string', 'max:255'],
            'category'          => ['required', 'string', 'max:100'],
            'form'              => ['required', 'string', 'max:100'],
            'strength'          => ['nullable', 'string', 'max:100'],
            'default_route'     => ['nullable', 'string', 'max:50'],
            'default_frequency' => ['nullable', 'string', 'max:50'],
            'is_controlled'     => ['nullable'],
            'is_active'         => ['nullable'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_controlled'] = $request->boolean('is_controlled');
        $validated['is_active']     = $request->boolean('is_active');

        $medication->update($validated);

        return redirect()->route('medications.show', $medication)->with('success', 'Medication updated.');
    }

    public function destroy(Medication $medication): RedirectResponse
    {
        $medication->delete();

        return redirect()->route('medications.index')->with('success', 'Medication deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlatformComplaintRequest;
use App\Models\PlatformComplaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformComplaintController extends Controller
{
    public function index(): View
    {
        $complaints = PlatformComplaint::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('complaints.index', compact('complaints'));
    }

    public function store(StorePlatformComplaintRequest $request): RedirectResponse
    {
        $data = $request->validated();

        PlatformComplaint::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'page_url' => $data['page_url'] ?? null,
            'severity' => $data['severity'],
            'status' => 'open',
        ]);

        return redirect()
            ->route('complaints.index')
            ->with('success', 'Complaint submitted successfully. Thank you for reporting the issue.');
    }
}

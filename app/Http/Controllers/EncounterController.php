<?php

namespace App\Http\Controllers;

use App\Models\Encounter;
use App\Services\Encounter\EncounterDetailService;
use Illuminate\View\View;

class EncounterController extends Controller
{
    public function __construct(
        private readonly EncounterDetailService $detailService,
    ) {}

    // GET /encounters
    public function index(): View
    {
        $encounters = $this->detailService->list();
        return view('encounters.index', compact('encounters'));
    }

    // GET /encounters/{encounter}
    public function show(Encounter $encounter): View
    {
        $encounter = $this->detailService->load($encounter);
        return view('encounters.show', compact('encounter'));
    }
}

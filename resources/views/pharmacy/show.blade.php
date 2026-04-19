@extends('layouts.dashboard')

@section('title', 'Pharmacy — ' . $encounter->encounter_number)

@push('styles')
<style>
    .card    { background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.08);border:1px solid #e5e7eb;margin-bottom:1.5rem; }
    .card-hd { padding:1rem 1.5rem;border-bottom:1px solid #f3f4f6;font-weight:600;font-size:.9rem;color:#111827; }
    .card-bd { padding:1.25rem 1.5rem; }
    .badge-locked  { display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:9999px;font-size:12px;font-weight:600; }
    .badge-open    { display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:9999px;font-size:12px;font-weight:600; }
    .btn-primary   { display:inline-flex;align-items:center;gap:6px;padding:9px 20px;font-size:13px;font-weight:600;background:#2563eb;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-primary:hover   { background:#1d4ed8; }
    .btn-success   { display:inline-flex;align-items:center;gap:6px;padding:9px 20px;font-size:13px;font-weight:600;background:#16a34a;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-success:hover   { background:#15803d; }
    .btn-danger    { display:inline-flex;align-items:center;gap:6px;padding:9px 20px;font-size:13px;font-weight:600;background:#dc2626;color:#fff;border-radius:8px;border:none;cursor:pointer; }
    .btn-danger:hover    { background:#b91c1c; }
    table { width:100%;border-collapse:collapse; }
    th,td { padding:8px 12px;text-align:left;font-size:13px; }
    th    { background:#f9fafb;font-weight:600;color:#374151;border-bottom:1px solid #e5e7eb; }
    td    { color:#374151;border-bottom:1px solid #f3f4f6; }
    input,textarea,select { width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px; }
</style>
@endpush

@section('page-header')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $encounter->patient->full_name }}</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $encounter->encounter_number }} &bull; Pharmacy Stage</p>
    </div>
    <div class="flex items-center gap-3">
        @if($encounter->is_locked)
            <span class="badge-locked">&#128274; Closed &amp; Locked</span>
        @else
            <span class="badge-open">&#9679; Open</span>
        @endif
        <a href="{{ route('pharmacy.queue') }}" class="text-sm text-blue-600 hover:underline">&#8592; Queue</a>
    </div>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- Prescription summary --}}
@if($encounter->prescription)
<div class="card">
    <div class="card-hd">Active Prescription — {{ $encounter->prescription->prescription_number }}</div>
    <div class="card-bd p-0">
        <table>
            <thead>
                <tr>
                    <th>Drug</th><th>Dose</th><th>Frequency</th><th>Duration</th><th>Qty</th><th>Route</th>
                </tr>
            </thead>
            <tbody>
                @forelse($encounter->prescription->items as $item)
                <tr>
                    <td class="font-medium">{{ $item->drug_name }}</td>
                    <td>{{ $item->dose }}</td>
                    <td>{{ $item->frequency }}</td>
                    <td>{{ $item->duration }}</td>
                    <td>{{ $item->quantity_prescribed }}</td>
                    <td>{{ $item->route ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-gray-400 py-4">No items on prescription.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Dispense form (only when not yet dispensed and encounter is open) --}}
@if(!$encounter->is_locked && !$encounter->dispense)
<div class="card" x-data="dispenseForm()">
    <div class="card-hd">Dispense Medications</div>
    <div class="card-bd">
        <form method="POST" action="{{ route('pharmacy.dispense', $encounter) }}">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Dispensing Notes</label>
                    <textarea name="dispensing_notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">{{ old('dispensing_notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Counseling Notes</label>
                    <textarea name="counseling_notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">{{ old('counseling_notes') }}</textarea>
                </div>
            </div>

            {{-- Drug rows --}}
            <div class="mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-600">Items Dispensed</span>
                    <button type="button" @click="addRow()" class="text-xs text-blue-600 hover:underline">+ Add drug</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Drug Name</th><th>Qty Dispensed</th><th>Batch No</th><th>Instructions</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in rows" :key="i">
                            <tr>
                                <td><input type="text" :name="`items[${i}][drug_name]`" x-model="row.drug_name" required placeholder="Drug name"></td>
                                <td><input type="number" :name="`items[${i}][quantity_dispensed]`" x-model="row.quantity_dispensed" required min="1" placeholder="0"></td>
                                <td><input type="text" :name="`items[${i}][batch_no]`" x-model="row.batch_no" placeholder="Optional"></td>
                                <td><input type="text" :name="`items[${i}][instructions]`" x-model="row.instructions" placeholder="Optional"></td>
                                <td>
                                    <button type="button" @click="rows.splice(i,1)" class="text-red-500 text-xs hover:underline" x-show="rows.length > 1">Remove</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-success">Save Dispense Record</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Dispense summary (after dispensing) --}}
@if($encounter->dispense)
<div class="card">
    <div class="card-hd">Dispense Record</div>
    <div class="card-bd">
        <p class="text-xs text-gray-500 mb-3">Dispensed on {{ $encounter->dispense->dispensed_at?->format('d M Y H:i') }}</p>
        <table>
            <thead>
                <tr><th>Drug</th><th>Qty Dispensed</th><th>Batch No</th><th>Instructions</th></tr>
            </thead>
            <tbody>
                @foreach($encounter->dispense->items as $item)
                <tr>
                    <td class="font-medium">{{ $item->drug_name }}</td>
                    <td>{{ $item->quantity_dispensed }}</td>
                    <td>{{ $item->batch_no ?? '—' }}</td>
                    <td>{{ $item->instructions ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($encounter->dispense->counseling_notes)
        <p class="mt-3 text-xs text-gray-600"><strong>Counseling:</strong> {{ $encounter->dispense->counseling_notes }}</p>
        @endif
    </div>
</div>
@endif

{{-- Close encounter button --}}
@if(!$encounter->is_locked && $encounter->dispense)
<div class="card">
    <div class="card-bd flex items-center justify-between">
        <div>
            <p class="font-semibold text-gray-900 text-sm">Close &amp; Lock Encounter</p>
            <p class="text-xs text-gray-500 mt-0.5">This is irreversible. The encounter will be locked for all edits.</p>
        </div>
        <form method="POST" action="{{ route('pharmacy.close', $encounter) }}" onsubmit="return confirm('Close and permanently lock this encounter?')">
            @csrf
            <input type="hidden" name="closure_notes" value="">
            <button type="submit" class="btn-danger">Close Encounter</button>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function dispenseForm() {
    return {
        rows: [{ drug_name: '', quantity_dispensed: '', batch_no: '', instructions: '' }],
        addRow() {
            this.rows.push({ drug_name: '', quantity_dispensed: '', batch_no: '', instructions: '' });
        }
    };
}
</script>
@endpush

@endsection

{{-- Shared medication form fields --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Name --}}
    <div>
        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $medication->name ?? '') }}" required
               class="w-full px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none"
               placeholder="e.g. Paracetamol">
        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Generic Name --}}
    <div>
        <label for="generic_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Generic Name</label>
        <input type="text" name="generic_name" id="generic_name" value="{{ old('generic_name', $medication->generic_name ?? '') }}"
               class="w-full px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none"
               placeholder="e.g. Acetaminophen">
        @error('generic_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Category --}}
    <div>
        <label for="category" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Category <span class="text-red-500">*</span></label>
        <input type="text" name="category" id="category" list="category-list" value="{{ old('category', $medication->category ?? '') }}" required
               class="w-full px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none"
               placeholder="e.g. Antibiotic">
        <datalist id="category-list">
            @foreach($categories as $cat)
                <option value="{{ $cat }}">
            @endforeach
        </datalist>
        @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Form --}}
    <div>
        <label for="form" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Form <span class="text-red-500">*</span></label>
        <select name="form" id="form" required
                class="w-full px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            <option value="">-- Select --</option>
            @foreach(['Tablet','Capsule','Syrup','Injection','Inhaler','Nebuliser','Cream','Ointment','Gel','Lotion','Solution','Suspension','Eye Drops','Eye Ointment','Ear Drops','Oral Drops','Suppository','Rectal Tube','Pessary','Sachet','IV Bag'] as $f)
                <option value="{{ $f }}" {{ old('form', $medication->form ?? '') === $f ? 'selected' : '' }}>{{ $f }}</option>
            @endforeach
        </select>
        @error('form') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Strength --}}
    <div>
        <label for="strength" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Strength</label>
        <input type="text" name="strength" id="strength" value="{{ old('strength', $medication->strength ?? '') }}"
               class="w-full px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none"
               placeholder="e.g. 500mg">
        @error('strength') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Default Route --}}
    <div>
        <label for="default_route" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Default Route</label>
        <select name="default_route" id="default_route"
                class="w-full px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            <option value="">-- Select --</option>
            @foreach(['Oral','IV','IM','SC','Topical','Rectal','Inhaled','Sublingual','Ophthalmic','Otic','Vaginal'] as $r)
                <option value="{{ $r }}" {{ old('default_route', $medication->default_route ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
            @endforeach
        </select>
        @error('default_route') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Default Frequency --}}
    <div>
        <label for="default_frequency" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Default Frequency</label>
        <select name="default_frequency" id="default_frequency"
                class="w-full px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            <option value="">-- Select --</option>
            @foreach(['Stat','OD','BD','TDS','QDS','PRN','Monthly'] as $freq)
                <option value="{{ $freq }}" {{ old('default_frequency', $medication->default_frequency ?? '') === $freq ? 'selected' : '' }}>{{ $freq }}</option>
            @endforeach
        </select>
        @error('default_frequency') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Notes --}}
    <div>
        <label for="notes" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Notes</label>
        <input type="text" name="notes" id="notes" value="{{ old('notes', $medication->notes ?? '') }}"
               class="w-full px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none"
               placeholder="Optional notes…">
        @error('notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

{{-- Checkboxes --}}
<div class="flex items-center gap-6 mt-5">
    <label class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
        <input type="checkbox" name="is_controlled" value="1"
               {{ old('is_controlled', $medication->is_controlled ?? false) ? 'checked' : '' }}
               class="rounded border-neutral-300 text-neutral-900 focus:ring-neutral-400">
        Controlled substance
    </label>
    <label class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $medication->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-neutral-300 text-neutral-900 focus:ring-neutral-400">
        Active
    </label>
</div>

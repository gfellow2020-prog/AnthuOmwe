<?php

namespace App\Http\Requests;

class CloseEncounterRequest extends BaseEncounterRequest
{
    public function rules(): array
    {
        return [
            'closure_notes' => ['nullable', 'string'],
        ];
    }
}

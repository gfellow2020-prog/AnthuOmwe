<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseEncounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'closure_notes' => ['nullable', 'string'],
        ];
    }
}

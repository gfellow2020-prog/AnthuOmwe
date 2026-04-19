<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.drug_name'     => ['required', 'string', 'max:255'],
            'items.*.strength'      => ['nullable', 'string', 'max:100'],
            'items.*.formulation'   => ['nullable', 'string', 'max:100'],
            'items.*.dose'          => ['required', 'string', 'max:100'],
            'items.*.frequency'     => ['required', 'string', 'max:100'],
            'items.*.duration'      => ['required', 'string', 'max:100'],
            'items.*.quantity_prescribed' => ['required', 'integer', 'min:1'],
            'items.*.route'         => ['nullable', 'string', 'max:100'],
            'items.*.instructions'  => ['nullable', 'string', 'max:500'],
        ];
    }
}

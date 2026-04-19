<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base class for all encounter-scoped Form Requests.
 *
 * Authorization: open to any authenticated user for now.
 * Roles and permission checks will be layered in without
 * refactoring by overriding authorize() in concrete subclasses.
 */
abstract class BaseEncounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }
}

<?php

namespace App\Http\Requests;

use App\Models\CalendarEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'event_date'  => ['required', 'date'],
            'start_time'  => ['nullable', 'date_format:H:i'],
            'end_time'    => ['nullable', 'date_format:H:i', 'after:start_time'],
            'event_type'  => ['required', Rule::in(array_keys(CalendarEvent::$types))],
            'location'    => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'      => 'Event title is required.',
            'event_date.required' => 'Event date is required.',
            'event_type.in'       => 'Invalid event type.',
            'end_time.after'      => 'End time must be after start time.',
        ];
    }
}

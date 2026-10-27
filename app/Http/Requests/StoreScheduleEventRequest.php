<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->streamerProfile !== null;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'in:stream,personal,training'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'title' => ['required', 'string', 'max:150'],
        ];
    }
}

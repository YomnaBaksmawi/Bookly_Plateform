<?php

namespace App\Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'booking_slot_id' => ['required', 'integer', 'exists:booking_slots,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
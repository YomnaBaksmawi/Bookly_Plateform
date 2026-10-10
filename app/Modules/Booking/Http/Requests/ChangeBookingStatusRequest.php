<?php

namespace App\Modules\Booking\Http\Requests;

use App\Modules\Booking\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;

class ChangeBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:' . implode(',', BookingStatus::values())],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function status(): BookingStatus
    {
        return BookingStatus::from($this->input('status'));
    }
}
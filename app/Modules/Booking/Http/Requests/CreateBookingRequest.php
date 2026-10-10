<?php

namespace App\Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'booking_slot_id' => ['required', 'integer', 'exists:booking_slots,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->header('Idempotency-Key')) {
                $validator->errors()->add('Idempotency-Key', 'The Idempotency-Key header is required.' );
            }
        });
    }

    public function idempotencyKey(): string
    {
        return $this->header('Idempotency-Key');
    }
}
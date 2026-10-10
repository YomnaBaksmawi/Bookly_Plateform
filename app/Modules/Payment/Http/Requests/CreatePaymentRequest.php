<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
class CreatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
        ];
    }
    
  public function idempotencyKey(): string
    {
        $key = $this->header('Idempotency-Key');

        if (!$key) {
            throw ValidationException::withMessages([
                'Idempotency-Key' => ['The Idempotency-Key header is required.'],
            ]);
        }

        return $key;
    }
}
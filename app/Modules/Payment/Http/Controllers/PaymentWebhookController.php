<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PaymentWebhookController extends Controller
{
 public function handle(Request $request): JsonResponse
{
    $payload = $request->getContent();
    $signature = $request->header('X-Payment-Signature');
    $secret = config('services.payment.webhook_secret');

    $expectedSignature = hash_hmac('sha256', $payload, $secret);

    if (!$signature || !hash_equals($expectedSignature, $signature)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid webhook signature.',
            'error_code' => 'INVALID_WEBHOOK_SIGNATURE',
        ], 401);
    }

    $data = json_decode($payload, true);
    $eventId = $data['id'] ?? null;
    $eventType = $data['type'] ?? null;
    $transactionId = $data['data']['transaction_id'] ?? null;

    if (!$eventId || !$eventType || !$transactionId) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid webhook payload.',
            'error_code' => 'INVALID_WEBHOOK_PAYLOAD',
        ], 422);
    }

    return DB::transaction(function () use ($eventId, $eventType, $transactionId, $data) {
        $existingEvent = WebhookEvent::where('event_id', $eventId)->first();

        if ($existingEvent) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook already processed.',
            ]);
        }

        $payment = Payment::where('transaction_id', $transactionId)
            ->lockForUpdate()
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.',
                'error_code' => 'PAYMENT_NOT_FOUND',
            ], 404);
        }

        $status = match ($eventType) {
            'payment.succeeded' => 'paid',
            'payment.failed' => 'failed',
            'payment.refunded' => 'refunded',
            default => null,
        };

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported webhook event.',
                'error_code' => 'UNSUPPORTED_WEBHOOK_EVENT',
            ], 422);
        }

        $payment->update(['status' => $status]);

        WebhookEvent::create([
            'event_id' => $eventId,
            'provider' => $payment->provider,
            'event_type' => $eventType,
            'payload' => $data,
            'processed_at' => now(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed successfully.',
        ]);
    });
}
public function generateTestSignature(Request $request): JsonResponse
{
    abort_unless(app()->environment('local'), 404);

    $payload = $request->getContent();
    $secret = config('services.payment.webhook_secret');

    return response()->json([
        'success' => true,
        'signature' => hash_hmac('sha256', $payload, $secret),
    ]);
}
}
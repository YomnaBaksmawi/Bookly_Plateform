<?php

namespace App\Modules\Payment\Http\Controllers;
use App\Modules\Payment\Queries\GetProviderPaymentsQuery;
use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Booking;
use App\Modules\Payment\Actions\CreatePaymentAction;
use App\Modules\Payment\Http\Requests\CreatePaymentRequest;
use Illuminate\Http\JsonResponse;
use App\Modules\Payment\Models\Payment;
use Illuminate\Http\Request;
use App\Modules\Payment\Queries\GetPaymentsQuery;
class PaymentController extends Controller
{
    public function index(
    Request $request,
    GetPaymentsQuery $query
): JsonResponse {
    $payments = $query->execute($request->user());

    return response()->json([
        'success' => true,
        'message' => 'Payments retrieved successfully.',
        'data' => $payments,
    ]);
}
    public function store(CreatePaymentRequest $request, CreatePaymentAction $action): JsonResponse
    {
        $booking = Booking::where('id', $request->integer('booking_id'))
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $payment = $action->execute(
            $booking,
    $request->idempotencyKey()

       );

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully.',
            'data' => [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'provider' => $payment->provider,
                'transaction_id' => $payment->transaction_id,
            ],
        ], 201);
    }
     public function show(Payment $payment): JsonResponse
    {
        $payment->load('booking');

        if ($payment->booking->customer_id !== request()->user()->id) {
            abort(404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment retrieved successfully.',
            'data' => [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'provider' => $payment->provider,
                'transaction_id' => $payment->transaction_id,
                'created_at' => $payment->created_at,
            ],
        ]);
    }
   public function providerIndex(
    Request $request,
    GetProviderPaymentsQuery $query
): JsonResponse {
    $payments = $query->execute($request->user());

    return response()->json([
        'success' => true,
        'message' => 'Provider payments retrieved successfully.',
        'data' => $payments,
    ]);
}
}
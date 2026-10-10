<?php

use App\Http\Middleware\CheckAbility;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SanitizeApiInput;
use App\Http\Middleware\VerifyApiCsrfToken;
use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\InvalidCurrentPasswordException;
use App\Modules\Auth\Exceptions\UserSuspendedException;
use App\Modules\Booking\Exceptions\BookingCancellationNotAllowedException;
use App\Modules\Booking\Exceptions\BookingReschedulingNotAllowedException;
use App\Modules\Booking\Exceptions\BookingSlotUnavailableException;
use App\Modules\PublicHoliday\Exceptions\PublicHolidayAlreadyExistsException;
use App\Modules\ScheduleException\Exceptions\ScheduleExceptionAlreadyExistsException;
use App\Modules\WorkingHour\Exceptions\LocationNotFoundException;
use App\Modules\WorkingHour\Exceptions\WorkingHourOverlapException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'role' => CheckRole::class,
            'ability' => CheckAbility::class,
            'csrf.api' => VerifyApiCsrfToken::class,
        ]);

        $middleware->api(append: [
            SanitizeApiInput::class,
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('api/*') ? null : route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Authentication - 401
        $exceptions->render(function (
            AuthenticationException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'UNAUTHENTICATED',
                'errors' => null,
            ], 401);
        });

        // Authorization - 403
        $exceptions->render(function (
            AuthorizationException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'This action is unauthorized.',
                'error_code' => 'FORBIDDEN',
                'errors' => null,
            ], 403);
        });

        // Route / Resource Not Found - 404
        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found.',
                'error_code' => 'ROUTE_NOT_FOUND',
                'errors' => null,
            ], 404);
        });

        // Method Not Allowed - 405
        $exceptions->render(function (
            MethodNotAllowedHttpException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed.',
                'error_code' => 'METHOD_NOT_ALLOWED',
                'errors' => null,
            ], 405);
        });

        // Validation - 422
        $exceptions->render(function (
            ValidationException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'error_code' => 'VALIDATION_ERROR',
                'errors' => $e->errors(),
            ], 422);
        });

        // Auth Module Exceptions
        $exceptions->render(function (
            InvalidCredentialsException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'INVALID_CREDENTIALS',
                'errors' => null,
            ], $e->getCode() ?: 401);
        });

        $exceptions->render(function (
            UserSuspendedException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'USER_SUSPENDED',
                'errors' => null,
            ], $e->getCode() ?: 403);
        });

        $exceptions->render(function (
            InvalidCurrentPasswordException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'INVALID_CURRENT_PASSWORD',
                'errors' => null,
            ], $e->getCode() ?: 422);
        });

        // Working Hour Exceptions
        $exceptions->render(function (
            LocationNotFoundException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'LOCATION_NOT_FOUND',
                'errors' => null,
            ], 404);
        });

        $exceptions->render(function (
            WorkingHourOverlapException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'WORKING_HOUR_OVERLAP',
                'errors' => null,
            ], 409);
        });

        // Schedule Exception
        $exceptions->render(function (
            ScheduleExceptionAlreadyExistsException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'SCHEDULE_EXCEPTION_ALREADY_EXISTS',
                'errors' => null,
            ], 409);
        });

        // Public Holiday
        $exceptions->render(function (
            PublicHolidayAlreadyExistsException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'PUBLIC_HOLIDAY_ALREADY_EXISTS',
                'errors' => null,
            ], 409);
        });

        // Booking Exceptions
        $exceptions->render(function (
            BookingSlotUnavailableException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BOOKING_SLOT_UNAVAILABLE',
                'errors' => null,
            ], 409);
        });

        $exceptions->render(function (
            BookingCancellationNotAllowedException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BOOKING_CANCELLATION_NOT_ALLOWED',
                'errors' => null,
            ], 409);
        });

        $exceptions->render(function (
            BookingReschedulingNotAllowedException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BOOKING_RESCHEDULING_NOT_ALLOWED',
                'errors' => null,
            ], 409);
        });

        // Booking status transition violates a business rule. The booking logic
        // throws a plain InvalidArgumentException, so only the known transition
        // message is mapped here; any other InvalidArgumentException falls through
        // to the generic 500 handler and stays an unexpected error.
        $exceptions->render(function (
            InvalidArgumentException $e,
            Request $request
        ) {
            if (! preg_match('/^Cannot transition booking from [a-z_]+ to [a-z_]+\.$/', $e->getMessage())) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'BOOKING_STATUS_TRANSITION_NOT_ALLOWED',
                'errors' => null,
            ], 409);
        });

        // Authorization - Access Denied
        $exceptions->render(function (
            AccessDeniedHttpException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'This action is unauthorized.',
                'error_code' => 'FORBIDDEN',
                'errors' => null,
            ], 403);
        });
        $exceptions->render(function (
            ThrottleRequestsException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'error_code' => 'TOO_MANY_REQUESTS',
                'errors' => null,
            ], 429);
        });

        // Unexpected Errors - 500
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => app()->environment('local')
                        ? $e->getMessage()
                        : 'An unexpected error occurred.',
                    'error_code' => app()->environment('local')
                        ? class_basename($e)
                        : 'INTERNAL_SERVER_ERROR',
                    'errors' => null,
                    'debug' => app()->environment('local')
                        ? [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]
                        : null,
                ], 500);
            }

            return null;
        });
    })
    ->create();

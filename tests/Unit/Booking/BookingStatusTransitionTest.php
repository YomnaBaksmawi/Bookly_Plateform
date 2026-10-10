<?php

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Services\BookingStatusTransitionService;

it('allows a pending booking to be confirmed', function () {
    $service = new BookingStatusTransitionService;

    expect($service->canTransition(BookingStatus::PENDING, BookingStatus::CONFIRMED))->toBeTrue();
});

it('allows a confirmed booking to be completed or marked as no-show', function () {
    $service = new BookingStatusTransitionService;

    expect($service->canTransition(BookingStatus::CONFIRMED, BookingStatus::COMPLETED))->toBeTrue();
    expect($service->canTransition(BookingStatus::CONFIRMED, BookingStatus::NO_SHOW))->toBeTrue();
});

it('rejects every invalid booking transition', function () {
    $service = new BookingStatusTransitionService;

    expect($service->canTransition(BookingStatus::PENDING, BookingStatus::COMPLETED))->toBeFalse();
    expect($service->canTransition(BookingStatus::PENDING, BookingStatus::NO_SHOW))->toBeFalse();
    expect($service->canTransition(BookingStatus::PENDING, BookingStatus::CANCELLED))->toBeFalse();
    expect($service->canTransition(BookingStatus::CONFIRMED, BookingStatus::PENDING))->toBeFalse();
    expect($service->canTransition(BookingStatus::CONFIRMED, BookingStatus::CANCELLED))->toBeFalse();
});

it('never allows a final booking to transition again', function () {
    $service = new BookingStatusTransitionService;

    foreach ([BookingStatus::CANCELLED, BookingStatus::COMPLETED, BookingStatus::NO_SHOW] as $final) {
        expect($final->isFinal())->toBeTrue();

        foreach (BookingStatus::cases() as $target) {
            expect($service->canTransition($final, $target))->toBeFalse();
        }
    }
});

it('returns the target status for a valid transition', function () {
    $service = new BookingStatusTransitionService;

    expect($service->transition(BookingStatus::PENDING, BookingStatus::CONFIRMED))
        ->toBe(BookingStatus::CONFIRMED);
});

it('throws a descriptive exception for an invalid transition', function () {
    $service = new BookingStatusTransitionService;

    $service->transition(BookingStatus::CONFIRMED, BookingStatus::PENDING);
})->throws(
    InvalidArgumentException::class,
    'Cannot transition booking from confirmed to pending.'
);
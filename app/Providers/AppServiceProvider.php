<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Policies\ServicePolicy;
use Illuminate\Support\Facades\Gate;
use App\Modules\WorkingHour\Models\WorkingHour;
use App\Modules\WorkingHour\Policies\WorkingHourPolicy;
use App\Modules\ScheduleException\Policies\ScheduleExceptionPolicy;
use App\Modules\ScheduleException\Models\ScheduleException;
use App\Modules\PublicHoliday\Models\PublicHoliday;
use App\Modules\PublicHoliday\Policies\PublicHolidayPolicy;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Policies\BookingPolicy;
use App\Modules\Booking\Events\BookingCreated;
use App\Modules\Booking\Listeners\SendBookingCreatedNotification;
use App\Modules\Payment\Contracts\PaymentGateway;
use App\Modules\Payment\Gateways\MockPaymentProvider;
use Illuminate\Support\Facades\Event;
use App\Modules\Booking\Events\BookingCancelled;
use App\Modules\Booking\Events\BookingCompleted;
use App\Modules\Booking\Events\BookingConfirmed;
use App\Modules\Booking\Listeners\SendBookingStatusNotification;
use App\Modules\Booking\Listeners\InvalidateMostBookedServicesCache;
use App\Modules\Location\Models\Location;
use App\Modules\Location\Policies\LocationPolicy;
use App\Modules\Booking\Listeners\SendAdminBookingCreatedNotification;
use App\Modules\Booking\Listeners\SendAdminBookingStatusNotification;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    $this->app->bind(PaymentGateway::class, MockPaymentProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Policies
    Gate::policy(Service::class, ServicePolicy::class);
    Gate::policy(WorkingHour::class, WorkingHourPolicy::class);
    Gate::policy(ScheduleException::class, ScheduleExceptionPolicy::class);
    Gate::policy(PublicHoliday::class, PublicHolidayPolicy::class);
    Gate::policy(Booking::class, BookingPolicy::class);
 Gate::policy(Location::class, LocationPolicy::class);

    // Events
  Event::listen(BookingCreated::class, SendBookingCreatedNotification::class);
Event::listen(BookingConfirmed::class, SendBookingStatusNotification::class);
Event::listen(BookingCancelled::class, SendBookingStatusNotification::class);
Event::listen(BookingCompleted::class, SendBookingStatusNotification::class);
Event::listen( BookingConfirmed::class,InvalidateMostBookedServicesCache::class);
Event::listen(BookingCancelled::class,InvalidateMostBookedServicesCache::class);
Event::listen(BookingCompleted::class,InvalidateMostBookedServicesCache::class);
Event::listen( BookingCreated::class, SendBookingCreatedNotification::class);
Event::listen(BookingCreated::class,SendAdminBookingCreatedNotification::class);
Event::listen(
    BookingCancelled::class,
    SendAdminBookingStatusNotification::class
);

Event::listen(
    BookingCompleted::class,
    SendAdminBookingStatusNotification::class
);
    }
}

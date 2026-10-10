# Bookly Development Runbook

## Queue Worker

php artisan queue:work

Required for:
- Notifications
- Queued Jobs

## Scheduler

php artisan schedule:work

Current scheduled tasks:
- Booking reminders - hourly

## Redis

Docker container:
redis

Used for:
- Cache
- Queue

## Swagger

php artisan l5-swagger:generate
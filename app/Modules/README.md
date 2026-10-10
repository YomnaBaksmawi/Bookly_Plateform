# Bookly — API-First Booking Engine

Bookly is a modular, API-first appointment-booking platform built with Laravel 13, PostgreSQL, and Redis. It is a work in progress focused on reliable booking workflows, dynamic availability, API security, and maintainable backend architecture.

> **Project status:** In development. Some endpoints and integrations remain incomplete and may be revisited in future iterations.

## Overview

Bookly provides a versioned REST API for managing appointment availability and bookings. Its backend is organized as a modular monolith, with domain-focused modules and dedicated actions, queries, services, policies, and API resources.

The project explores several backend engineering concerns, including:

- Generating appointment slots from working hours and schedule exceptions.
- Protecting booking creation against conflicting or simultaneous requests.
- Enforcing booking lifecycle and cancellation/rescheduling rules.
- Handling retry-safe operations with idempotency keys.
- Processing payment webhooks with signature verification and event de-duplication.
- Sending booking notifications and scheduled reminders.
- Providing administrative user-management, audit-log, and reporting endpoints.

## Technology Stack

| Area | Technology |
| --- | --- |
| Language and framework | PHP 8.3, Laravel 13 |
| Database | PostgreSQL |
| Cache and queues | Redis |
| Authentication | Laravel Sanctum |
| API documentation | OpenAPI / Swagger UI with L5-Swagger |
| Notifications | Laravel database and mail notifications, Firebase Cloud Messaging |
| Payments | Payment gateway abstraction with a mock provider |
| Testing | Pest |
| Code style | Laravel Pint |
| Frontend | No application frontend currently; Vite and Tailwind are configured in the repository |

The exact capabilities available in a local environment depend on its configuration and the integrations that have been completed.

## Architecture

Bookly follows a **modular monolith** approach. Domain code is organized under `app/Modules/`, separating business areas while keeping the application in one Laravel codebase.

Modules use patterns such as:

- **Actions** for individual write operations.
- **Queries** for read operations.
- **Services** for coordinating business logic.
- **Form Requests** for request validation.
- **API Resources** for response formatting.
- **Policies** for authorization.
- **Events, listeners, jobs, and notifications** for asynchronous or event-driven workflows.

This structure aims to keep controllers focused on HTTP concerns and make business rules easier to locate, test, and maintain.

Example module layout:

```text
app/Modules/Booking/
├── Actions/
├── Enums/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Queries/
└── Services/
```

## Core Features

### Availability and scheduling

- Weekly working-hour configuration.
- Schedule exceptions, including custom hours and holidays.
- Public-holiday records.
- Duration-aware appointment slot generation.
- Redis caching for availability results.

### Booking lifecycle

- Booking creation through a dedicated action.
- Transactional booking workflows and row locking.
- Database constraints intended to prevent duplicate bookings.
- Booking status transitions, including confirmation, completion, and no-show handling.
- Customer cancellation and rescheduling rules.
- Booking status history with actor and reason information.

### Payments and webhook handling

- A payment gateway abstraction with a mock provider.
- Idempotency support for payment operations.
- HMAC signature verification for payment webhooks.
- Webhook event de-duplication.

**Payment integration status:** The runtime provider is a mock implementation. Stripe and PayPal are not presented as completed payment integrations.

### Notifications and reminders

- Laravel database and mail notifications.
- Firebase Cloud Messaging support for push notifications.
- Queued booking notifications.
- Scheduled reminders for upcoming confirmed bookings.
- Duplicate-reminder protection.

### Administration and auditing

- User management and account status actions.
- Audit-log search.
- Reporting endpoints for booking and business data.
- Caching for selected reports.

The availability and completeness of individual endpoints may vary while the project remains under development.

## Database Design

The database includes entities for users, provider profiles, locations, services, categories, working hours, schedule exceptions, public holidays, booking slots, bookings, payments, webhook events, notifications, device tokens, audit logs, idempotency keys, and booking reminders.

PostgreSQL migrations define relationships and constraints for data integrity. Booking-related unique constraints are intended to provide a database-level safeguard against conflicting bookings, alongside application-level transaction and locking logic.

For the authoritative schema, refer to the project's migrations.

## API

Bookly exposes a versioned REST API.

- **Base path:** `/api/v1`
- **API documentation:** `/api/documentation` (Swagger UI, when generated and configured)
- **Authentication:** Laravel Sanctum bearer tokens
- **CSRF protection:** State-changing API requests may require the `X-CSRF-TOKEN` header, according to the project's API middleware configuration.
- **Idempotency:** Booking creation requires an `Idempotency-Key` header, according to the current implementation.

Main endpoint groups include authentication, CSRF tokens, provider profiles, services, categories, locations, working hours, schedule exceptions, public holidays, availability, bookings, payments, administration, and health checks.

Some routes are known to be incomplete or may reference controller methods that have not yet been implemented. Verify the current route and controller implementations before relying on a particular endpoint.

### Example request

The following example illustrates the general shape of a booking request. It assumes a valid token, CSRF token, and existing booking-slot ID.

```bash
curl -X POST http://localhost:8000/api/v1/bookings \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN" \
  -H "Idempotency-Key: YOUR_UNIQUE_REQUEST_KEY" \
  -H "Content-Type: application/json" \
  -d '{"booking_slot_id":12,"notes":"Afternoon appointment"}'
```

Use the generated Swagger documentation and current route definitions to confirm request fields, required headers, and response formats.

## Getting Started

### Prerequisites

Confirm the versions supported by your local setup. The project is intended to use:

- PHP 8.3 or a compatible version required by `composer.json`
- Composer
- PostgreSQL
- Redis
- Node.js and npm, if building the configured frontend assets

### Installation

Clone the repository and enter the project directory:

```bash
git clone YOUR_REPOSITORY_URL
cd Bookly
```

Install PHP dependencies and create the local environment file:

```bash
composer install
```

On macOS or Linux:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure `.env` for your local PostgreSQL database and Redis instance. Do not commit real credentials or secret values.

Typical settings may include:

```dotenv
DB_CONNECTION=pgsql
DB_DATABASE=bookly
DB_USERNAME=postgres
DB_PASSWORD=your_local_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

The exact environment variable names and required values should be checked against the project's configuration. Payment webhook secrets and Firebase credentials are only needed when exercising the corresponding integrations.

Run migrations after verifying that the application is connected to the intended **development database**:

```bash
php artisan migrate
```

If frontend dependencies are needed for the configured assets:

```bash
npm install
npm run build
```

### Running locally

Start the Laravel development server:

```bash
php artisan serve
```

If using queued notifications, start a queue worker in a separate terminal:

```bash
php artisan queue:work --tries=1
```

To run scheduled booking reminders locally, start the scheduler in another terminal:

```bash
php artisan schedule:work
```

Generate or refresh the Swagger specification when the documentation configuration is ready:

```bash
php artisan l5-swagger:generate
```

The repository also documents a combined development command through Composer. Check `composer.json` and `BOOKLY-RUNBOOK.md` for the current local workflow and service requirements.

## Testing

The project uses Pest for automated tests. The current test suite is intended to cover areas such as:

- Booking lifecycle and role-based access.
- Availability rules and schedule exceptions.
- Cancellation and rescheduling policies.
- Payment idempotency and webhook replay handling.
- Concurrent booking attempts.

Run the tests using:

```bash
php artisan test --compact
```

Configure a dedicated test database before running tests. The reported setup uses a PostgreSQL database named `bookly_test`; verify the active test configuration before execution.

Test coverage and pass status should be confirmed against the current branch. This README does not claim that every test passes.

## Current Status and Limitations

Bookly is an ongoing development project, not a finished production-ready service.

Known limitations include:

- The application is currently single-tenant, with role-based ownership rather than full multi-tenant isolation.
- Payment processing currently uses a mock provider; live Stripe and PayPal integrations are not implemented.
- There is no completed application frontend.
- Some routes are incomplete or may reference missing controller methods, including reported work around nearby locations, provider-based location lookup, country-specific public holidays, email verification, location reports, and push-notification settings. These items must be rechecked against the current code before use.
- CI/CD, Docker configuration, and a project license have not yet been established.

These limitations may change as development continues.

## Security Notes

- Keep `.env`, Firebase service-account credentials, API tokens, webhook secrets, and database dumps out of version control.
- Use local or dedicated test databases for development and testing.
- Review authorization, validation, rate limiting, webhook handling, and booking concurrency protections before deploying the application.
- Do not expose the development configuration or treat the current project as production-ready without a separate security and deployment review.

## Roadmap

Potential future work includes:

- Completing and verifying unfinished API endpoints.
- Adding automated CI checks.
- Adding Docker-based local development configuration.
- Implementing a real payment gateway integration.
- Improving API and operational documentation.
- Reviewing test coverage and production-readiness requirements.
- Adding a frontend if the project scope calls for one.

## License

No license has been specified yet. Until a license is added, reuse and redistribution permissions should not be assumed.

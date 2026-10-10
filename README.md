# Bookly

**Bookly** is a modular, API-first appointment and booking platform built with Laravel. It connects **customers** who want to book services with **providers** who offer them, while giving **admins** the tools to supervise users, bookings, payments, and sensitive operations.

Bookly is designed around a real availability engine: time slots are derived dynamically from provider working hours, service durations, holidays, schedule exceptions, and existing bookings — with concurrency-safe booking creation, idempotent requests, and a full audit trail.

---

## Introduction

The platform supports three actor roles on a single `users` table:

| Role | Responsibilities |
| --- | --- |
| **Admin** | Manage users & providers, supervise bookings, review audit logs, run reports (bookings, revenue, providers, services, locations). |
| **Provider** | Manage business profile, branches/locations, services, working hours, holidays and schedule exceptions, and bookings. |
| **Customer** | Discover services, view available slots, create, cancel and reschedule bookings, and pay. |

Core guarantees:

- Availability is recalculated on the server and never trusted from the client.
- Booking price is snapshotted server-side (`price_at_booking`).
- Concurrent requests cannot double-book the same slot (DB unique index + transaction).
- Write operations support idempotency keys to make retries safe.
- Sensitive operations are recorded with actor, reason, before/after values, IP and user agent.

---

## Tech Stack

### Backend

| Concern | Technology |
| --- | --- |
| Language | PHP **8.3** |
| Framework | Laravel **13** |
| API Authentication | Laravel Sanctum **4** (personal access tokens) |
| API CSRF | Custom double-submit token middleware (`csrf.api`) |
| Authorization | Role middleware (`CheckRole`), ability middleware (`CheckAbility`), Eloquent policies |
| Payment Integration | Gateway abstraction (`PaymentGateway` contract) + webhook signature verification |
| Push Notifications | Firebase Cloud Messaging via `kreait/firebase-php` **8** |
| API Documentation | OpenAPI / Swagger via `darkaonline/l5-swagger` **11** |
| Cache & Queue | Redis (`predis` / `phpredis`) |

### Database

| Concern | Technology |
| --- | --- |
| Primary Database | **PostgreSQL** (`pgsql`) |
| Key types | `BIGSERIAL` PKs, `TIMESTAMPTZ`, native `ENUM` types, `JSONB` payloads |
| Integrity | Foreign keys, CHECK constraints, partial unique indexes |
| Soft Deletes | Users, locations, services, bookings |

### Frontend / Tooling

| Concern | Technology |
| --- | --- |
| Bundler | Vite **8** + `laravel-vite-plugin` |
| CSS | Tailwind CSS **4** |
| Testing | Pest **4** + `pest-plugin-laravel` |
| Formatter | Laravel Pint |
| Dev utilities | Laravel Boost, Laravel Pail, Laravel Tinker |

### Architecture

Bookly is a **modular monolith** (Domain-Driven Design style). Each domain lives under `app/Modules/{Module}` with a consistent internal structure:

```
app/Modules/{Module}/
├── Http/{Controllers,Requests,Resources}
├── Models/
├── Services/        # orchestration
├── Actions/         # single write operations
├── Queries/         # read operations
├── Enums/
└── Exceptions/
```

New modules are scaffolded with:

```bash
php artisan make:module ModuleName
```

**Domain modules:** `Auth`, `Admin`, `AuditLog`, `Availability`, `Booking`, `BookingSlot`, `BookingStatusLog`, `Category`, `Location`, `Notification`, `Payment`, `ProviderProfile`, `PublicHoliday`, `ScheduleException`, `Service`, `ServiceLocation`, `WorkingHour`.

The flow is consistently: **Controller → Form Request (validation) → Service → Action/Query → Model → API Resource.**

---

## Features

### Authentication & Accounts
- Register / login / logout / logout-all with Sanctum tokens.
- Email verification (signed URL) and resend verification.
- Profile view/update, change password, account suspension handling.
- Role and status enforcement (`active` / `suspended`).

### Provider & Catalog Management
- Provider profiles (business name, bio, phone, timezone).
- Multi-branch locations with city, country, coordinates (`lat`/`lng`) and timezone, including geo "nearby" search.
- Categories for service organization.
- Services with duration and server-authoritative price, soft deletes, and search/filtering.
- Service ↔ Location linking (`service_locations`) with unique pairing.

### Availability Engine
- Weekly **working hours** per provider/location/day.
- **Schedule exceptions** (`holiday` = closed, `custom_hours` = override) checked before normal hours.
- **Public holidays** (per-location or per-country).
- Slots generated dynamically from service duration and cached (5 min) via `AvailabilityService`.

### Bookings
- Full lifecycle: `pending → confirmed → cancelled / completed / no_show`.
- Create, cancel, reschedule, and status-change with business-rule enforcement.
- Transactional creation, slot re-validation, and DB-level double-booking protection (`uq_booking_slot`).
- Append-only `booking_status_logs` recording actor and reason for every transition.
- Configurable **cancellation & rescheduling policies** (blocked for `completed`/`cancelled`).
- Idempotent booking creation via middleware + `idempotency_keys` store.

### Payments
- Gateway abstraction (`PaymentGateway`) with a `MockPaymentProvider` and `mock/stripe/paypal` enum.
- Payment lifecycle: `pending / paid / failed / refunded`.
- Signature-verified webhooks with idempotent processing via `webhook_events`.
- Provider payments listing.

### Notifications
- In-app database notifications with read/unread/count and filters.
- Push notifications through a custom Firebase channel; device token management.
- Notification settings and admin broadcast / send-to-role / send-to-users.
- Scheduled **booking reminders** dispatched hourly via queue.

### Admin, Auditing & Reporting
- User management (create, update, delete, suspend, activate).
- Audit log search/filter (polymorphic, append-only).
- Reports: bookings, revenue, providers, services, locations, most-booked services (cached).

### Platform Hardening
- Per-route rate limiting / throttling.
- Input sanitization middleware on all API requests.
- Consistent JSON error envelope with `error_code`, `message`, `errors`, and 401/403/404/405/409/422/429/500 handling.

---

## Database / ERD Overview

> The authoritative schema lives in `database/migrations`. `bookly_schema.sql` is an early PostgreSQL design reference; the running application uses `service_locations` as the booking anchor.

### Tables

| Group | Tables |
| --- | --- |
| Identity | `users`, `provider_profiles`, `personal_access_tokens`, `api_csrf_tokens`, `user_device_tokens` |
| Catalog | `categories`, `services`, `locations`, `service_locations` |
| Scheduling | `working_hours`, `schedule_exceptions`, `public_holidays` |
| Booking | `bookings`, `booking_slots`, `booking_status_logs`, `booking_reminders` |
| Payment | `payments`, `webhook_events` |
| Platform | `notifications`, `audit_logs`, `idempotency_keys`, `failed_jobs` |

### Relationships

```text
users (role: admin | provider | customer)
 ├─1:1─ provider_profiles
 ├─1:N─ locations ──1:N── working_hours
 │                 ├─1:N── schedule_exceptions
 │                 ├─1:N── public_holidays
 │                 └─1:N── service_locations ──1:N── booking_slots
 ├─1:N─ services ──1:N── service_locations ──1:N── bookings
 ├─1:N─ bookings (as customer)                         │
 │                                                    ├─1:N── booking_status_logs
 │                                                    ├─1:N── payments
 │                                                    ├─1:1── booking_slots.booking_id
 │                                                    └─1:N── booking_reminders
 ├─1:N─ notifications
 ├─1:N─ audit_logs
 ├─1:N─ idempotency_keys
 └─1:N─ user_device_tokens

services ──N:1── categories
service_locations (unique service_id + location_id)
bookings (soft deletes, unique service_location_id + starts_at  → uq_booking_slot)
webhook_events (unique event_id → idempotent webhook processing)
```

### Integrity Highlights
- `bookings.idempotency_key` — client-controlled request idempotency.
- `payments.idempotency_key` / `payments.transaction_id` — payment-level safeguards.
- CHECK constraints on time ranges (`ends_at > starts_at`) and non-negative amounts.
- Native PostgreSQL ENUMs for `user_role`, `user_status`, `booking_status`, `payment_status`, `payment_provider`, `service_status`, `day_of_week`, `exception_type`.
- `JSONB` for audit values, webhook payloads, idempotency responses and notification data.

---

## Setup Instructions

### Prerequisites
- PHP **8.3+** with required extensions
- Composer
- Node.js + npm
- **PostgreSQL** running (database `bookly`)
- **Redis** running (cache, queue, sessions)

### 1. Install & Configure

```bash
composer install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bookly
DB_USERNAME=postgres
DB_PASSWORD=your_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
```

### 2. Migrate the Database

```bash
php artisan migrate
```

### 3. Frontend Assets

```bash
npm install
npm run build        # production build
# or: npm run dev    # hot-reload during development
```

### 4. Run the Application

Single command (server + queue worker + Vite):

```bash
composer run dev
```

Or run each process separately:

```bash
php artisan serve
php artisan queue:work
php artisan schedule:work
```

### One-Shot Setup

A convenience script runs the full first-time setup:

```bash
composer run setup
```

### Firebase (Push Notifications)
Place the service account JSON at `storage/app/firebase/firebase-credentials.json` (path configurable via `FIREBASE_CREDENTIALS`).

### API Documentation (Swagger)

```bash
php artisan l5-swagger:generate
```

Then browse the generated docs route (default `/api/documentation`).

### Testing

```bash
php artisan test --compact
# or
vendor/bin/pest
```

### Code Style

```bash
vendor/bin/pint --dirty --format agent
```

---

## Background Processes (Runbook)

| Process | Command | Purpose |
| --- | --- | --- |
| Queue Worker | `php artisan queue:work` | Notifications & queued jobs |
| Scheduler | `php artisan schedule:work` | Booking reminders (hourly) |
| Redis | (Docker container `redis`) | Cache & queue backend |
| Swagger | `php artisan l5-swagger:generate` | Regenerate API docs |

---

## API Overview

All routes are versioned under `/api/v1` and grouped by domain:

| Area | Base path |
| --- | --- |
| Auth | `/api/v1/auth/*` |
| Provider | `/api/v1/provider/*` |
| Services | `/api/v1/services/*` |
| Locations | `/api/v1/locations/*` |
| Categories | `/api/v1/categories/*` |
| Working Hours | `/api/v1/working-hours/*` |
| Schedule Exceptions | `/api/v1/schedule-exceptions/*` |
| Public Holidays | `/api/v1/public-holidays/*` |
| Bookings / Availability | `/api/v1/bookings/*`, `/api/v1/availability` |
| Payments | `/api/v1/payments/*` |
| Admin | `/api/v1/admin/*` |
| Health | `/api/v1/health` |

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

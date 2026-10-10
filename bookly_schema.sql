-- ============================================================
-- BOOKLY DATABASE SCHEMA
-- PostgreSQL DDL — Run in pgAdmin Query Tool
-- ============================================================

-- Enable UUID extension (optional, we use BIGSERIAL as primary key)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================================
-- 1. USERS
-- Core authentication table for all actors in the system.
-- A single user can be an Admin, Provider, or Customer based
-- on their role. All login, email verification, and password
-- reset logic is tied to this table. Soft delete is supported
-- so we never lose historical data tied to a user.
-- ============================================================
DO $$ BEGIN
    CREATE TYPE user_role AS ENUM ('admin', 'provider', 'customer');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE user_status AS ENUM ('active', 'suspended');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS users (
    id                  BIGSERIAL PRIMARY KEY,
    name                VARCHAR(255)        NOT NULL,
    email               VARCHAR(255)        NOT NULL UNIQUE,
    password            VARCHAR(255)        NOT NULL,
    role                user_role           NOT NULL DEFAULT 'customer',
    status              user_status         NOT NULL DEFAULT 'active',
    email_verified_at   TIMESTAMPTZ         NULL,
    created_at          TIMESTAMPTZ         NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ         NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMPTZ         NULL
);

CREATE INDEX IF NOT EXISTS idx_users_role     ON users (role);
CREATE INDEX IF NOT EXISTS idx_users_status   ON users (status);
CREATE INDEX IF NOT EXISTS idx_users_deleted  ON users (deleted_at);

-- ============================================================
-- 2. PROVIDER PROFILES
-- Extends the users table for users with role = 'provider'.
-- Stores business-specific information like business name, bio,
-- phone, and timezone. The timezone here is critical — it drives
-- the entire availability engine. One-to-one with users.
-- ============================================================
CREATE TABLE IF NOT EXISTS provider_profiles (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT          NOT NULL UNIQUE REFERENCES users (id) ON DELETE CASCADE,
    business_name   VARCHAR(255)    NOT NULL,
    bio             TEXT            NULL,
    phone           VARCHAR(50)     NULL,
    timezone        VARCHAR(100)    NOT NULL DEFAULT 'UTC',
    is_active       BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_provider_profiles_active    ON provider_profiles (is_active);

-- ============================================================
-- 3. LOCATIONS
-- Stores the physical location(s) of a provider's business.
-- A provider can have one or more branches. Each location has
-- its own address, city, country, and optional coordinates for
-- geo search. Customers need this to know where to show up for
-- their appointment. Bookings and services reference this table
-- to record exactly where the appointment takes place.
-- ============================================================
CREATE TABLE IF NOT EXISTS locations (
    id              BIGSERIAL PRIMARY KEY,
    provider_id     BIGINT          NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    name            VARCHAR(255)    NOT NULL DEFAULT 'Main Branch',
    address         VARCHAR(500)    NOT NULL,
    city            VARCHAR(100)    NOT NULL,
    country         VARCHAR(100)    NOT NULL,
    lat             DECIMAL(10, 7)  NULL,
    lng             DECIMAL(10, 7)  NULL,
    is_primary      BOOLEAN         NOT NULL DEFAULT TRUE,
    is_active       BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMPTZ     NULL
);

CREATE INDEX IF NOT EXISTS idx_locations_provider_id  ON locations (provider_id);
CREATE INDEX IF NOT EXISTS idx_locations_is_primary   ON locations (is_primary);
CREATE INDEX IF NOT EXISTS idx_locations_is_active    ON locations (is_active);
CREATE INDEX IF NOT EXISTS idx_locations_city         ON locations (city);
CREATE INDEX IF NOT EXISTS idx_locations_lat_lng      ON locations (lat, lng);

-- ============================================================
-- 4. CATEGORIES
-- A simple lookup table for organizing services into groups.
-- Examples: Hair, Dental, Fitness, Consulting. Admin manages
-- these. Each service belongs to one category, which helps
-- customers filter and discover services more easily.
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(255)    NOT NULL,
    slug            VARCHAR(255)    NOT NULL UNIQUE,
    description     TEXT            NULL,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);

-- ============================================================
-- 5. SERVICES
-- The services a provider offers to customers.
-- Each service has a duration (in minutes) which the availability
-- engine uses to calculate bookable time slots. The price here
-- is the source of truth — the server always uses this value
-- and never trusts the price sent by the client. Soft delete
-- ensures historical bookings are never orphaned.
-- ============================================================
DO $$ BEGIN
    CREATE TYPE service_status AS ENUM ('active', 'inactive');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS services (
    id              BIGSERIAL PRIMARY KEY,
    provider_id     BIGINT              NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    category_id     BIGINT              NOT NULL REFERENCES categories (id) ON DELETE RESTRICT,
    location_id     BIGINT              NULL REFERENCES locations (id) ON DELETE SET NULL,
    name            VARCHAR(255)        NOT NULL,
    description     TEXT                NULL,
    duration        INTEGER             NOT NULL CHECK (duration > 0),
    price           DECIMAL(10, 2)      NOT NULL CHECK (price >= 0),
    status          service_status      NOT NULL DEFAULT 'active',
    created_at      TIMESTAMPTZ         NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ         NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMPTZ         NULL
);

CREATE INDEX IF NOT EXISTS idx_services_provider_id   ON services (provider_id);
CREATE INDEX IF NOT EXISTS idx_services_category_id   ON services (category_id);
CREATE INDEX IF NOT EXISTS idx_services_location_id   ON services (location_id);
CREATE INDEX IF NOT EXISTS idx_services_status        ON services (status);
CREATE INDEX IF NOT EXISTS idx_services_price         ON services (price);
CREATE INDEX IF NOT EXISTS idx_services_deleted       ON services (deleted_at);

-- ============================================================
-- 6. WORKING HOURS
-- Defines the provider's repeating weekly schedule per location.
-- The availability engine reads this table first when calculating
-- open time slots for a given day. If a day is marked inactive,
-- no slots are generated for it. A unique constraint prevents
-- duplicate entries for the same provider, location, and day.
-- ============================================================
DO $$ BEGIN
    CREATE TYPE day_of_week AS ENUM (
        'monday', 'tuesday', 'wednesday',
        'thursday', 'friday', 'saturday', 'sunday'
    );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS working_hours (
    id              BIGSERIAL PRIMARY KEY,
    provider_id     BIGINT          NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    location_id     BIGINT          NULL REFERENCES locations (id) ON DELETE CASCADE,
    day_of_week     day_of_week     NOT NULL,
    start_time      TIME            NOT NULL,
    end_time        TIME            NOT NULL,
    is_active       BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_working_hours UNIQUE NULLS NOT DISTINCT (provider_id, location_id, day_of_week),
    CONSTRAINT chk_working_hours_time CHECK (end_time > start_time)
);

CREATE INDEX IF NOT EXISTS idx_working_hours_provider_id  ON working_hours (provider_id);
CREATE INDEX IF NOT EXISTS idx_working_hours_location_id  ON working_hours (location_id);
CREATE INDEX IF NOT EXISTS idx_working_hours_day          ON working_hours (day_of_week);

-- ============================================================
-- 7. SCHEDULE EXCEPTIONS
-- Handles one-off overrides to a provider's normal weekly schedule.
-- Two types: 'holiday' means the provider is fully unavailable
-- on that date, 'custom_hours' means they work different hours
-- than usual. This table is always checked BEFORE working_hours
-- when calculating slots — if a date has an entry here, it wins.
-- ============================================================
DO $$ BEGIN
    CREATE TYPE exception_type AS ENUM ('holiday', 'custom_hours');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS schedule_exceptions (
    id              BIGSERIAL PRIMARY KEY,
    provider_id     BIGINT              NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    location_id     BIGINT              NULL REFERENCES locations (id) ON DELETE CASCADE,
    date            DATE                NOT NULL,
    type            exception_type      NOT NULL,
    start_time      TIME                NULL,
    end_time        TIME                NULL,
    reason          VARCHAR(500)        NULL,
    created_at      TIMESTAMPTZ         NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ         NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_schedule_exception UNIQUE NULLS NOT DISTINCT (provider_id, location_id, date),
    CONSTRAINT chk_custom_hours CHECK (
        (type = 'holiday') OR
        (type = 'custom_hours' AND start_time IS NOT NULL AND end_time IS NOT NULL AND end_time > start_time)
    )
);

CREATE INDEX IF NOT EXISTS idx_schedule_exceptions_provider_id    ON schedule_exceptions (provider_id);
CREATE INDEX IF NOT EXISTS idx_schedule_exceptions_date           ON schedule_exceptions (date);
CREATE INDEX IF NOT EXISTS idx_schedule_exceptions_location_id    ON schedule_exceptions (location_id);

-- ============================================================
-- 8. BOOKINGS
-- The core transaction table of the entire system.
-- Records every appointment between a customer and a provider
-- for a specific service at a specific location. Times are always
-- stored in UTC and converted at the API layer. price_at_booking
-- is a snapshot of the service price at the time of booking so
-- future price changes do not affect historical records.
-- The idempotency_key prevents duplicate bookings on retries.
-- The unique constraint on (provider_id, location_id, starts_at)
-- is the last line of defense against double booking at DB level.
-- ============================================================
DO $$ BEGIN
    CREATE TYPE booking_status AS ENUM (
        'pending', 'confirmed', 'cancelled', 'completed', 'no_show'
    );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS bookings (
    id                  BIGSERIAL PRIMARY KEY,
    customer_id         BIGINT              NOT NULL REFERENCES users (id) ON DELETE RESTRICT,
    provider_id         BIGINT              NOT NULL REFERENCES users (id) ON DELETE RESTRICT,
    service_id          BIGINT              NOT NULL REFERENCES services (id) ON DELETE RESTRICT,
    location_id         BIGINT              NOT NULL REFERENCES locations (id) ON DELETE RESTRICT,
    starts_at           TIMESTAMPTZ         NOT NULL,
    ends_at             TIMESTAMPTZ         NOT NULL,
    status              booking_status      NOT NULL DEFAULT 'pending',
    price_at_booking    DECIMAL(10, 2)      NOT NULL CHECK (price_at_booking >= 0),
    idempotency_key     VARCHAR(255)        NOT NULL UNIQUE,
    notes               TEXT                NULL,
    created_at          TIMESTAMPTZ         NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ         NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMPTZ         NULL,

    CONSTRAINT chk_booking_time CHECK (ends_at > starts_at)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_booking_slot ON bookings (provider_id, location_id, starts_at)
WHERE status != 'cancelled' AND deleted_at IS NULL;

CREATE INDEX IF NOT EXISTS idx_bookings_customer_id   ON bookings (customer_id);
CREATE INDEX IF NOT EXISTS idx_bookings_provider_id   ON bookings (provider_id);
CREATE INDEX IF NOT EXISTS idx_bookings_service_id    ON bookings (service_id);
CREATE INDEX IF NOT EXISTS idx_bookings_location_id   ON bookings (location_id);
CREATE INDEX IF NOT EXISTS idx_bookings_status        ON bookings (status);
CREATE INDEX IF NOT EXISTS idx_bookings_starts_at     ON bookings (starts_at);
CREATE INDEX IF NOT EXISTS idx_bookings_provider_time ON bookings (provider_id, starts_at, ends_at);

-- ============================================================
-- 9. BOOKING STATUS LOGS
-- An append-only audit trail for every booking state transition.
-- Records who changed the status, from what state, to what state,
-- and why. This satisfies the requirement to "record the actor
-- and reason for sensitive operations." Never updated or deleted.
-- ============================================================
CREATE TABLE IF NOT EXISTS booking_status_logs (
    id              BIGSERIAL PRIMARY KEY,
    booking_id      BIGINT          NOT NULL REFERENCES bookings (id) ON DELETE CASCADE,
    from_status     booking_status  NULL,
    to_status       booking_status  NOT NULL,
    changed_by      BIGINT          NOT NULL REFERENCES users (id) ON DELETE RESTRICT,
    reason          TEXT            NULL,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_booking_status_logs_booking_id     ON booking_status_logs (booking_id);
CREATE INDEX IF NOT EXISTS idx_booking_status_logs_changed_by     ON booking_status_logs (changed_by);

-- ============================================================
-- 10. PAYMENTS
-- Tracks the payment lifecycle for each booking.
-- Uses a payment abstraction so multiple providers (mock, Stripe,
-- PayPal) can be supported. The payload column stores the raw
-- gateway response for debugging — no card data is ever stored
-- here. The idempotency_key ensures a payment is never processed
-- twice even if the request is retried.
-- ============================================================
DO $$ BEGIN
    CREATE TYPE payment_status AS ENUM ('pending', 'paid', 'failed', 'refunded');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE payment_provider AS ENUM ('mock', 'stripe', 'paypal');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS payments (
    id                  BIGSERIAL PRIMARY KEY,
    booking_id          BIGINT              NOT NULL REFERENCES bookings (id) ON DELETE RESTRICT,
    amount              DECIMAL(10, 2)      NOT NULL CHECK (amount >= 0),
    status              payment_status      NOT NULL DEFAULT 'pending',
    provider            payment_provider    NOT NULL DEFAULT 'mock',
    transaction_id      VARCHAR(255)        NULL,
    idempotency_key     VARCHAR(255)        NOT NULL UNIQUE,
    payload             JSONB               NULL,
    created_at          TIMESTAMPTZ         NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ         NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_payments_booking_id        ON payments (booking_id);
CREATE INDEX IF NOT EXISTS idx_payments_status            ON payments (status);
CREATE INDEX IF NOT EXISTS idx_payments_transaction_id    ON payments (transaction_id);

-- ============================================================
-- 11. WEBHOOK EVENTS
-- Ensures webhook processing is idempotent.
-- When a payment gateway fires a webhook, we first check if the
-- event_id already exists in this table. If it does, we skip it.
-- If it does not, we process it and set processed_at. This
-- prevents double-processing when gateways retry webhook delivery.
-- ============================================================
CREATE TABLE IF NOT EXISTS webhook_events (
    id              BIGSERIAL PRIMARY KEY,
    event_id        VARCHAR(255)    NOT NULL UNIQUE,
    provider        VARCHAR(100)    NOT NULL,
    event_type      VARCHAR(100)    NOT NULL,
    payload         JSONB           NOT NULL,
    processed_at    TIMESTAMPTZ     NULL,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_webhook_events_provider        ON webhook_events (provider);
CREATE INDEX IF NOT EXISTS idx_webhook_events_processed_at    ON webhook_events (processed_at);

-- ============================================================
-- 12. NOTIFICATIONS
-- Stores in-app and database notifications for users.
-- Each notification has a type and a JSON data payload so it
-- can carry any event-specific information. read_at tracks
-- whether the user has seen it. Email notifications are handled
-- separately through the queue worker and do not need a row here.
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT          NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    type        VARCHAR(255)    NOT NULL,
    data        JSONB           NOT NULL,
    read_at     TIMESTAMPTZ     NULL,
    created_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_notifications_user_id  ON notifications (user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_read_at  ON notifications (read_at);
CREATE INDEX IF NOT EXISTS idx_notifications_type     ON notifications (type);

-- ============================================================
-- 13. AUDIT LOGS
-- A polymorphic, append-only log of all sensitive operations
-- across the platform. Covers any model (Booking, User, Payment)
-- via auditable_type and auditable_id. Records who did what,
-- the before and after values, and the request context (IP,
-- user agent). This table is never updated or hard-deleted.
-- Financial and historical records depend on it.
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT          NULL REFERENCES users (id) ON DELETE SET NULL,
    action          VARCHAR(255)    NOT NULL,
    auditable_type  VARCHAR(100)    NOT NULL,
    auditable_id    BIGINT          NOT NULL,
    old_values      JSONB           NULL,
    new_values      JSONB           NULL,
    ip_address      VARCHAR(45)     NULL,
    user_agent      TEXT            NULL,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id         ON audit_logs (user_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action          ON audit_logs (action);
CREATE INDEX IF NOT EXISTS idx_audit_logs_auditable       ON audit_logs (auditable_type, auditable_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at      ON audit_logs (created_at);

-- ============================================================
-- 14. IDEMPOTENCY KEYS
-- Central idempotency store for the entire API.
-- When a client sends a request with an idempotency key, we
-- check this table first. If the key exists and has not expired,
-- we return the stored response without reprocessing. This
-- prevents duplicate bookings, payments, or any other operation
-- from being executed twice due to network retries.
-- ============================================================
CREATE TABLE IF NOT EXISTS idempotency_keys (
    id              BIGSERIAL PRIMARY KEY,
    key             VARCHAR(255)    NOT NULL UNIQUE,
    user_id         BIGINT          NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    request_path    VARCHAR(500)    NOT NULL,
    response_code   INTEGER         NOT NULL,
    response_body   JSONB           NOT NULL,
    expires_at      TIMESTAMPTZ     NOT NULL,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_idempotency_keys_user_id       ON idempotency_keys (user_id);
CREATE INDEX IF NOT EXISTS idx_idempotency_keys_expires_at    ON idempotency_keys (expires_at);

-- ============================================================
-- DONE
-- 14 Tables | All indexes | All constraints | All ENUMs
-- ============================================================

# Bookly — Requirements

## 1. Actors

### 1.1 Admin

The Admin is responsible for managing and supervising the platform.

Main responsibilities:

- Manage users.
- Manage providers.
- Manage bookings.
- Review sensitive operations and audit logs.

### 1.2 Provider

The Provider is a service provider who offers services through Bookly.

Main responsibilities:

- Manage provider profile.
- Manage business settings.
- Create and manage services.
- Define working hours.
- Define holidays and schedule exceptions.
- Manage bookings.

### 1.3 Customer

The Customer is a user who wants to discover services and book available time slots.

Main responsibilities:

- Discover available services.
- View available time slots.
- Create bookings.
- Cancel bookings.
- Reschedule bookings.
## 2. Use Cases

### 2.1 Admin Use Cases

The Admin can:

- Manage users.
- Manage providers.
- Manage bookings.
- View audit logs.
- Search and filter audit logs.
### 2.2 Provider Use Cases

The Provider can:

- Manage provider profile.
- Manage business settings.
- Create and manage services.
- Define working hours.
- Define holidays.
- Define schedule exceptions.
- Manage bookings.
### 2.3 Customer Use Cases

The Customer can:

- Discover services.
- View available time slots.
- Create a booking.
- Cancel a booking.
- Reschedule a booking.

## 3. Business Rules

### 3.1 Booking Rules

- A booking must be created only for an available time slot.
- The server must re-check availability when creating a booking.
- Two concurrent requests must not be able to successfully book the same slot.
- Booking creation must be performed inside a database transaction.
- Booking creation must support an idempotency key to prevent duplicate bookings caused by request retries.
- The booking price must be calculated or verified on the server and must not be trusted from the client.
- Every booking must have an explicit state.
- Booking state transitions must follow defined business rules.
- Cancellation and rescheduling must follow defined business rules.
- Sensitive booking operations should record the actor and the reason.

## 4. Booking States

A booking can have one of the following states:

- `pending`
- `confirmed`
- `cancelled`
- `completed`
- `no_show`

## 5. Cancellation & Rescheduling Rules

### 5.1 Cancellation

- A `completed` booking cannot be cancelled.
- A `cancelled` booking cannot be cancelled again.
- A `pending` booking can be cancelled.
- A `confirmed` booking can be cancelled.
- The system must record who cancelled the booking.
- The system should record the reason for cancellation.

### 5.2 Rescheduling

- A `completed` booking cannot be rescheduled.
- A `cancelled` booking cannot be rescheduled.
- A `pending` booking can be rescheduled if the new slot is available.
- A `confirmed` booking can be rescheduled if the new slot is available.
- The new slot must be validated for availability.
- The original slot must be released after a successful rescheduling.
- Rescheduling must not create a double booking.

### 6.1 Slot Storage Strategy

Bookly will calculate available slots dynamically rather than storing
every generated slot as a database record.

The system will derive availability from:

- Provider working hours.
- Service duration.
- Holidays.
- Schedule exceptions.
- Existing bookings.
- Current time.
- Provider timezone.

Booking concurrency will be handled separately during booking creation.
# Design Document — Workshop Registration Service

## Stack Choices

| Layer      | Choice               | Why |
|------------|----------------------|-----|
| **Backend**    | Laravel 12           | Mature PHP framework with built-in ORM (Eloquent), migrations, middleware, and validation. Its `DB::transaction` and `lockForUpdate()` methods directly solve the concurrency problem described in the brief. |
| **Auth**       | Laravel Breeze (Blade) | Lightweight authentication scaffolding. No SPA complexity needed — this is a 15-person internal tool, not a public-facing app. Server-rendered pages keep things simple and fast. |
| **Frontend**   | Blade + Tailwind CSS | Server-side rendering is the right fit here: fewer moving parts, no API layer to maintain, and the team is non-technical so reliability matters more than interactivity. |
| **Database**   | MySQL                | Chosen specifically for proper **row-level pessimistic locking** (`SELECT ... FOR UPDATE`). This is the mechanism that prevents the double-booking problem described in the brief. |

## Design Decisions

### 1. Access Control — Backend Enforcement, Not UI Hiding

The spec states: *"Anything not marked must be refused by the backend, not just hidden in the interface."*

Every route group is protected by a `CheckRole` middleware that returns **403 Forbidden** if the user's role doesn't match. The permission matrix is:

| Action                       | Admin | Manager | Staff |
|------------------------------|-------|---------|-------|
| Create user accounts & roles | ✅     | ❌       | ❌     |
| Add & edit workshops         | ❌     | ✅       | ❌     |
| Register & cancel attendees  | ❌     | ✅       | ✅     |
| View workshops & history     | ❌     | ✅       | ✅     |

Notably, Admin is **not** a superuser. Admin can only manage user accounts — they cannot access workshops or registrations. This matches the spec exactly.

### 2. How Over-Registration Is Prevented

This is the core technical challenge. The brief describes two staff members promising the last seat simultaneously.

**Solution: Pessimistic locking inside a database transaction.**

```php
DB::transaction(function () use ($workshop, $validated) {
    // Acquire an exclusive row-level lock on this workshop
    $locked = Workshop::lockForUpdate()->findOrFail($workshop->id);

    // Count active registrations while holding the lock
    $count = Registration::where('workshop_id', $locked->id)
        ->where('status', 'active')->count();

    // Reject if full
    if ($count >= $locked->capacity) {
        throw new \Exception('Workshop is full.');
    }

    // Safe to create — no other transaction can read/write this row
    return Registration::create([...]);
});
```

**How it works:** `lockForUpdate()` issues a MySQL `SELECT ... FOR UPDATE`, which acquires an exclusive row-level lock. If a second request arrives at the same instant, it **blocks** at the lock until the first transaction commits. It then re-reads the count and sees the seat is taken. Capacity can never be exceeded.

**Why pessimistic over optimistic:** The brief describes a high-contention scenario ("Saturday mornings are chaos"). Pessimistic locking guarantees correctness under contention without retry loops or version conflicts.

### 3. Registration History — Never Delete

Cancellation sets `status = 'cancelled'` and records `cancelled_by` (which staff member) and `cancelled_at` (timestamp). The original record is never deleted. A dedicated history view shows every registration — active and cancelled — with full attribution.

### 4. No Public Signup

The register route is removed entirely. The first Admin is database-seeded. Only Admins can create new accounts through the user management interface.

## Assumptions

- **Single timezone:** All dates stored and displayed in the server's timezone. A multi-timezone setup would add complexity without clear benefit for three local locations.
- **Attendees don't have accounts:** They are name + email entered by staff, as described in the brief. If attendees needed self-service, this would become a different product.
- **Workshop status is manual:** Managers set status (scheduled/ongoing/completed/cancelled). Automatic status transitions based on date could be added but weren't in scope.

## Trade-offs

- **Server-rendered vs SPA:** Chose Blade over React/Vue. Trades interactivity for simplicity and reliability. For a 15-person internal tool, this is the right call — fewer things to break, no API versioning, no CORS.
- **Pessimistic vs optimistic locking:** Pessimistic locking holds a row lock during the transaction, which reduces throughput under extreme load. For 15 staff members, this is a non-issue. The correctness guarantee outweighs the performance cost.
- **Single repo:** Backend and frontend live in the same Laravel project. Simpler to deploy and test, at the cost of not being able to scale them independently.

## Bonus Features Implemented

- **Audit trail:** Every user CRUD, workshop edit, and registration/cancellation is logged with who did it, when, and old/new values. Admin-only view at `/audit-logs`.
- **Workshop filtering:** Filter by status, date range, available seats, and free-text search.
- **Extra workshop fields:** Added `location` and `description` beyond the spreadsheet spec.

## What I Skipped

- **Waitlist:** The optional bonus. When full, attendees could be queued and auto-offered seats on cancellation.
- **Email notifications:** Confirmation emails on registration, notifications for waitlisted attendees.
- **Automated tests:** Feature tests for every route, especially concurrent registration stress tests.
- **Export:** CSV/PDF export of registration lists for workshops.
- **Live deployment:** Focused on getting the core working correctly over hosting setup.

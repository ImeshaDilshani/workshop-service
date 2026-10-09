# WorkshopHub — Workshop Registration Service

A full-stack workshop registration system built with **Laravel 12 + Blade + Tailwind CSS**. Designed for a community training centre to manage workshops, registrations, and staff accounts with role-based access control.

---

## Quick Start

### Prerequisites

- **PHP 8.2+**
- **MySQL 8.0+** (XAMPP, Laragon, or standalone)
- **Composer**
- **Node.js 18+** & npm

### Setup

```bash
# 1. Clone and install
git clone <repo-url> && cd workshop-service
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Create MySQL database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS workshop_service;"
# Then update .env with your DB credentials if different from root/no-password

# 4. Migrate & seed
php artisan migrate:fresh --seed

# 5. Build frontend assets
npm run build

# 6. Run
php artisan serve
```

Open **http://localhost:8000** and log in.

### Seeded Account

| Role  | Email              | Password   |
|-------|--------------------|------------|
| Admin | admin@workshop.com | `password` |

> Only the Admin is seeded (as required by the spec). Log in as Admin, go to **Users → Create User** to add Manager and Staff accounts.

Six sample workshops are pre-loaded so you can start testing immediately.

---

## Architecture & Design Decisions

### Stack Choices

| Layer      | Choice               | Why                                                                 |
|------------|----------------------|---------------------------------------------------------------------|
| Backend    | Laravel 12           | Mature, batteries-included PHP framework with excellent ORM, auth, migrations, and middleware |
| Auth       | Laravel Breeze       | Lightweight auth scaffolding with Blade — no SPA complexity needed for a 15-person internal tool |
| Frontend   | Blade + Tailwind CSS | Server-rendered views are fast to build, simple to deploy, and perfect for an internal CRUD app |
| Database   | MySQL                | Proper row-level `SELECT ... FOR UPDATE` locking essential for concurrent registration safety |
| Locking    | Pessimistic locks    | `SELECT ... FOR UPDATE` in a transaction prevents concurrent overbooking |

### Permission Matrix (Backend-Enforced)

The spec explicitly states: *"Anything not marked must be refused by the backend, not just hidden in the interface."*

| Action                        | Admin | Manager | Staff |
|-------------------------------|-------|---------|-------|
| Create user accounts & roles  | ✅     | ❌       | ❌     |
| Add & edit workshops          | ❌     | ✅       | ❌     |
| Register & cancel attendees   | ❌     | ✅       | ✅     |
| View workshops & history      | ❌     | ✅       | ✅     |

Enforcement is via `CheckRole` middleware on routes (e.g., `->middleware('role:manager')`), not UI hiding. An Admin who manually crafts a request to `/workshops` gets a **403 Forbidden**.

### How Over-Registration Is Prevented

The core race condition: *"Two of us promising the last seat at the same time."*

```php
DB::transaction(function () use ($workshop, $validated) {
    // Lock the workshop row — blocks any other concurrent registration
    $lockedWorkshop = Workshop::lockForUpdate()->findOrFail($workshop->id);

    // Count active registrations while holding the lock
    $activeCount = Registration::where('workshop_id', $lockedWorkshop->id)
        ->where('status', 'active')
        ->count();

    if ($activeCount >= $lockedWorkshop->capacity) {
        throw new \Exception('This workshop is full.');
    }

    return Registration::create([...]);
});
```

`lockForUpdate()` acquires a row-level pessimistic lock on the workshop row in MySQL. If two staff members submit at the exact same moment, one transaction blocks until the other commits. The second transaction then re-reads the active count and sees the seat is taken. **Capacity can never be exceeded.**

### Registration History

Records are **never deleted**. Cancelling a registration flips its `status` to `cancelled` and records:
- `cancelled_by` — which staff member cancelled it
- `cancelled_at` — when it happened

The full history (active + cancelled) is always accessible via the workshop detail page and the dedicated history view.

### Bonus Features Implemented

- **Audit Trail**: Every user creation/update/deletion, workshop creation/edit, and registration/cancellation is logged with who did it, when, and old/new values. Viewable at `/audit-logs` (Admin only).
- **Extra fields**: Added `location` and `description` to workshops beyond the spreadsheet spec.

### What I'd Add With More Time

- **Waitlist**: Queue attendees when full, auto-offer seats on cancellation
- **Email notifications**: Confirm registrations, notify waitlisted attendees
- **Comprehensive test suite**: Feature tests for every route, especially concurrency tests
- **API endpoints**: REST API for potential future mobile or external integrations
- **Export**: CSV/PDF export of registration lists per workshop

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php    # Role-aware dashboard
│   │   ├── UserController.php         # Admin-only CRUD
│   │   ├── WorkshopController.php     # Manager create/edit, all view
│   │   ├── RegistrationController.php # Pessimistic-locked registration
│   │   └── AuditLogController.php     # Admin-only audit viewer
│   └── Middleware/
│       └── CheckRole.php              # Backend role enforcement
├── Models/
│   ├── User.php                       # + role column & helpers
│   ├── Workshop.php                   # + query scopes for filtering
│   ├── Registration.php               # + active/cancelled scopes
│   └── AuditLog.php                   # Polymorphic audit trail
database/
├── migrations/                        # users+role, workshops, registrations, audit_logs
└── seeders/
    └── DatabaseSeeder.php             # Admin + sample data
resources/views/
├── dashboard.blade.php                # Role-specific dashboards
├── workshops/                         # index, create, edit, show
├── registrations/                     # create, history
├── users/                             # index, create, edit (Admin only)
└── audit/                             # index (Admin only)
```

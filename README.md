# WorkshopHub — Workshop Registration Service

A full-stack workshop registration system built with **Laravel 12 + Blade + Tailwind CSS** for a community training centre.

---

## Prerequisites

- **PHP 8.2+**
- **MySQL 8.0+** (XAMPP, Laragon, or standalone)
- **Composer**
- **Node.js 18+** & npm

## Setup

```bash
# 1. Clone and install dependencies
git clone <repo-url>
cd workshop-service
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Create MySQL database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS workshop_service;"

# 4. Update .env if your MySQL credentials differ from defaults (root / no password)
#    DB_USERNAME=root
#    DB_PASSWORD=

# 5. Run migrations and seed the admin account + sample workshops
php artisan migrate --seed

# 6. Build frontend assets
npm run build

# 7. Start the server
php artisan serve
```

Open **http://localhost:8000** in your browser.

## Seeded Admin Login

| Role  | Email              | Password   |
|-------|--------------------|------------|
| Admin | admin@workshop.com | `password` |

Only the Admin account is seeded. Log in as Admin, navigate to **Users → Create User** to add Manager and Staff accounts.

Six sample workshops are pre-loaded so you can try registrations straight away.

## How to Test Each Role

1. **Admin** — Log in with the seeded account. You'll see the user management dashboard. Go to **Users → Create User** to create a Manager and a Staff account.
2. **Manager** — Log in with the manager account you created. You'll see the workshop dashboard with stats and upcoming workshops. You can add/edit workshops and register/cancel attendees.
3. **Staff** — Log in with the staff account you created. You can view workshops, register attendees, and cancel registrations. You cannot add/edit workshops or manage users.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php    # Role-aware dashboard
│   │   ├── UserController.php         # Admin-only user CRUD
│   │   ├── WorkshopController.php     # Manager: create/edit, Staff: view
│   │   ├── RegistrationController.php # Pessimistic-locked registration
│   │   └── AuditLogController.php     # Admin-only audit viewer
│   └── Middleware/
│       └── CheckRole.php              # Backend role enforcement (403)
├── Models/
│   ├── User.php                       # role column + helpers
│   ├── Workshop.php                   # query scopes for filtering
│   ├── Registration.php               # active/cancelled + relationships
│   └── AuditLog.php                   # Polymorphic audit trail
database/
├── migrations/                        # users, workshops, registrations, audit_logs
└── seeders/
    └── DatabaseSeeder.php             # Admin account + sample workshops
resources/views/
├── dashboard.blade.php                # Role-specific dashboards
├── workshops/                         # index, create, edit, show
├── registrations/                     # create, history
├── users/                             # index, create, edit (Admin only)
└── audit/                             # index (Admin only)
```

## Design Document

See [DESIGN.md](DESIGN.md) for stack choices, design decisions, trade-offs, and how over-registration is prevented.

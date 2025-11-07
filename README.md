# Just In Case

A web application that enables users to securely store critical information and automatically deliver it to designated recipients if they stop confirming their status through a timer-based system.

## What It Does

Users create "custodianships" containing messages and attachments that are automatically sent to chosen recipients when a countdown timer expires. By regularly resetting these timers, users confirm everything is okay. If they stop resetting, the system assumes something has happened and delivers the stored information.

**Disclaimer:** This is NOT a legal testament or legal service. It is purely an automated message delivery system based on timer mechanisms.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Vue.js 3 with Inertia.js, Vite, Tailwind CSS 4.0
- **Database:** SQLite (dev), MySQL 8.0 (production via Sail)
- **Storage:** Spatie Media Library (local/S3)
- **Email:** Mailgun (configurable)
- **Queue:** Redis
- **Testing:** PHPUnit, Playwright

## Setup

### Quick Start
```bash
composer setup
```
This installs dependencies, creates .env, generates app key, runs migrations, and builds assets.

### Docker Development (Laravel Sail)
```bash
./vendor/bin/sail up
```

Available services:
- MySQL 8.0 (port 3306)
- Redis (port 6379)
- Meilisearch (port 7700)
- Mailpit (SMTP: 1025, Dashboard: 8025)

### Development Server
```bash
composer dev
```
Runs concurrently:
- PHP dev server
- Queue worker
- Log viewer (pail)
- Vite HMR

## Testing

```bash
php artisan test           # All tests
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
npx playwright test        # E2E tests
```

## Key Features

- Timer-based custodianship system with automatic expiration
- Multi-recipient support with attachment handling
- Email delivery with retry logic and webhook tracking
- Draft/active status management
- Delivery status tracking (delivered, failed, partially delivered)
- Comprehensive audit logging (resets, deliveries, downloads)

## Code Quality

```bash
./vendor/bin/pint          # Format code (PSR-12)
php artisan pail           # Monitor logs
```

## License

Proprietary - All Rights Reserved

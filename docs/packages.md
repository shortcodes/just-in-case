# Zainstalowane Pakiety i Konfiguracja - Just In Case MVP

**Data:** 2025-10-20
**Status:** Instalacja zakończona pomyślnie

---

## Spis treści

1. [Backend Packages (Composer)](#backend-packages-composer)
2. [Frontend Packages (NPM)](#frontend-packages-npm)
3. [Infrastructure (Docker Compose)](#infrastructure-docker-compose)
4. [Konfiguracja Środowiska](#konfiguracja-środowiska)
5. [Struktura Projektu](#struktura-projektu)
6. [Weryfikacja Instalacji](#weryfikacja-instalacji)

---

## Backend Packages (Composer)

### 1. Authentication & Authorization

#### Laravel Breeze (v2.3.8)
- **Przeznaczenie:** Authentication scaffolding z Inertia + Vue + TypeScript
- **Instalacja:** `composer require laravel/breeze --dev`
- **Zawiera:**
  - Fortify functionality (login, register, password reset, email verification)
  - Pre-built Vue components dla auth
  - Middleware configuration
  - Routes dla autentykacji

#### Laravel Sanctum (v4.2.0)
- **Przeznaczenie:** API authentication (zainstalowane przez Breeze)
- **Użycie:** SPA authentication, API tokens

#### Inertia Laravel (v2.0.10)
- **Przeznaczenie:** SPA adapter bez REST API overhead
- **Instalacja:** Automatycznie przez Breeze
- **Funkcjonalność:** Server-side routing z client-side rendering

#### Tightenco Ziggy (v2.6.0)
- **Przeznaczenie:** Laravel routes w JavaScript/TypeScript
- **Instalacja:** Automatycznie przez Breeze
- **Użycie:** `route('dashboard')` w Vue components

---

### 2. Media & File Management

#### Spatie Laravel Media Library (v11.15.0)
- **Przeznaczenie:** Zarządzanie załącznikami (upload, storage, download)
- **Instalacja:** `composer require "spatie/laravel-medialibrary:^11.0"`
- **Dokumentacja PRD:** REQ-016 do REQ-020
- **Konfiguracja:**
  - Default disk: `s3` (MinIO w dev, AWS S3 w prod)
  - Max file size: 10MB (zgodne z planem free)
  - Queue conversions: enabled

**Zależności:**
- `spatie/image` (v3.8.6) - Image processing
- `spatie/image-optimizer` (v1.8.0) - Image optimization
- `spatie/temporary-directory` (v2.3.0) - Temp files handling
- `maennchen/zipstream-php` (v3.2.0) - Streaming zip archives

**Migracja:**
```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```

**Tabela utworzona:** `media` (18 kolumn, zgodna z db-plan.md section 1.6)

---

### 3. Cloud Storage (AWS S3 / MinIO)

#### League Flysystem AWS S3 v3 (v3.30.1)
- **Przeznaczenie:** S3 storage adapter
- **Instalacja:** `composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies`
- **Dokumentacja PRD:** REQ-017, Section 6.1 (tech_stack.md)

**Zależności:**
- `aws/aws-sdk-php` (v3.356.42) - AWS SDK
- `aws/aws-crt-php` (v1.2.7) - AWS Common Runtime
- `mtdowling/jmespath.php` (v2.8.0) - JSON query language

**Konfiguracja S3 disk** (`config/filesystems.php`):
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'), // MinIO w dev
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
],
```

---

### 4. Email Delivery

#### Symfony Mailgun Mailer (v7.3.1)
- **Przeznaczenie:** Transactional email dla produkcji
- **Instalacja:** `composer require symfony/mailgun-mailer symfony/http-client`
- **Dokumentacja PRD:** REQ-027 do REQ-032, Section 7.1 (tech_stack.md)

**Zależności:**
- `symfony/http-client` (v7.3.4)
- `symfony/http-client-contracts` (v3.6.0)

**Funkcjonalność:**
- Webhooks dla delivery tracking
- Bounce handling (hard bounce, soft bounce)
- Retry logic z exponential backoff
- Email deliverability monitoring

**Konfiguracja produkcyjna** (`.env`):
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-api-key
MAILGUN_ENDPOINT=api.mailgun.net
```

---

## Frontend Packages (NPM)

### 1. Core Framework

#### Vue 3 (^3.4.0)
- **Przeznaczenie:** Frontend framework
- **Instalacja:** Automatycznie przez Breeze
- **Użycie:** Reactive UI components

#### @inertiajs/vue3 (^2.0.0)
- **Przeznaczenie:** Inertia adapter dla Vue
- **Instalacja:** Automatycznie przez Breeze
- **Funkcjonalność:** SPA navigation bez REST API

#### TypeScript (^5.6.3)
- **Przeznaczenie:** Type safety dla Vue components
- **Instalacja:** Automatycznie przez Breeze
- **Konfiguracja:** `tsconfig.json` z path aliases (`@/*`)

---

### 2. Build Tools

#### Vite (^7.0.0)
- **Przeznaczenie:** Fast build tool z HMR
- **Instalacja:** Zainstalowane w projekcie
- **Uwaga:** Wymagał downgrade `@vitejs/plugin-vue` do v6.0 dla kompatybilności

#### @vitejs/plugin-vue (^6.0.0)
- **Przeznaczenie:** Vite plugin dla Vue 3
- **Uwaga:** Wersja 6.0 wspiera Vite 7 (fix dla PR #475 w Breeze)

#### Laravel Vite Plugin (^2.0.0)
- **Przeznaczenie:** Laravel integration dla Vite
- **Konfiguracja:** `vite.config.js` wskazuje na `resources/js/app.ts`

---

### 3. UI Components

#### Shadcn-vue (v2.3.2)
- **Przeznaczenie:** Tailwind-native UI components library
- **Instalacja:** `npm install shadcn-vue`
- **Dokumentacja:** Section 2.4 (tech_stack.md)
- **Charakterystyka:**
  - Copy-paste components (nie node_module dependency)
  - Accessibility built-in (WCAG 2.1)
  - TypeScript support
  - Customizable via Tailwind

#### Radix Vue (v1.9.17)
- **Przeznaczenie:** Headless UI primitives (dependency dla shadcn-vue)
- **Instalacja:** `npm install radix-vue`
- **Funkcjonalność:** Unstyled, accessible components

**Inicjalizacja:**
```bash
npx shadcn-vue@latest init
```

**Komponenty dostępne:**
- Modal, Dialog, Dropdown, Progress Bar, Toast
- Button, Input, Checkbox, Select
- Data Table, Form, Popover, Tabs

---

### 4. Utilities

#### DayJS (v1.11.18)
- **Przeznaczenie:** Date/time formatting dla timer displays
- **Instalacja:** `npm install dayjs`
- **Dokumentacja PRD:** REQ-033 (progress timerów), METRIC-001
- **Użycie:** Formatowanie `next_trigger_at`, `last_reset_at`

**Przykład użycia:**
```typescript
import dayjs from 'dayjs';

const daysRemaining = dayjs(nextTriggerAt).diff(dayjs(), 'day');
const formattedDate = dayjs(lastResetAt).format('DD/MM/YYYY HH:mm');
```

#### @vueuse/core (v13.9.0)
- **Przeznaczenie:** Vue composition utilities
- **Instalacja:** `npm install @vueuse/core`
- **Funkcjonalność:** Reactivity helpers, browser APIs, animations

**Użyteczne funkcje dla projektu:**
- `useIntervalFn` - Timer countdown w UI
- `useStorage` - Local/session storage
- `useClipboard` - Copy to clipboard
- `useConfirmDialog` - Confirmation modals

---

### 5. Styling

#### Tailwind CSS (^4.0.0)
- **Przeznaczenie:** Utility-first CSS framework
- **Instalacja:** Zainstalowane przez Breeze
- **Plugin:** `@tailwindcss/vite` (^4.0.0)
- **Forms:** `@tailwindcss/forms` (^0.5.3)

---

## Infrastructure (Docker Compose)

### Services w `compose.yaml`

#### 1. Laravel Application
```yaml
laravel.test:
  image: sail-8.4/app
  ports:
    - "${APP_PORT:-80}:80"
    - "${VITE_PORT:-5173}:${VITE_PORT:-5173}"
```

#### 2. MySQL 8.0
```yaml
mysql:
  image: mysql/mysql-server:8.0
  ports:
    - "${FORWARD_DB_PORT:-3306}:3306"
```
- **Przeznaczenie:** Primary database
- **Volume:** `sail-mysql`
- **Database:** `laravel`
- **Credentials:** `sail` / `password`

#### 3. Redis
```yaml
redis:
  image: redis:alpine
  ports:
    - "${FORWARD_REDIS_PORT:-6379}:6379"
```
- **Przeznaczenie:** Cache & session storage
- **Volume:** `sail-redis`

#### 4. MinIO (S3-compatible)
```yaml
minio:
  image: minio/minio:latest
  ports:
    - "${FORWARD_MINIO_PORT:-9000}:9000"
    - "${FORWARD_MINIO_CONSOLE_PORT:-8900}:8900"
  command: minio server /data/minio --console-address ":8900"
```
- **Przeznaczenie:** S3-compatible storage dla development
- **Volume:** `sail-minio`
- **Bucket:** `justincase`
- **Credentials:** `sail` / `password`
- **API:** `http://localhost:9000`
- **Console:** `http://localhost:8900`

**Utworzenie bucketa:**
```bash
./vendor/bin/sail exec minio mc alias set myminio http://localhost:9000 sail password
./vendor/bin/sail exec minio mc mb myminio/justincase
```

#### 5. Mailpit (Email Testing)
```yaml
mailpit:
  image: axllent/mailpit:latest
  ports:
    - "${FORWARD_MAILPIT_PORT:-1025}:1025"
    - "${FORWARD_MAILPIT_DASHBOARD_PORT:-8025}:8025"
```
- **Przeznaczenie:** Email testing dla development
- **SMTP:** `localhost:1025`
- **Dashboard:** `http://localhost:8025`

#### 6. Meilisearch
```yaml
meilisearch:
  image: getmeili/meilisearch:latest
  ports:
    - "${FORWARD_MEILISEARCH_PORT:-7700}:7700"
```
- **Przeznaczenie:** Search engine
- **Volume:** `sail-meilisearch`

#### 7. Selenium
```yaml
selenium:
  image: selenium/standalone-chromium
```
- **Przeznaczenie:** Browser testing

---

## Konfiguracja Środowiska

### Kluczowe Zmienne Środowiskowe

Projekt wymaga skonfigurowania następujących grup zmiennych w pliku `.env`:

#### Application
- `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL`

#### Database
- `DB_CONNECTION=mysql`
- `DB_HOST=mysql` (nazwa serwisu w Docker Compose)
- Pozostałe credentials zgodnie z `.env.example`

#### Storage (MinIO for Development)
- `AWS_BUCKET=justincase`
- `AWS_ENDPOINT=http://minio:9000` (dla MinIO w dev)
- `AWS_USE_PATH_STYLE_ENDPOINT=true` (wymagane dla MinIO)
- Production: użyj prawdziwych AWS credentials i usuń `AWS_ENDPOINT`

#### Email (Mailpit for Development)
- `MAIL_MAILER=smtp`
- `MAIL_HOST=mailpit`
- `MAIL_PORT=1025`
- `MAIL_FROM_ADDRESS="noreply@justincase.local"`

#### Email (Mailgun for Production)
- `MAILGUN_DOMAIN` - twoja domena w Mailgun
- `MAILGUN_SECRET` - API key z Mailgun
- `MAILGUN_ENDPOINT=api.mailgun.net`
- **Uwaga:** W produkcji zmień `MAIL_MAILER=mailgun`

**Wszystkie credentials i hasła znajdują się w pliku `.env` (gitignored). Nigdy nie commituj pliku `.env` do repozytorium.**

---

## Struktura Projektu

### Backend

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Auth/          # Breeze auth controllers
│   └── Middleware/        # Inertia middleware
├── Models/
│   └── User.php           # User model (Breeze)
└── Providers/

config/
├── filesystems.php        # S3 disk configured
├── media-library.php      # Spatie config (disk: s3, max: 10MB)
└── mail.php              # Mail configuration

database/
└── migrations/
    ├── 0001_01_01_000000_create_users_table.php
    ├── 0001_01_01_000001_create_cache_table.php
    ├── 0001_01_01_000002_create_jobs_table.php
    └── 2025_10_20_214719_create_media_table.php
```

### Frontend

```
resources/
├── css/
│   └── app.css           # Tailwind imports
└── js/
    ├── Components/       # Breeze UI components (13 files)
    │   ├── ApplicationLogo.vue
    │   ├── Checkbox.vue
    │   ├── DangerButton.vue
    │   ├── Dropdown.vue
    │   ├── InputError.vue
    │   ├── InputLabel.vue
    │   ├── Modal.vue
    │   ├── PrimaryButton.vue
    │   ├── SecondaryButton.vue
    │   └── TextInput.vue
    ├── Layouts/          # 2 layouty
    │   ├── AuthenticatedLayout.vue
    │   └── GuestLayout.vue
    ├── Pages/
    │   ├── Auth/         # 6 auth pages
    │   │   ├── ConfirmPassword.vue
    │   │   ├── ForgotPassword.vue
    │   │   ├── Login.vue
    │   │   ├── Register.vue
    │   │   ├── ResetPassword.vue
    │   │   └── VerifyEmail.vue
    │   ├── Profile/      # 3 profile pages
    │   │   ├── Edit.vue
    │   │   ├── Partials/
    │   │   │   ├── DeleteUserForm.vue
    │   │   │   ├── UpdatePasswordForm.vue
    │   │   │   └── UpdateProfileInformationForm.vue
    │   ├── Dashboard.vue
    │   └── Welcome.vue
    ├── types/            # TypeScript definitions
    │   ├── global.d.ts
    │   └── index.d.ts
    ├── app.ts            # Main entry point (Inertia setup)
    └── bootstrap.ts      # Axios configuration
```

### Configuration Files

```
root/
├── vite.config.js        # Vite + Vue + Laravel plugin
├── tsconfig.json         # TypeScript config (paths: @/*)
├── tailwind.config.js    # Tailwind CSS config
├── package.json          # NPM dependencies
├── composer.json         # Composer dependencies
├── compose.yaml          # Docker Compose services
└── .env                  # Environment variables
```

---

## Weryfikacja Instalacji

### Backend Verification

#### 1. Composer Packages
```bash
./vendor/bin/sail composer show | grep -E "(breeze|inertia|spatie|flysystem|mailgun)"
```

**Expected output:**
```
inertiajs/inertia-laravel       v2.0.10
laravel/breeze                  v2.3.8
league/flysystem-aws-s3-v3     3.30.1
spatie/laravel-medialibrary    11.15.0
symfony/mailgun-mailer         v7.3.1
```

#### 2. Database Tables
```bash
./vendor/bin/sail artisan db:show
```

**Expected tables:**
- `users`
- `media` ✅ (Spatie)
- `password_reset_tokens`
- `sessions`
- `cache`, `cache_locks`
- `jobs`, `job_batches`, `failed_jobs`
- `migrations`

#### 3. Storage (MinIO/S3)
```bash
./vendor/bin/sail artisan tinker --execute="
use Illuminate\Support\Facades\Storage;
Storage::disk('s3')->put('test.txt', 'Hello MinIO');
print_r(Storage::disk('s3')->files());
"
```

**Expected output:**
```
Array ( [0] => test.txt )
```

#### 4. Email (Mailpit)
```bash
./vendor/bin/sail artisan tinker --execute="
use Illuminate\Support\Facades\Mail;
Mail::raw('Test email', function(\$m) {
    \$m->to('test@example.com')->subject('Test');
});
echo 'Email sent!';
"
```

**Verification:** Check `http://localhost:8025`

---

### Frontend Verification

#### 1. NPM Packages
```bash
./vendor/bin/sail npm list --depth=0
```

**Expected packages:**
```
@inertiajs/vue3@2.0.0
@vitejs/plugin-vue@6.0.0
@vueuse/core@13.9.0
dayjs@1.11.18
radix-vue@1.9.17
shadcn-vue@2.3.2
typescript@5.6.3
vite@7.0.0
vue@3.4.0
```

#### 2. Build Process
```bash
./vendor/bin/sail npm run build
```

**Expected output:**
```
✓ 780 modules transformed.
✓ built in X.XXs
```

**No errors, no TypeScript errors, 0 vulnerabilities**

#### 3. Dev Server
```bash
./vendor/bin/sail npm run dev
```

**Expected:**
- Vite dev server na `http://localhost:5173`
- HMR (Hot Module Replacement) działa

---

### Infrastructure Verification

#### 1. Docker Containers
```bash
./vendor/bin/sail ps
```

**Expected containers (all running):**
- `laravel.test`
- `mysql`
- `redis`
- `minio` ✅
- `mailpit` ✅
- `meilisearch`
- `selenium`

#### 2. MinIO Console
- URL: `http://localhost:8900`
- Login: `sail` / `password`
- Bucket: `justincase` should exist

#### 3. Mailpit Dashboard
- URL: `http://localhost:8025`
- Should show sent test emails

---

## Rozwiązane Problemy podczas Instalacji

### Problem 1: Vite 7 vs @vitejs/plugin-vue 5
**Objaw:** `ERESOLVE` error podczas instalacji Breeze

**Rozwiązanie:**
- Upgrade `@vitejs/plugin-vue` z `^5.0.0` do `^6.0.0`
- Zgodnie z PR #475 w laravel/breeze
- `@vitejs/plugin-vue@6.0.1` wspiera Vite 7

### Problem 2: MinIO bucket persistence
**Objaw:** Bucket `justincase` znikał po restarcie kontenerów

**Rozwiązanie:**
- Volume `sail-minio` utworzony w `compose.yaml`
- Manual bucket creation via `mc` client:
```bash
./vendor/bin/sail exec minio mc alias set myminio http://localhost:9000 sail password
./vendor/bin/sail exec minio mc mb myminio/justincase
```

### Problem 3: Mailpit port typo
**Objaw:** Sail nie startował (invalid hostPort: t)

**Rozwiązanie:**
- Fix w `compose.yaml` line 94:
- Przed: `${FORWARD_MAILPIT_DASHBOARD_PORT:-t}:8025`
- Po: `${FORWARD_MAILPIT_DASHBOARD_PORT:-8025}:8025`

---

## Następne Kroki

Zgodnie z dokumentacją PRD i db-plan.md:

### 1. Database Schema
Utworzyć migracje dla:
- `custodianships` (table 1.3 w db-plan.md)
- `custodianship_messages` (table 1.4)
- `recipients` (table 1.5)
- `deliveries` (table 1.7)
- `resets` (table 1.8)
- `downloads` (table 1.9)

### 2. Models
Utworzyć Eloquent models z relationships:
- `Custodianship` - implement `HasMedia` interface
- `Recipient`
- `Delivery`
- `Reset`
- `Download`

### 3. Controllers
Implementacja CRUD dla:
- `CustodianshipController`
- `RecipientController`
- Attachment handling (via Spatie Media Library)

### 4. Frontend Components
Shadcn-vue components dla:
- Dashboard z timer progress bars
- Custodianship CRUD forms
- Modals (delete confirmation, reset timer confirmation)
- File upload UI

### 5. Cron Jobs
Laravel scheduled tasks dla:
- Timer checking (every minute)
- Email sending (queued jobs)
- Reminder emails (7 days before expiration)

---

## Referencje

- **PRD:** `docs/prd.md`
- **MVP:** `docs/mvp.md`
- **Tech Stack:** `docs/tech_stack.md`
- **Database Plan:** `docs/db-plan.md`

---

**Dokument utworzony:** 2025-10-20
**Ostatnia aktualizacja:** 2025-10-20
**Status:** ✅ Wszystkie pakiety zainstalowane i zweryfikowane

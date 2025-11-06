# Stack Technologiczny - Just In Case

**Wersja:** 1.0
**Data:** 2025-10-14
**Status:** Zatwierdzony po analizie

---

## 1. Przegląd architektury

Just In Case to monolityczna aplikacja SPA (Single Page Application) oparta na:
- **Backend:** Laravel 12 API
- **Frontend:** Vue 3 + Inertia.js
- **Infrastruktura:** AWS EC2 + AWS S3
- **Model:** Freemium SaaS

---

## 2. Frontend Stack

### 2.1 Framework aplikacyjny

**Vue 3 + Inertia.js**

**Uzasadnienie:**
- Vue zapewnia reaktywność i komponentową architekturę wymaganą dla dynamic UI (timers, progress bars, modals)
- Inertia.js eliminuje potrzebę REST API - backend renderuje komponenty Vue poprzez kontrolery Laravel
- Brak potrzeby state management (Vuex/Pinia) - Inertia zarządza stanem przez props
- Szybszy development niż pełny REST + SPA
- Laravel Breeze z Inertia starter dostępny out-of-the-box

**Wymagania PRD adresowane:**
- REQ-033: Dashboard z real-time timer displays
- REQ-036: Szybki reset bez przeładowania strony
- US-008: Edycja z modalem potwierdzenia resetu timera

### 2.2 Typowanie

**TypeScript 5**

**Uzasadnienie:**
- Statyczne typowanie redukuje błędy runtime w kluczowych funkcjach (timer calculations, date handling)
- Lepsze wsparcie IDE dla team development
- Type safety dla Inertia props przekazywanych z Laravel
- Standard w enterprise Vue applications

**Trade-off:** Dodatkowy overhead developmentowy (+15-20% czasu), ale akceptowalny dla długoterminowej maintainability.

### 2.3 Styling

**Tailwind CSS 4**

**Uzasadnienie:**
- Utility-first approach przyspiesza development UI
- Wbudowana responsywność (mobile-first requirement z PRD)
- Mały bundle size w production (unused CSS purged)
- Świetna integracja z Vue
- Laravel Breeze używa Tailwind domyślnie

### 2.4 Biblioteka komponentów

**Shadcn-vue**

**Uzasadnienie:**
- Port oficjalnego Shadcn/ui dla Vue 3 (https://www.shadcn-vue.com)
- Headless UI components oparte na Radix Vue (pełna kontrola nad stylingiem)
- Accessibility built-in (WCAG 2.1 compliance)
- Komponenty wymagane przez PRD: Modal, Dialog, Dropdown, Progress Bar, Toast
- Tailwind-native (komponenty zbudowane z Tailwind utility classes)
- Copy-paste approach (komponenty jako kod źródłowy, nie node_module dependency)
- TypeScript support out-of-the-box
- Aktywnie rozwijany przez Vue community

**Alternatywy odrzucone:**
- Radix Vue (bezpośrednio) - zbyt niskopoziomowe, Shadcn-vue dostarcza higher-level abstrakcje
- PrimeVue - zbyt opinionated, trudniejsze customizowanie, cięższy bundle
- Element Plus - ciężki bundle, design nie pasuje do Tailwind aesthetic

### 2.5 Build tooling

**Vite 5**

**Uzasadnienie:**
- Hot Module Replacement (HMR) szybsze niż Webpack
- Native ES modules - faster builds
- Domyślny bundler w Laravel 11+
- Oficjalnie wspierany przez Vue

---

## 3. Backend Stack

### 3.1 Framework

**Laravel 12 (PHP 8.4)**

**Uzasadnienie:**
- Mature framework z wbudowaną funkcjonalnością wymaganą przez PRD:
  - Authentication (REQ-001 - REQ-005)
  - Email queue (REQ-027 - REQ-032)
  - Task scheduling (REQ-022: cron job co minutę)
  - File storage abstraction (REQ-016 - REQ-020: S3 integration)
  - Rate limiting (REQ-031: 10 pobrań/h)
- Laravel 12 eliminuje boilerplate (slim structure, auto-registering commands)
- PHP 8.4 performance improvements (JIT compiler)
- Ekosystem Laravel (Forge, Pulse, Horizon) redukuje DevOps overhead

**Kluczowe features wykorzystane:**
- Queue workers dla async email delivery
- Task scheduler dla cron job sprawdzającego timery
- Events & Listeners dla audit logging (resets, deliveries, downloads)
- Policies dla authorization (REQ-033: tylko owner może edytować)
- Form Requests dla validation (REQ-007: limity freemium)

---

## 4. Baza danych

### 4.1 Primary database

**MySQL 8.0**

**Uzasadnienie:**
- Proven reliability dla transactional workloads
- AWS EC2 as database -  Managed 
- Laravel Query Builder abstrakcja - możliwość zmiany na PostgreSQL bez refactoringu
- Team familiarity (mniejsza krzywa uczenia)

**Data model requirements (PRD):**
- Users, custodianships, recipients, attachments, resets (audit log)
- Relacje 1:N (user → custodianships, custodianship → attachments)
- Indeksy na: next_trigger_at (cron job queries), status, user_id

**Uwagi:**
- PostgreSQL oferuje lepsze features (JSON, row-level security), ale MySQL wystarczający dla MVP
- Możliwa migracja post-MVP jeśli potrzebne advanced features

---

## 5. Queue & Cache

### 5.1 Redis

**Uzasadnienie:**
- Queue backend dla Laravel jobs (email sending, file processing)
- Session storage (szybsze niż database)
- Cache layer dla expensive queries (dashboard stats, użytkownicy z multiple custodianships)
- Pub/Sub dla real-time notifications (opcjonalne, post-MVP)

**Wymagania PRD adresowane:**
- REQ-030: Retry logic z exponential backoff (Laravel Queue + Redis)
- REQ-022: Async processing cron job results

**Deployment:**
- Development: Redis na localhost
- Production: Managed Redis na AWS EC2 ($15/m) lub self-hosted na EC2 (MVP)

---

## 6. Storage & CDN

### 6.1 Załączniki

**AWS S3**

**Uzasadnienie:**
- Unlimited storage (REQ-016: 10MB per custodianship × potencjalnie tysiące użytkowników)
- Server-side encryption at-rest z KMS (REQ-034, US-034)
- High availability (99.99% SLA)
- Lifecycle policies (automatyczne archiwizowanie starych załączników)
- Cost-effective ($0.023/GB/month)

**Security requirements (PRD):**
- Bucket bez publicznego dostępu (REQ-017)
- Aplikacja jako proxy (REQ-020: tokenized URLs z rate limiting)
- Encryption: AWS KMS

**Struktura bucketów:**
- `justincase-attachments-production`: Aktywne załączniki
- `justincase-backups-production`: Database backups (separate bucket per REQ)

---

## 7. Email Delivery

### 7.1 Transactional email

**Mailgun**

**Uzasadnienie:**
- Dedicated transactional email service (nie marketing platform)
- Webhooks dla delivery tracking (REQ-029, REQ-031, REQ-032)
- Retry logic built-in
- Bounce classification (hard bounce vs soft bounce)
- Free tier: 5,000 emails/month (wystarczające dla early MVP)

**Wymagania PRD adresowane:**
- REQ-027: Automatyczna wysyłka przy wygaśnięciu timera
- REQ-029: Webhook integration dla delivery status
- REQ-031: Bounce handling (hard bounce → delivery_failed)
- REQ-037: Reminder emails (7 dni przed wygaśnięciem)

**Konfiguracja wymagana:**
- SPF, DKIM, DMARC records dla domeny (zapobieganie spam classification)
- Webhook endpoint verification (signature validation)
- Dedicated sending domain: mail.justincase.com

**Alternatywy:**
- SendGrid, AWS SES, Postmark (podobna funkcjonalność i ceny)

---

## 8. Hosting & Infrastructure

### 8.1 Application hosting

**AWS EC2**

**Uzasadnienie:**
- Predictable pricing ($12/m za 2GB droplet wystarczający dla MVP)
- Full control nad server configuration
- SSH access dla debugging
- Możliwość vertical scaling (upgrade droplet size)

**Server requirements:**
- Ubuntu 22.04 LTS
- Nginx web server
- PHP 8.4-FPM
- MySQL 8.0 (self-hosted lub Managed)
- Redis (self-hosted lub Managed)
- Supervisor dla Laravel queue workers

**Trade-off:** Manual setup możliwy (oszczędność $12/m), ale wymaga 3-5 dni DevOps work i ongoing maintenance.


## 9. CI/CD

### 9.1 Continuous Integration

**GitHub Actions**

**Uzasadnienie:**
- Native integration z GitHub repository
- Free tier: 2,000 minutes/month (wystarczające dla MVP)
- YAML-based configuration
- Laravel testing pipeline ready (PHPUnit + Pint)

**Pipeline stages:**
1. Code quality: Laravel Pint (PSR-12 formatting)
2. Tests: PHPUnit (unit + feature tests)
3. Build: npm run build (Vite production bundle)
4. Deploy: rsync do AWS EC2 (jeśli tests pass)

---

## 10. Security & Monitoring

### 10.1 Encryption & Data Protection

**Database encryption:**
- Column-level encryption dla `custodianships.message_content` (AES-256 via Laravel encrypt())
- APP_KEY jako master encryption key (rotacja tylko przy initial setup)

**File encryption:**
- S3 server-side encryption z AWS KMS
- Tokenized download URLs (UUID v4, praktycznie nie do zgadnięcia)

**Session security:**
- HTTPS only (secure cookies)
- HTTP-only cookies (XSS protection)
- SameSite=strict (CSRF protection)

**Wymagania PRD adresowane:**
- US-033: Authorization policies (tylko owner może edytować)
- US-034: Encryption at-rest dla załączników
- US-035: Secure non-guessable links
- REQ-019: UUID-based tokenized URLs

### 10.2 Application Monitoring

**Nightwatch** (https://nightwatch.dev)

**Uzasadnienie:**
- All-in-one monitoring platform dla Laravel applications
- Free tier: 1 project (idealny dla MVP)
- Zastępuje potrzebę 3-4 różnych narzędzi (Sentry + Pulse + Schedule Monitor + uptime)

**Funkcjonalność pokrywająca wymagania PRD:**

1. **Error Tracking:**
   - Real-time exception monitoring z stack traces
   - Automatic error grouping
   - Laravel-native integration
   - Email/Slack notifications dla critical errors

2. **Application Performance Monitoring (APM):**
   - Slow query detection (SQL queries, Redis operations)
   - Request performance profiling
   - Memory usage tracking
   - Queue job monitoring

3. **Scheduled Task Monitoring:**
   - Automatyczne wykrywanie Laravel scheduled commands
   - Alert jeśli cron job fails lub nie wykonuje się
   - **Krytyczne dla REQ-022:** monitoring sprawdzania timerów co minutę
   - Execution time tracking

4. **Uptime Monitoring:**
   - HTTP endpoint checks (co 1-5 minut)
   - SSL certificate expiration monitoring
   - Email/SMS alerts przy downtime
   - Response time tracking

5. **Audit Logging:**
   - Automatic activity log dla user actions
   - Database query logging
   - Job execution history
   - Custom event tracking


**Alternatywa (jeśli Nightwatch nie spełni oczekiwań):**
- Sentry (error tracking) + UptimeRobot (uptime) + Laravel Pulse (APM) + Laravel Schedule Monitor
- Cost: wszystkie free tier, ale wymaga konfiguracji 4 różnych serwisów

### 10.3 Audit Logging

**Custom tables:**
- `resets`: user_id, custodianship_id, timestamp, IP, method
- `deliveries`: custodianship_id, recipient_email, status, message_id, timestamp
- `downloads`: attachment_id, IP, user_agent, timestamp

**Wymagania PRD adresowane:**
- REQ-024: Audit log resetów
- US-036: Logowanie kluczowych aktywności
- REQ-019: Logowanie każdego pobrania załącznika

**Retention policy:** 24 miesiące (compliance z typowymi wymaganiami audytowymi)

---

## 11. Backups & Disaster Recovery

### 11.1 Database backups

**AWS Backup (native)**

**Strategia:**
- **MySQL na EC2:** Automated EBS snapshots + mysqldump do S3
- **Post-MVP (RDS):** AWS RDS Automated Backups (point-in-time recovery)
- Retention: 7 daily, 4 weekly, 12 monthly
- Encrypted at-rest (AWS KMS)
- Cross-region replication (opcjonalne, post-MVP)

**Implementacja MVP (MySQL self-hosted na EC2):**
```bash
# Cron job: daily backup do S3
0 3 * * * mysqldump -u root -p$DB_PASS justincase | gzip | aws s3 cp - s3://justincase-backups-production/mysql/backup-$(date +\%Y\%m\%d-\%H\%M\%S).sql.gz
```

**AWS Backup Policy:**
- Lifecycle management: 7 dni snapshots → 30 dni archive
- Notification via SNS przy backup failures
- Automated testing: monthly restore verification

### 11.2 Recovery Time Objective (RTO)

**Target:** < 4 godziny od catastrophic failure do restored service

**Procedure:**
1. Provision new droplet (30 min)
2. Re*store database z latest backup (30 min)
3. Deploy application code (15 min)*
4. DNS cutover (0-48h TTL dependent)

---

## 12. Secrets Management

### 12.1 Development

**.env file** (gitignored)
- Local development credentials
- Dummy API keys

### 12.2 Production

**AWS Secrets Manager**

**Uzasadnienie:**
- Centralized secret storage dla AWS EC2 infrastructure
- Automatic rotation capable (DB passwords, API keys)
- Audit trail via CloudTrail (kto i kiedy dostał dostęp do secrets)
- Eliminuje plain-text credentials w .env na serwerze
- Native integration z AWS services (EC2, RDS, S3)
- Pricing: $0.40/secret/month + $0.05 per 10,000 API calls (szacunkowo $2-3/m dla MVP)

**Secrets required:**
- APP_KEY (Laravel encryption master key)
- DB_PASSWORD
- AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY
- MAILGUN_SECRET
- Redis password

**KRYTYCZNE:** APP_KEY nie może być rotowany po starcie produkcji (encrypted data stanie się nieczytelna)

---

## 13. Compliance & Legal

### 13.1 RODO / GDPR

**Scope:** MVP pomija automated GDPR compliance.

**Post-MVP requirements:**
- Data export endpoint (user może pobrać wszystkie swoje dane)
- Right to deletion (user może usunąć konto + wszystkie dane)
- Automated cleanup nieaktywnych kont (definicja: brak logowania 12+ miesięcy)

### 13.2 Disclaimer

**Wymagane w ToS i UI (REQ - dokumentacja):**
- "Just In Case NIE jest testamentem ani usługą prawną"
- "System nie gwarantuje dostarczenia wiadomości w 100%"
- "Użytkownik odpowiedzialny za legalność przechowywanych danych"

---

## 14. Koszty miesięczne (MVP estimate)

```
AWS EC2 t2.small (1 vCPU, 2GB RAM):   ~$17/m
MySQL 8.0 (self-hosted na EC2):       $0 (included w EC2)
Redis (self-hosted na EC2):           $0 (included w EC2)
AWS S3 storage (50GB):                $1-3
AWS S3 bandwidth (100GB):             $1-5
AWS Secrets Manager (5 secrets):      $2-3
Mailgun (free tier):                  $0 (do 5,000 emails/m)
Nightwatch (free tier):               $0 (1 project)
GitHub Actions (free tier):           $0 (2,000 min/m)
Domain (.com):                        $12/year ($1/m)
SSL (Let's Encrypt):                  $0
---------------------------------------------------
TOTAL (minimal):                      ~$20-28/m
```

**Uwagi:**
- EC2 t2.small wystarczający dla MVP (500-1000 users)
- Scaling: t2.medium (~$35/m) dla 1000-5000 users
- Alternative: AWS Lightsail ($10-12/m) prostszy niż EC2, ale mniej elastyczny

**Breakeven:** ~100-150 użytkowników jeśli monetyzacja paid plan $5-10/m.

---


## 15. Podsumowanie kluczowych decyzji

| Kategoria | Technologia | Uzasadnienie |
|-----------|-------------|--------------|
| Frontend | Vue 3 + Inertia.js | SPA without REST API overhead, Laravel integration |
| Typing | TypeScript 5 | Type safety dla timer logic i date calculations |
| Styling | Tailwind 4 | Fast development, small bundle, responsive out-of-box |
| Components | Shadcn-vue | Copy-paste components, Tailwind-native, accessible |
| Backend | Laravel 12 | Mature ecosystem, built-in features dla wszystkich PRD requirements |
| Database | MySQL 8.0 | Proven reliability, team familiar, self-hostable |
| Queue/Cache | Redis | Fast, reliable, Laravel native support |
| Storage | AWS S3 | Unlimited, encrypted, lifecycle policies |
| Email | Mailgun | Transactional focus, webhooks, bounce handling |
| Hosting | AWS EC2 | Full control, predictable costs, scalable |
| CI/CD | GitHub Actions | Free, native GitHub integration |
| Monitoring | Nightwatch | All-in-one: errors, APM, cron, uptime, audit |

---

## 16. Skalowanie post-MVP

### 16.1 Horizontal scaling path

**Gdy EC2 instance osiągnie limit:**
1. Load balancer (AWS ALB: ~$20/m)
2. Multiple application servers (2-3 EC2 instances)
3. Separate database server (AWS RDS MySQL: ~$30/m)
4. Redis cluster (AWS ElastiCache: ~$15/m)
5. CDN dla static assets (CloudFront + S3)

### 16.2 Estimated scaling thresholds

- **1 EC2 t2.small:** 0-1,000 active users
- **2 instances + ALB:** 1,000-5,000 users
- **3+ instances + RDS:** 5,000-20,000 users

**Wąskie gardło:** MySQL concurrent connections (~150 default). Rozwiązanie: connection pooling + RDS read replicas.

---

## 17. Ryzyka i mitygacje

| Ryzyko | Prawdopodobieństwo | Wpływ | Mitygacja |
|--------|-------------------|-------|-----------|
| Cron job failure (timery nie wygasają) | Średnie | Krytyczny | Nightwatch scheduled task monitoring + email alerts |
| Email deliverability < 90% | Średnie | Wysokie | SPF/DKIM/DMARC setup, dedicated sending domain, Mailgun reputation monitoring |
| S3 costs explosion | Niskie | Średnie | Lifecycle policies (archive po 12 miesiącach), user upload limits enforced |
| Database encryption key leak (APP_KEY) | Niskie | Krytyczny | AWS Secrets Manager, limited IAM access, no rotation post-production |
| MySQL connection pool exhaustion | Średnie (post-MVP) | Wysokie | Connection pooling, read replicas, upgrade do AWS RDS |
| DDoS na download endpoints | Średnie | Średnie | Rate limiting (10/h per IP), AWS WAF, CloudFlare proxy (opcjonalne) |
| Nightwatch service downtime | Niskie | Średnie | Fallback do lokalnych logs + alternatywne narzędzia (Sentry) ready |

---

## 18. Załączniki

### 18.1 Package dependencies (Laravel)

**Core:**
- laravel/framework: ^12.0
- inertiajs/inertia-laravel: ^1.0
- laravel/breeze: ^2.0 (Inertia + Vue starter)

**Monitoring:**
- nightwatch/nightwatch: ^1.0 (all-in-one monitoring)

**Features:**
- spatie/laravel-backup: ^9.0 (automated database + file backups)
- bepsvpt/laravel-security-header: ^7.0 (CSP, HSTS, X-Frame-Options)

**Opcjonalne:**
- spatie/laravel-activitylog: ^4.0 (enhanced audit logging)

### 18.2 Package dependencies (Frontend)

**Core:**
- vue: ^3.4
- @inertiajs/vue3: ^1.0
- typescript: ^5.3
- vite: ^5.0

**UI:**
- shadcn-vue: ^0.10 (copy-paste Tailwind components)
- radix-vue: ^1.9 (dependency dla shadcn-vue)
- tailwindcss: ^4.0

**Utilities:**
- dayjs: ^1.11 (date formatting dla timer displays)
- @vueuse/core: ^10.0 (composition utilities)

---

**Dokument zatwierdzony:** 2025-10-14
**Następny review:** Po wdrożeniu MVP (szacowane: Q1 2026)

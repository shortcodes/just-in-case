# Specyfikacja Architektury Autentykacji - Just In Case

**Wersja:** 1.0
**Data:** 2025-11-06
**Status:** Zaimplementowane (Laravel Breeze)

---

## 1. PRZEGLĄD

System autentykacji Just In Case został zaimplementowany z wykorzystaniem Laravel Breeze (wersja 2.3.8) w połączeniu z Inertia.js i Vue 3. Obejmuje rejestrację, logowanie, weryfikację emaila oraz resetowanie hasła zgodnie z wymaganiami PRD (US-001 do US-005).

---

## 2. ARCHITEKTURA INTERFEJSU UŻYTKOWNIKA

### 2.1 Widoki autentykacji

**Register - `/register`**
- Formularz rejestracyjny: nazwa, email, hasło (potwierdź hasło)
- Walidacja: email unikalny, hasło min. 8 znaków
- Po rejestracji: automatyczne logowanie i wysyłka emaila weryfikacyjnego
- Przekierowanie: Dashboard z bannerem weryfikacji

**Login - `/login`**
- Formularz logowania: email, hasło, checkbox "Remember me"
- Link "Forgot password?" prowadzi do formularza reset
- Link do rejestracji dla nowych użytkowników
- Możliwość logowania przed weryfikacją emaila (zgodnie z REQ-003)
- Przekierowanie: Dashboard

**ForgotPassword - `/forgot-password`**
- Formularz: email użytkownika
- Komunikat sukcesu zawsze wyświetlany (ochrona przed user enumeration)
- Wysyłka linku resetującego (ważność: 1 godzina)

**ResetPassword - `/reset-password/{token}`**
- Formularz: nowe hasło (potwierdź hasło)
- Email pre-filled (read-only)
- Walidacja tokena: weryfikacja ważności i autentyczności
- Po sukcesie: przekierowanie do Login z komunikatem

**VerifyEmail - `/verify-email`**
- Przypomnienie o weryfikacji emaila
- Przycisk ponownego wysłania linku weryfikacyjnego
- Link weryfikacyjny ważny przez 24 godziny
- Po weryfikacji: przekierowanie do Dashboard

### 2.2 Layout i nawigacja

**GuestLayout**
- Minimalistyczny layout dla widoków autentykacji
- Logo aplikacji (klikalne, prowadzi do strony głównej)
- Biała karta z cieniem na szarym tle
- Responsywny design (max-width 28rem na desktop)

**Banner weryfikacji emaila**
- Wyświetlany na górze Dashboard dla użytkowników bez zweryfikowanego emaila
- Amber/żółte tło (nieintrusywny warning)
- Przycisk inline do ponownego wysłania emaila
- Automatycznie znika po weryfikacji

### 2.3 Komponenty współdzielone

- Button (Shadcn-vue) - wszystkie przyciski akcji
- Input (Shadcn-vue) - pola tekstowe i email
- Label (Shadcn-vue) - etykiety formularzy
- Checkbox (Shadcn-vue) - "Remember me"
- Alert (Shadcn-vue) - banner weryfikacji, komunikaty statusu

### 2.4 User Flow

**Rejestracja → Pierwsze powiernictwo:**
1. Wypełnienie formularza Register
2. Automatyczne zalogowanie
3. Redirect do Dashboard z bannerem weryfikacji
4. Email weryfikacyjny wysłany w tle
5. Użytkownik może przeglądać dashboard (draft custodianships)
6. Kliknięcie linku w emailu → weryfikacja → aktywacja możliwa

**Logowanie:**
1. Wypełnienie formularza Login
2. Opcjonalnie zaznaczenie "Remember me"
3. Walidacja credentials
4. Regeneracja sesji (security)
5. Redirect do Dashboard

**Reset hasła:**
1. Kliknięcie "Forgot password?" na Login
2. Wprowadzenie emaila w ForgotPassword
3. Email z linkiem resetującym (ważność 1h)
4. Kliknięcie linku → formularz ResetPassword
5. Ustawienie nowego hasła
6. Redirect do Login z komunikatem sukcesu

---

## 3. LOGIKA BACKENDOWA

### 3.1 Kontrolery autentykacji

**RegisteredUserController**
- Renderowanie widoku rejestracji
- Walidacja danych wejściowych (nazwa, email, hasło)
- Utworzenie użytkownika w bazie (password hashing: bcrypt)
- Emisja eventu Registered (trigger wysyłki emaila weryfikacyjnego)
- Automatyczne zalogowanie użytkownika
- Redirect do Dashboard

**AuthenticatedSessionController**
- Renderowanie widoku logowania
- Walidacja credentials (email + hasło)
- Rate limiting: 5 prób/minutę per email+IP
- Logowanie użytkownika (remember me support)
- Regeneracja sesji (session fixation protection)
- Redirect do Dashboard lub intended URL
- Wylogowanie: invalidacja sesji, regeneracja CSRF tokena

**PasswordResetLinkController**
- Renderowanie widoku forgot password
- Walidacja emaila
- Utworzenie tokena resetującego (hash w bazie)
- Wysyłka emaila z linkiem
- Komunikat sukcesu (nawet jeśli email nie istnieje - security)

**NewPasswordController**
- Renderowanie widoku reset password z tokenem
- Walidacja tokena (hash, expiration)
- Walidacja nowego hasła (min 8 znaków)
- Resetowanie hasła, regeneracja remember token
- Usunięcie tokena z bazy (one-time use)
- Redirect do Login z komunikatem sukcesu

**EmailVerificationNotificationController**
- Ponowne wysyłanie emaila weryfikacyjnego
- Rate limiting: 6 prób/minutę
- Sprawdzenie czy email już zweryfikowany

**VerifyEmailController**
- Weryfikacja signed URL (signature + expiration)
- Ustawienie email_verified_at timestamp
- Emisja eventu Verified
- Redirect do Dashboard z parametrem verified=1

### 3.2 Model User

**Główne cechy:**
- Implementuje MustVerifyEmail interface (weryfikacja wymagana)
- Password hashing automatyczny (bcrypt)
- Remember token dla persistent login
- Relacja do Custodianships (1:N)
- Kolumny: id, name, email, email_verified_at, password, remember_token, timestamps

**Dodatkowe kolumny audytowe:**
- last_login_at - timestamp ostatniego logowania
- last_activity_at - timestamp ostatniej aktywności
- deleted_at - soft delete dla RODO compliance

### 3.3 Middleware i zabezpieczenia

**auth middleware:**
- Sprawdza czy użytkownik zalogowany
- Redirect do /login jeśli nie
- Guard: web (session-based)

**guest middleware:**
- Sprawdza czy użytkownik NIE jest zalogowany
- Redirect do /custodianships jeśli zalogowany
- Używany na routes autentykacji

**verified middleware:**
- Sprawdza czy email został zweryfikowany
- NIE stosowany na /login i /custodianships (użytkownik może się zalogować przed weryfikacją)
- Stosowany tylko na akcję aktywacji powiernictwa

**Rate limiting:**
- Login: 5 prób/minutę per email+IP (w LoginRequest)
- Register: 5 prób/minutę per IP
- Password reset: 6 prób/godzinę per IP
- Email verification resend: 6 prób/minutę

**CSRF protection:**
- Automatyczny token w każdym formularzu
- Weryfikacja przez middleware VerifyCsrfToken
- Token w meta tag, automatycznie dołączany przez Inertia

### 3.4 Events i Listeners

**Event: Registered**
- Emitowany po utworzeniu nowego użytkownika
- Listener: SendEmailVerificationNotification (wysyła email weryfikacyjny)

**Event: Verified**
- Emitowany po pomyślnej weryfikacji emaila
- Opcjonalny listener dla audytu/analytics

**Event: PasswordReset**
- Emitowany po pomyślnym resecie hasła
- Remember token automatycznie regenerowany (invalidacja sesji)

**Event: Login**
- Emitowany po pomyślnym logowaniu
- Listener: UpdateUserLastLogin (aktualizacja last_login_at)

**Event: Lockout**
- Emitowany po przekroczeniu rate limitu logowania
- Opcjonalny listener dla powiadomienia admina

### 3.5 Email Notifications

**VerifyEmail Notification:**
- Temat: "Verify Your Email Address - Just In Case"
- Zawiera signed URL (id użytkownika + hash emaila)
- Ważność: 24 godziny
- Przycisk akcji: "Verify Email Address"

**ResetPassword Notification:**
- Temat: "Reset Password Notification - Just In Case"
- Zawiera URL z tokenem resetującym
- Ważność: 60 minut
- Przycisk akcji: "Reset Password"

---

## 4. SYSTEM AUTENTYKACJI

### 4.1 Laravel Breeze + Inertia.js

**Architektura:**
- Server-side routing (Laravel routes)
- Client-side rendering (Vue components przez Inertia)
- Brak REST API (Inertia obsługuje komunikację)
- Automatic CSRF protection
- Form helper z obsługą błędów
- Shared data (auth user, flash messages)

**Session-based authentication:**
- Guard: web (session driver)
- Provider: users (Eloquent)
- Cookie-based session storage
- Session lifetime: 120 minut (2 godziny)
- Session driver: database (production: opcjonalnie redis)

**Remember Me functionality:**
- Długoterminowy cookie (2 lata)
- Token w users.remember_token (60 znaków, random)
- Automatycznie zarządzany przez Laravel
- Cookies flags: httpOnly, secure (HTTPS), SameSite=lax

### 4.2 Laravel Sanctum

**Status w MVP:**
- Zainstalowany (wersja 4.2.0)
- NIE używany aktywnie (session auth wystarczający)
- Middleware AuthenticateSession aktywny (dodatkowa warstwa security)

**Middleware AuthenticateSession:**
- Invaliduje sesję jeśli hasło zostało zmienione
- Automatycznie wylogowuje użytkownika z innych urządzeń po resecie hasła

### 4.3 Session Management

**Konfiguracja:**
- Driver: database (tabela sessions)
- Lifetime: 120 minut
- Expire on close: false (sesja przetrwa zamknięcie przeglądarki)
- Cookie name: justincase_session
- Cookie flags: httpOnly=true, secure=true (production), SameSite=lax

**Session Security:**
- Session fixation protection: regeneracja ID po logowaniu
- Session invalidation: wylogowanie usuwa sesję
- CSRF token regeneracja: po wylogowaniu nowy token
- Remember token regeneracja: po resecie hasła invalidacja wszystkich sesji

**Tabela sessions:**
- id (primary key)
- user_id (foreign key, nullable)
- ip_address
- user_agent
- payload (encrypted session data)
- last_activity (timestamp, indexed)

### 4.4 Password Reset Tokens

**Tabela password_reset_tokens:**
- email (primary key)
- token (hashed bcrypt)
- created_at (timestamp)

**Mechanizm:**
- Token plain (60 znaków) wysyłany w emailu
- Token hashed przechowywany w bazie
- Expiration: 60 minut (sprawdzane przez created_at)
- One-time use: token usuwany po użyciu
- Cleanup: automatyczne usuwanie wygasłych tokenów

### 4.5 Email Verification

**Signed URLs:**
- Format: /verify-email/{id}/{hash}?expires={timestamp}&signature={signature}
- Signature zapobiega modyfikacji URL
- Hash: sha1(user email) - weryfikacja że link dla tego użytkownika
- Expiration: 24 godziny (konfiguracja w AppServiceProvider)
- Middleware signed: weryfikacja autentyczności i ważności

**Ponowne wysyłanie:**
- Rate limiting: 6 prób/minutę
- Sprawdzenie czy już zweryfikowany (redirect jeśli tak)
- Nowy link z nowym timestamp expiration

---

## 5. BEZPIECZEŃSTWO

### 5.1 Ochrona przed atakami

**Brute Force Protection:**
- Rate limiting: 5 prób logowania/minutę per email+IP
- Lockout: 1 minuta po przekroczeniu limitu
- Clear counter po sukcesie (nie karze za pojedyncze pomyłki)

**User Enumeration Protection:**
- Password reset: zawsze komunikat sukcesu (nie ujawnia czy email istnieje)
- Login error: generyczny "Invalid credentials" (nie ujawnia czy email istnieje)

**Session Fixation Protection:**
- Regeneracja session ID po logowaniu
- Nowy session ID = stary invalid

**CSRF Protection:**
- Token w każdym formularzu (automatyczny przez Inertia)
- Weryfikacja przez middleware
- Regeneracja po wylogowaniu

**XSS Protection:**
- Vue automatycznie escapuje interpolacje
- httpOnly cookies (nie dostępne przez JavaScript)
- Brak v-html w widokach auth

**SQL Injection Protection:**
- Prepared statements (Eloquent/Query Builder)
- Parameter binding automatyczny
- Brak raw queries

### 5.2 HTTPS i Cookie Security

**HTTPS Enforcement:**
- Middleware force HTTPS w production
- Nginx redirect 80→443
- Security headers: HSTS, X-Frame-Options, X-Content-Type-Options

**Cookie Security:**
- httpOnly: true (XSS protection)
- secure: true w production (HTTPS only)
- SameSite: lax (CSRF protection)

### 5.3 Zgodność z RODO

**Soft Delete użytkowników:**
- Kolumna deleted_at w users
- Usunięcie konta: soft delete (dane zachowane)
- Automatyczne czyszczenie: konta soft-deleted >12 miesięcy permanent delete

**Prawa użytkownika:**
- Prawo do usunięcia: opcja w profilu
- Prawo do eksportu danych: opcjonalnie post-MVP

---

## 6. INTEGRACJA Z APLIKACJĄ

### 6.1 Banner weryfikacji emaila

**Lokalizacja:** AuthenticatedLayout.vue (górna część każdej strony)

**Warunek wyświetlania:**
- Użytkownik zalogowany
- email_verified_at = null

**Funkcjonalność:**
- Komunikat: "Please verify your email to activate custodianships"
- Przycisk: "Resend verification email" (inline, underline)
- POST do /email/verification-notification
- Toast notification po sukcesie

**Styling:**
- Tło: amber-50 (subtle warning color)
- Border: amber-200
- Ikona: AlertCircle (lucide-vue-next)
- Text: amber-900

### 6.2 Shared Data (Inertia)

**Auth user (zawsze dostępny):**
- $page.props.auth.user
- Pola: id, name, email, email_verified_at

**Flash messages:**
- $page.props.flash.message
- $page.props.flash.status

**Wykorzystanie:**
- Warunek wyświetlania banneru
- Personalizacja UI (nazwa użytkownika w navbar)
- Sprawdzenie statusu weryfikacji przed akcjami

### 6.3 Middleware na routes powiernictw

**Auth required:**
- Wszystkie routes /custodianships/* wymagają logowania
- Redirect do /login jeśli guest

**Verified NOT required:**
- Dashboard (/custodianships) dostępny przed weryfikacją
- Widoki Create, Show, Edit dostępne przed weryfikacją
- Powiernictwa tworzone jako draft

**Verified required:**
- Akcja aktywacji powiernictwa (/custodianships/{id}/activate)
- Middleware verified blokuje dostęp
- Redirect do /verify-email jeśli nie zweryfikowany

---

## 7. METRYKI SUKCESU

**Target KPI (METRIC-001):** Time to First Custodianship < 5 minut

**Breakdown autentykacji:**
- Rejestracja: < 1 minuta (prosty formularz, 3 pola)
- Weryfikacja emaila: opcjonalna (można pominąć, aktywacja później)
- Nawigacja do Create: natychmiastowa (Dashboard → przycisk)

**Implementacja wspiera KPI:**
- Minimalistyczne formularze (bez zbędnych pól)
- Automatyczne logowanie po rejestracji (bez manual login)
- Możliwość tworzenia draft bez weryfikacji (brak blokady)
- Clear CTAs (Empty state → "Create Your First Custodianship")

---

**Koniec dokumentu**

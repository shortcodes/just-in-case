# Plan implementacji widoku Forgot Password

## 1. Przegląd

Widok Forgot Password umożliwia użytkownikom inicjację procesu resetowania hasła poprzez podanie adresu email. Jest to pierwszy krok w przepływie odzyskiwania dostępu do konta. System wysyła link resetujący na podany email (jeśli istnieje w bazie), ale zawsze wyświetla komunikat sukcesu aby zapobiec enumeracji użytkowników.

### Kluczowe cele:
- Prosty formularz z jednym polem (email)
- Wysłanie linku resetującego na email
- Zawsze wyświetla komunikat sukcesu (security best practice - zapobiega user enumeration)
- Link ważny przez 1 godzinę
- Możliwość ponownego wysłania linku

## 2. Routing widoku

**Ścieżka:** `/forgot-password`
**Middleware:** `guest` (tylko dla niezalogowanych użytkowników)
**HTTP Method:** GET (wyświetlenie formularza), POST (wysłanie linku)
**Redirect po sukcesie:** Pozostanie na `/forgot-password` z success message
**Alternative:** redirect do `/login` z flash message (do ustalenia)

## 3. Struktura komponentów

```
ForgotPassword.vue (główna strona)
├── GuestLayout
│   ├── Logo/AppName (top)
│   └── Footer (opcjonalnie)
└── Card (centered)
    ├── Header
    │   └── Tytuł "Forgot your password?"
    ├── Description
    │   └── Tekst: "No problem. Just let us know your email address..."
    ├── SuccessMessage (conditional - po wysłaniu)
    │   └── Alert (green/blue)
    └── Form (conditional - przed wysłaniem lub zawsze)
        ├── FormField (Email)
        │   └── TextInput (type="email")
        ├── FormActions
        │   └── Button (Send Reset Link - primary)
        └── FooterLinks
            └── Link ("Back to login")
```

## 4. Szczegóły komponentów

### 4.1 ForgotPassword.vue (główna strona)

**Przeznaczenie:**
Główny kontener formularza forgot password. Zarządza wysyłką linku resetującego i wyświetla komunikat sukcesu.

**Główne elementy:**
- GuestLayout wrapper
- Centered card z formularzem
- Header: "Forgot your password?" lub "Reset your password"
- Description text: "No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one."
- Success message (conditional, po wysłaniu): "We have emailed your password reset link!"
- Formularz z email field
- Submit button "Email Password Reset Link" lub "Send Reset Link"
- Link "Back to login"

**Obsługiwane interakcje:**
- Wypełnienie email field
- Submisja formularza (POST request)
- Wyświetlenie success message
- Nawigacja back do Login

**Obsługiwana walidacja:**
- **Email:** required, valid email format (server-side)
- Security: zawsze wyświetla success message (nawet jeśli email nie istnieje w bazie)

**Propsy (z Inertii):**
- `status?: string` (success message po wysłaniu: "We have emailed your password reset link!")

**Local State:**
- `form` (Inertia useForm) zawierający: email
- `processing: boolean` (automatyczne przez Inertia)
- `submitted: boolean` (czy formularz został wysłany - do pokazania success message)

### 4.2 GuestLayout.vue

**Przeznaczenie:**
Reużywalny layout (już opisany w Register i Login plans).

### 4.3 TextInput.vue

**Przeznaczenie:**
Reużywalny input component (już opisany).

**Props dla Forgot Password:**
- `type: 'email'`
- `autocomplete: 'email'`
- `required: true`

### 4.4 FormField.vue

**Przeznaczenie:**
Wrapper z error handling (już opisany).

### 4.5 Button.vue

**Przeznaczenie:**
Submit button z loading state (już opisany).

**Props dla Forgot Password:**
- `type: 'submit'`
- `variant: 'primary'`
- `processing: boolean`

### 4.6 Alert.vue (nowy component)

**Przeznaczenie:**
Component do wyświetlania success/info/error messages.

**Główne elementy:**
- Container z colored background (green dla success, blue dla info, red dla error)
- Icon (opcjonalnie, based on variant)
- Message text
- Close button (opcjonalnie)

**Propsy:**
- `variant: 'success' | 'info' | 'error' | 'warning'`
- `message: string`
- `dismissible?: boolean`

**Emits:**
- `dismiss` (jeśli dismissible)

## 5. Typy

### ViewModels

Brak ViewModels specyficznych dla Forgot Password.

### Form Data

**ForgotPasswordFormData:**
```typescript
{
  email: string
}
```

### Page Props (Inertia)

**ForgotPasswordPageProps:**
```typescript
{
  status?: string // success message: "We have emailed your password reset link!"
}
```

## 6. Zarządzanie stanem

### Global State (Inertia Props)
- `status` - success message po wysłaniu linku (flash message)

### Local State (ForgotPassword.vue)

**Inertia Form:**
- Form object: `useForm<ForgotPasswordFormData>`
- Inicjalne wartości: email=''
- Automatyczne zarządzanie: processing, errors, wasSuccessful

**Local State:**
- `submitted: boolean` - czy email został wysłany (dla UI feedback)

**Computed Properties:**
- `hasStatus: boolean` - czy jest success message (status prop exists)
- `showForm: boolean` - czy pokazać formularz (zawsze true lub false po submit - do ustalenia UX)

**Methods:**
- `submit()` - handler submisji (form.post('/forgot-password'))
- `navigateToLogin()` - helper do nawigacji back do /login

### Custom Composables

Brak custom composables. Używamy standardowego Inertia `useForm`.

## 7. Dane Testowe (Mock Data)

**Dla widoku Forgot Password NIE MA danych testowych** - jest to prosty formularz z jednym polem.

### Development Helper (opcjonalnie)

Dla testowania można auto-fill z istniejącym emailem z mockUsers.

**Lokalizacja (opcjonalnie):**
`resources/js/data/mockForgotPassword.ts`

**Zawartość:**
```typescript
mockForgotPasswordData = {
  email: mockUser.email // email z mockUsers dla consistency
}
```

## 8. Interakcje użytkownika

### 8.1 Wypełnienie formularza
**Trigger:** User wpisuje email

**Flow:**
1. User wypełnia email field (required, email format)
2. Submit button enabled (zawsze, brak client-side validation oprócz HTML5)

### 8.2 Submisja formularza
**Trigger:** Kliknięcie "Send Reset Link" button lub Enter

**Flow:**
1. User klika submit
2. HTML5 validation sprawdza email format i required
3. Inertia form: set processing=true (button spinner, disabled)
4. POST request do `/forgot-password` z email
5. Backend (Laravel):
   - Validate email (required|email format)
   - Search user by email w bazie
   - Jeśli user exists:
     - Delete old password reset tokens dla tego email
     - Generate nowy token (random 64-char string)
     - Save token w `password_reset_tokens` table (hashed)
     - Send email z reset link containing token
   - Jeśli user NIE exists:
     - Nie wysyła email
     - Ale NIE ujawnia tego (security)
   - ZAWSZE return success message (security best practice)
6. Response:
   - Success: status='We have emailed your password reset link!'
   - Redirect back do `/forgot-password` z flash message
   - LUB pozostanie na stronie z success message (do ustalenia UX)
7. Set processing=false

**Success scenario (email exists):**
- Success message wyświetlony: "We have emailed your password reset link!"
- Email wysłany z linkiem resetującym
- Link format: `/reset-password/{token}?email={email}`
- Link ważny przez 1 godzinę (config Laravel)
- User klika link w email → navigate do Reset Password page

**Success scenario (email NIE exists):**
- Identyczny success message: "We have emailed your password reset link!"
- Email NIE wysłany
- User myśli że email został wysłany (security - zapobiega user enumeration)
- User czeka na email który nigdy nie przyjdzie
- To jest intended behavior dla security

**Error scenario (validation):**
- Email format invalid: error pod polem
- Email empty: HTML5 validation blocks submit

### 8.3 Nawigacja back do Login
**Trigger:** Kliknięcie "Back to login" link

**Flow:**
1. User klika link
2. Inertia navigation: `router.visit('/login')`
3. Płynne przejście do Login page

### 8.4 Ponowne wysłanie (user klika submit znowu)
**Trigger:** User klika submit button ponownie z tym samym lub innym emailem

**Flow:**
1. Identyczny flow jak w 8.2
2. Backend:
   - Delete old token (jeśli exists)
   - Create new token
   - Send new email
3. Success message wyświetlony znowu
4. Poprzedni link w email przestaje działać (token replaced)

## 9. Warunki i walidacja

### 9.1 Email field
**Warunek:** required, valid email format
**Komponenty:** ForgotPassword.vue (FormField + TextInput)
**Efekt:**
- Frontend: HTML5 email validation, required attribute
- Backend: validation rule 'required|email'
- Error messages:
  - "The email field is required."
  - "The email must be a valid email address."
- Visual: red border + error text pod polem

### 9.2 Email existence (security consideration)
**Warunek:** email may or may not exist w bazie
**Komponenty:** Backend logic
**Efekt:**
- Backend NIE ujawnia czy email exists
- ZAWSZE return success message
- Jeśli exists: send email
- Jeśli NIE exists: NIE send email ale return success anyway
- Security: zapobiega user enumeration attack
- UX trade-off: user który pomyli email nie dostanie feedback

### 9.3 Token validity
**Warunek:** token valid przez 1 godzinę od wygenerowania
**Komponenty:** Backend (password_reset_tokens table)
**Efekt:**
- Token ma created_at timestamp
- Przy użyciu linku: backend sprawdza czy created_at > now - 1h
- Jeśli expired: error message "This password reset link has expired."
- User musi zrequest new link (back do Forgot Password)

### 9.4 Rate limiting (security)
**Warunek:** max 5 requests per minute per IP (podobnie jak login)
**Komponenty:** Backend throttle middleware
**Efekt:**
- Po 5 requests: temporary lock (1 minute)
- Error message: "Too many requests. Please try again in 60 seconds."
- Zapobiega abuse (spam wysyłka emaili)

### 9.5 Old token deletion
**Warunek:** tylko 1 active token per email w danym czasie
**Komponenty:** Backend logic
**Efekt:**
- Przed utworzeniem new token: delete old tokens dla tego email
- Poprzednie linki przestają działać
- User może request new link wielokrotnie ale tylko ostatni działa

## 10. Obsługa błędów

### 10.1 Invalid email format
**Scenariusz:** User wpisuje niepoprawny format email

**Obsługa:**
- HTML5 validation blocks submit
- Browser native error: "Please enter a valid email address."
- Visual: invalid state (red border)
- Fallback: server-side validation catches jeśli bypassed

### 10.2 Empty email field
**Scenariusz:** User próbuje submit bez wpisania email

**Obsługa:**
- HTML5 validation blocks submit
- Browser native error: "Please fill out this field."
- Visual: invalid state

### 10.3 Rate limiting exceeded
**Scenariusz:** User klika submit >5 razy w ciągu minuty

**Obsługa:**
- Backend throttle middleware blocks request
- HTTP 429 returned
- Error message: "Too many password reset requests. Please try again in 60 seconds."
- Error wyświetlany jako alert/banner
- Form disabled podczas throttle period (opcjonalnie)
- Auto-unlock po 1 minucie

### 10.4 Email service failure
**Scenariusz:** Email nie może być wysłany (np. Mailgun down, network error)

**Obsługa:**
- Backend email send fails
- Exception caught
- Error logged do monitoring
- User widzi success message (nie wie o failure)
- Powód: nie chcemy ujawniać internal errors
- Alternative approach: retry logic w background (queue job)

### 10.5 Network error / timeout
**Scenariusz:** POST request fails z powodu network issue

**Obsługa:**
- Inertia timeout (30s)
- Toast notification: "Request timed out. Please check your connection and try again."
- Processing state wraca do false
- Form data preserved
- User może retry

### 10.6 Server error (500)
**Scenariusz:** Unexpected server error

**Obsługa:**
- HTTP 500 returned
- Toast notification: "Something went wrong. Please try again later."
- Processing state wraca do false
- Error logged do monitoring

## 11. Kroki implementacji

### Krok 1: Przygotowanie typów (10 min)
- Dodaj do `types/auth.ts`: ForgotPasswordFormData interface
- Dodaj ForgotPasswordPageProps interface
- Dokumentuj z JSDoc

### Krok 2: Komponent Alert (30 min)
- Utwórz `Components/Alert.vue`
- Props: variant ('success', 'info', 'error', 'warning'), message, dismissible
- Emits: dismiss (jeśli dismissible)
- Variants styling:
  - Success: green background (bg-green-50), green border, green text
  - Info: blue background, blue border, blue text
  - Error: red background, red border, red text
  - Warning: yellow/amber background, amber border, amber text
- Icon dla każdego variant (Heroicons)
- Close button (X icon) jeśli dismissible
- Accessibility: ARIA role="alert" dla screen readers

### Krok 3: Główna strona ForgotPassword.vue (1.5h)
- Utwórz `Pages/Auth/ForgotPassword.vue`
- Użyj GuestLayout wrapper
- Setup Inertia useForm z ForgotPasswordFormData
- Header + description text
- Success message (Alert component) conditional na status prop
- FormField z email (TextInput type="email" autocomplete="email")
- Submit button (Button)
- Link "Back to login"
- Submit handler: form.post('/forgot-password')
- Success handling: show Alert z status message

### Krok 4: Backend - PasswordResetLinkController (1h)
- Sprawdź czy Laravel Breeze dostarcza controller
- Jeśli nie: utwórz `Http/Controllers/Auth/PasswordResetLinkController.php`
- Method `store()`:
  - Validate: email (required|email)
  - Rate limiting: throttle 5 per minute
  - Check user exists: User::where('email', $email)->first()
  - Jeśli exists:
    - Delete old tokens: DB::table('password_reset_tokens')->where('email', $email)->delete()
    - Generate token: Str::random(64) lub use Laravel Password::sendResetLink()
    - Store token (hashed): DB::table('password_reset_tokens')->insert([...])
    - Send email: $user->sendPasswordResetNotification($token)
  - ZAWSZE return success (nie ujawniaj czy email exists)
  - Redirect back z status flash message

### Krok 5: Password reset token table migration (15 min)
- Sprawdź czy migracja `password_reset_tokens` istnieje (Laravel default)
- Schema:
  - email (varchar 255) - PK
  - token (varchar 255) - hashed
  - created_at (timestamp)
- Jeśli nie istnieje: create migration

### Krok 6: Password reset email notification (45 min)
- Sprawdź Laravel default ResetPassword notification
- Customize mailable view (resources/views/emails/reset-password.blade.php):
  - Subject: "Reset Your Password - Just In Case"
  - Body: clear message z reset button/link
  - Link format: `{{ url('/reset-password/'.$token.'?email='.$email) }}`
  - Link expires in 1 hour (mention w email)
  - Style email: inline CSS dla compatibility
- Config expiration: `config/auth.php` → passwords.users.expire = 60 (minutes)

### Krok 7: Routing (10 min)
- Sprawdź routes w `routes/auth.php`
- GET `/forgot-password` - wyświetla formularz (method `create()`)
- POST `/forgot-password` - wysyła link (method `store()`)
- Middleware: guest

### Krok 8: Rate limiting (10 min)
- Apply throttle middleware do POST route
- Throttle: 5 attempts per minute per IP
- Custom error message w exception handler

### Krok 9: Security testing (30 min)
- Test user enumeration prevention:
  - Submit z existing email → success message
  - Submit z non-existing email → identyczny success message
  - Sprawdź że response time podobny (timing attack prevention)
- Test rate limiting:
  - 5 requests → success
  - 6th request → 429 error
  - Wait 1 minute → unlocked
- Test token security:
  - Token hashed w bazie (nie plain text)
  - Token random (nie predictable)

### Krok 10: Testy manualne (1h)
- Test wypełnienia formularza (email)
- Test submisji success:
  - Valid existing email
  - Success message displayed
  - Email received z reset link
  - Link format correct
- Test submisji z non-existing email:
  - Success message displayed (identyczny)
  - Email NIE received
- Test validation:
  - Empty email (HTML5 blocks)
  - Invalid format (HTML5 blocks)
- Test rate limiting (5+ requests)
- Test link "Back to login" (navigate działa)
- Test responsywności
- Test accessibility

### Krok 11: Integration testy (30 min)
- Test complete flow:
  - Forgot Password → Email received → Click link → Reset Password page
- Test token expiration:
  - Request reset link
  - Wait >1h (opcjonalnie mock time)
  - Try use expired link → error message
  - Request new link → works
- Test multiple requests (old token invalidated)

### Krok 12: Dokumentacja i cleanup (15 min)
- Remove console.logs
- Format code
- Add comments do security logic (user enumeration prevention)
- Update README
- Git commit

**Całkowity szacowany czas:** 6-7 godzin

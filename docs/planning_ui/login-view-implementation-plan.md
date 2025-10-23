# Plan implementacji widoku Login

## 1. Przegląd

Widok Login umożliwia autentykację istniejących użytkowników w aplikacji Just In Case. Jest to główna brama wejściowa do aplikacji dla zarejestrowanych użytkowników. Widok obsługuje zarówno użytkowników z zweryfikowanym emailem, jak i tych którzy jeszcze nie zweryfikowali swojego konta (z ograniczeniami funkcjonalności).

### Kluczowe cele:
- Szybka i prosta autentykacja (email + hasło)
- Opcja "Remember me" dla wygody użytkownika
- Łatwy dostęp do resetowania hasła
- Generyczny komunikat błędu dla bezpieczeństwa (zapobieganie enumeracji użytkowników)
- Możliwość logowania przed weryfikacją email (z ograniczeniami)

## 2. Routing widoku

**Ścieżka:** `/login`
**Middleware:** `guest` (tylko dla niezalogowanych użytkowników)
**HTTP Method:** GET (wyświetlenie formularza), POST (autentykacja)
**Redirect po sukcesie:** `/custodianships` (Dashboard)
**Redirect jeśli już zalogowany:** `/custodianships` (Dashboard)

## 3. Struktura komponentów

```
Login.vue (główna strona)
├── GuestLayout
│   ├── Logo/AppName (top)
│   └── Footer (opcjonalnie)
└── Card (centered)
    ├── Header
    │   └── Tytuł "Welcome back"
    └── Form
        ├── FormField (Email)
        │   └── TextInput (type="email")
        ├── FormField (Password)
        │   └── TextInput (type="password")
        ├── FormOptions (flex row)
        │   ├── Checkbox ("Remember me")
        │   └── Link ("Forgot password?")
        ├── FormActions
        │   └── Button (Log in - primary)
        └── FooterLinks
            └── Link ("Don't have an account? Register")
```

## 4. Szczegóły komponentów

### 4.1 Login.vue (główna strona)

**Przeznaczenie:**
Główny kontener formularza logowania. Zarządza procesem autentykacji, obsługuje "remember me" funkcjonalność i przekierowuje użytkownika po pomyślnym logowaniu.

**Główne elementy:**
- GuestLayout wrapper
- Centered card z formularzem
- Header: "Welcome back" lub "Log in to your account"
- Formularz z polami: email, password
- Checkbox "Remember me"
- Link "Forgot password?" (prowadzi do /forgot-password)
- Submit button "Log in"
- Link do Register dla nowych użytkowników

**Obsługiwane interakcje:**
- Wypełnienie email i password
- Toggle "Remember me" checkbox
- Submisja formularza (POST request)
- Nawigacja do Forgot Password
- Nawigacja do Register

**Obsługiwana walidacja:**
- **Email:** required, valid email format (server-side)
- **Password:** required (server-side)
- Credentials validation: email + password match w bazie (server-side)
- Security: generyczny error message "Invalid email or password" (zapobiega enumeracji użytkowników)

**Propsy (z Inertii):**
- `status?: string` (flash message, np. po resecie hasła: "Password reset successfully")
- `canResetPassword: boolean` (czy funkcja reset password jest dostępna)

**Local State:**
- `form` (Inertia useForm) zawierający: email, password, remember
- `processing: boolean` (automatyczne przez Inertia)

### 4.2 GuestLayout.vue

**Przeznaczenie:**
Reużywalny layout dla stron niezalogowanych (już opisany w Register plan).

**Główne elementy:**
- Centered container
- Logo/App name na górze
- Main content slot
- Footer (opcjonalnie)

### 4.3 TextInput.vue

**Przeznaczenie:**
Reużywalny input component (już opisany w Register plan).

**Props kluczowe dla Login:**
- `type: 'email' | 'password'`
- `autocomplete: 'email' | 'current-password'`
- `required: true`

### 4.4 FormField.vue

**Przeznaczenie:**
Wrapper z error handling (już opisany w Register plan).

### 4.5 Checkbox.vue

**Przeznaczenie:**
Checkbox component dla "Remember me" (już opisany w Register plan).

**Props dla Login:**
- `modelValue: boolean` (remember)
- Label: "Remember me"

### 4.6 Button.vue

**Przeznaczenie:**
Submit button z loading state (już opisany w Register plan).

**Props dla Login:**
- `type: 'submit'`
- `variant: 'primary'`
- `processing: boolean` (pokazuje spinner podczas logowania)

## 5. Typy

### ViewModels

Brak ViewModels specyficznych dla Login.

### Form Data

**LoginFormData:**
```typescript
{
  email: string
  password: string
  remember: boolean
}
```

### Page Props (Inertia)

**LoginPageProps:**
```typescript
{
  status?: string // flash message (np. po reset password: "Password reset successfully")
  canResetPassword: boolean // czy funkcja reset password dostępna
}
```

## 6. Zarządzanie stanem

### Global State (Inertia Props)
- `status` - flash message z poprzedniej akcji (np. po reset password)
- `canResetPassword` - czy link "Forgot password?" powinien być aktywny

### Local State (Login.vue)

**Inertia Form:**
- Form object utworzony przez `useForm<LoginFormData>`
- Inicjalne wartości: email='', password='', remember=false
- Automatyczne zarządzanie: processing, errors, wasSuccessful

**Computed Properties:**
- `hasStatus: boolean` - czy jest flash message do wyświetlenia
- `showForgotPasswordLink: boolean` - bazuje na canResetPassword prop

**Methods:**
- `submit()` - handler submisji (form.post('/login'))
- `navigateToRegister()` - helper do nawigacji na /register
- `navigateToForgotPassword()` - helper do nawigacji na /forgot-password

### Custom Composables

Brak custom composables specyficznych dla Login. Używamy standardowego Inertia `useForm`.

## 7. Dane Testowe (Mock Data)

**Dla widoku Login NIE MA danych testowych** - jest to pusty formularz do wprowadzenia credentials.

### Development Helper (opcjonalnie)

Dla wygody podczas development można dodać helper do auto-fill.

**Lokalizacja (opcjonalnie):**
`resources/js/data/mockLogin.ts`

**Zawartość (przykład dla dev convenience):**
```typescript
mockLoginData = {
  email: 'test@example.com'
  password: 'password123'
  remember: false
}
```

**UWAGA:** Tylko dla development testing. Można dodać button "Fill with test data" tylko w dev mode.

## 8. Interakcje użytkownika

### 8.1 Wypełnienie formularza
**Trigger:** User wpisuje credentials

**Flow:**
1. User wypełnia email field (required, email format)
2. User wypełnia password field (required)
3. Opcjonalnie: user zaznacza "Remember me" checkbox
4. Submit button enabled (zawsze, nie ma client-side validation oprócz HTML5)

**Walidacja podczas wypełniania:**
- HTML5 validation: email format, required fields
- Brak real-time walidacji credentials (tylko server-side)

### 8.2 Submisja formularza
**Trigger:** Kliknięcie "Log in" button lub Enter w input field

**Flow:**
1. User klika submit lub naciska Enter
2. Browser HTML5 validation sprawdza email format i required fields
3. Inertia form: set processing=true (button spinner, disabled)
4. POST request do `/login` z credentials
5. Backend (Laravel):
   - Attempt authentication: Auth::attempt(['email' => $email, 'password' => $password], $remember)
   - Jeśli success:
     - Login user (session created)
     - Regenerate session ID (security - prevents session fixation)
     - Redirect do intended location (default: /custodianships)
   - Jeśli failure:
     - Return validation error (generic message)
6. Response handling:
   - **Success:** Redirect do Dashboard
   - **Error:** Pozostanie na /login, form.errors.email z generic message
7. Set processing=false

**Success scenario:**
- User zalogowany
- Session created (z remember token jeśli checkbox zaznaczony)
- Redirect do `/custodianships` (Dashboard)
- Jeśli email niezweryfikowany: EmailVerificationBanner pokazany na Dashboard
- User może zacząć korzystać z aplikacji

**Error scenario (invalid credentials):**
- User pozostaje na /login
- Generic error message: "These credentials do not match our records." lub "Invalid email or password."
- Error wyświetlany pod email field (nie pod password dla consistency)
- Password field zachowany (nie cleared) - user decision
- User może spróbować ponownie

**Remember me:**
- Jeśli checked: Laravel tworzy długoterminowy cookie (default: 5 lat)
- User pozostaje zalogowany przez długi czas (nawet po zamknięciu browser)
- Jeśli not checked: sesja wygasa po zamknięciu browser

### 8.3 Nawigacja do Forgot Password
**Trigger:** Kliknięcie "Forgot password?" link

**Flow:**
1. User klika link
2. Sprawdzenie canResetPassword prop
   - Jeśli true: Inertia navigation do `/forgot-password`
   - Jeśli false: link disabled lub hidden
3. Płynne przejście bez page reload

### 8.4 Nawigacja do Register
**Trigger:** Kliknięcie "Don't have an account? Register" link

**Flow:**
1. User klika link
2. Inertia navigation: `router.visit('/register')`
3. Płynne przejście do Register page

### 8.5 Wyświetlenie status message
**Trigger:** User trafia na /login z flash message (np. po reset password)

**Flow:**
1. Backend ustawia flash message w session
2. Inertia przekazuje status w props
3. Login.vue wyświetla status message (jeśli exists)
4. Message styling: info banner (blue/green) na górze formularza
5. Przykłady messages:
   - "Your password has been reset successfully."
   - "Your email has been verified."
   - "Please log in to continue."

## 9. Warunki i walidacja

### 9.1 Email field
**Warunek:** required, valid email format
**Komponenty:** Login.vue (FormField + TextInput type="email")
**Efekt:**
- Frontend: HTML5 email validation, required attribute
- Backend: validation rule 'required|email' (format check only, nie sprawdza czy exists)
- Error messages:
  - "The email field is required."
  - "The email must be a valid email address."
- Visual: red border + error text pod polem

### 9.2 Password field
**Warunek:** required
**Komponenty:** Login.vue (FormField + TextInput type="password")
**Efekt:**
- Frontend: HTML5 required attribute
- Backend: validation rule 'required|string'
- Error message: "The password field is required."
- Visual: red border + error text pod polem
- Security: type="password" maskuje znaki

### 9.3 Credentials validation
**Warunek:** email + password must match existing user w bazie
**Komponenty:** Login.vue (backend logic)
**Efekt:**
- Backend: Auth::attempt() sprawdza credentials
- Jeśli invalid:
  - Generic error: "These credentials do not match our records."
  - Error wyświetlany pod email field (przez FormField)
  - NIE ujawnia czy email exists (security - zapobiega user enumeration)
- Jeśli valid:
  - User zalogowany
  - Redirect do Dashboard

### 9.4 Remember me checkbox
**Warunek:** opcjonalny, domyślnie unchecked
**Komponenty:** Login.vue (Checkbox)
**Efekt:**
- Jeśli checked: Laravel tworzy długoterminowy remember token
- Jeśli unchecked: sesja tylko na czas browser session
- Brak walidacji (opcjonalne pole)

### 9.5 Rate limiting (security)
**Warunek:** max 5 failed login attempts per email per minute (Laravel default)
**Komponenty:** Backend middleware (Laravel Throttle)
**Efekt:**
- Po 5 failed attempts: user temporarily locked (1 minute)
- Error message: "Too many login attempts. Please try again in X seconds."
- Frontend: wyświetla error message z countdown (opcjonalnie)
- Unlocked automatycznie po 1 minucie

### 9.6 Email verification status
**Warunek:** user.email_verified_at === null (email niezweryfikowany)
**Komponenty:** Login nie sprawdza - user może się zalogować
**Efekt:**
- User może się zalogować przed weryfikacją email (REQ-003)
- Po zalogowaniu: redirect do Dashboard
- Dashboard pokazuje EmailVerificationBanner
- User ma ograniczone funkcjonalności:
  - Może tworzyć custodianships tylko jako draft
  - Nie może aktywować timerów
  - Banner przypomina o weryfikacji

## 10. Obsługa błędów

### 10.1 Invalid credentials
**Scenariusz:** User wprowadza błędny email lub hasło

**Obsługa:**
- Backend: Auth::attempt() returns false
- Error returned: form.errors.email = "These credentials do not match our records."
- Generic message (nie ujawnia czy email exists - security best practice)
- Error wyświetlany pod email field przez FormField
- Password field nie cleared (user decision)
- User może poprawić credentials i retry

### 10.2 Empty fields (HTML5 validation)
**Scenariusz:** User próbuje submit z pustymi polami

**Obsługa:**
- HTML5 validation blocks submit
- Browser native error messages:
  - Email: "Please fill out this field."
  - Password: "Please fill out this field."
- Visual: invalid pseudo-class styling (red border)
- Submit nie wysłany do backend

### 10.3 Invalid email format
**Scenariusz:** User wpisuje niepoprawny format email (np. "test@")

**Obsługa:**
- HTML5 validation (type="email") sprawdza format
- Browser native error: "Please enter a valid email address."
- Visual: invalid state styling
- Submit blocked
- Fallback: server-side validation catches jeśli client-side bypassed

### 10.4 Rate limiting (too many attempts)
**Scenariusz:** User próbuje się zalogować >5 razy w ciągu minuty z tym samym emailem

**Obsługa:**
- Backend: Laravel Throttle middleware blocks request
- HTTP 429 Too Many Requests returned
- Error message: "Too many login attempts. Please try again in 60 seconds."
- Error wyświetlany jako alert/banner na górze formularza
- Countdown timer (opcjonalnie): pokazuje remaining seconds
- Form inputs disabled podczas throttle period (opcjonalnie)
- Po 1 minucie: automatically unlocked, user może retry

### 10.5 Network error / timeout
**Scenariusz:** POST request fails z powodu network issue

**Obsługa:**
- Inertia timeout (default 30s)
- Toast notification: "Request timed out. Please check your connection and try again."
- Processing state wraca do false
- Form data preserved
- User może retry submit

### 10.6 Server error (500)
**Scenariusz:** Unexpected server error podczas authentication

**Obsługa:**
- Backend throws exception
- HTTP 500 returned
- Toast notification: "Something went wrong. Please try again later."
- Processing state wraca do false
- Form data preserved
- Error logged do monitoring (Nightwatch/Sentry)

### 10.7 Session expired (jeśli user już był zalogowany)
**Scenariusz:** User trafia na /login mimo że ma active session

**Obsługa:**
- Middleware `guest` sprawdza czy user authenticated
- Jeśli tak: automatic redirect do `/custodianships` (Dashboard)
- User nie widzi login form
- Smooth redirect bez error message

## 11. Kroki implementacji

### Krok 1: Przygotowanie typów (10 min)
- Dodaj do `types/auth.ts`: LoginFormData interface
- Dodaj LoginPageProps interface
- Dokumentuj z JSDoc comments

### Krok 2: Wykorzystanie istniejących komponentów (0 min - już istnieją)
- GuestLayout (z Register)
- TextInput (z Register)
- FormField (z Register)
- Checkbox (z Register)
- Button (z Register)

### Krok 3: Główna strona Login.vue (1.5h)
- Utwórz `Pages/Auth/Login.vue`
- Użyj GuestLayout jako wrapper
- Setup Inertia useForm z LoginFormData
- FormFields:
  - Email (TextInput type="email" autocomplete="email")
  - Password (TextInput type="password" autocomplete="current-password")
- Options row:
  - Checkbox "Remember me"
  - Link "Forgot password?" (conditional na canResetPassword)
- Submit button (Button type="submit" variant="primary")
- Link do Register
- Status message display (jeśli status prop exists)
- Submit handler: form.post('/login')

### Krok 4: Backend - LoginController (45 min)
- Sprawdź czy Laravel Breeze już dostarcza controller
- Jeśli nie: utwórz `Http/Controllers/Auth/AuthenticatedSessionController.php`
- Method `store()`:
  - Validate: email (required|email), password (required|string)
  - Rate limiting: throttle 5 attempts per minute per email
  - Authentication attempt: Auth::attempt(['email' => $email, 'password' => $password], $remember)
  - Jeśli success:
    - Regenerate session: $request->session()->regenerate()
    - Redirect: Inertia::location(route('dashboard')) (intended URL)
  - Jeśli failure:
    - Return validation error: 'email' => 'These credentials do not match our records.'
    - Increment failed attempts counter (rate limiting)

### Krok 5: Routing (10 min)
- Sprawdź routes w `routes/auth.php` (Laravel Breeze powinien dostarczyć)
- GET `/login` - wyświetla formularz (method `create()`)
- POST `/login` - autentykuje (method `store()`)
- Middleware: guest na obu routes

### Krok 6: Rate limiting configuration (15 min)
- Sprawdź `app/Http/Kernel.php` lub `bootstrap/app.php` (Laravel 11+)
- Upewnij się że throttle middleware skonfigurowany
- Default: 'throttle:5,1' (5 attempts per 1 minute)
- Custom error message w validation exception

### Krok 7: Flash messages handling (20 min)
- Backend: ustawienie flash message w session po akcjach:
  - Po reset password: session()->flash('status', 'Your password has been reset.')
  - Po email verification: session()->flash('status', 'Your email has been verified.')
- Frontend: wyświetlenie status w Login.vue:
  - Alert/banner component (blue/green)
  - Conditional rendering jeśli status prop exists
  - Auto-dismiss po 5 sekundach (opcjonalnie)

### Krok 8: Remember me functionality (15 min)
- Backend: Auth::attempt() drugi parametr = $remember (boolean)
- Laravel automatycznie tworzy remember token w cookies
- Token expiration: config w `config/auth.php` (default: 2628000 minutes = 5 lat)
- Security: token stored hashed w bazie

### Krok 9: Testy manualne (1h)
- Test wypełnienia formularza (email + password)
- Test submisji success:
  - Valid credentials
  - User zalogowany
  - Session created
  - Redirect do Dashboard
  - Remember me works (logout, close browser, reopen - still logged in)
- Test submisji error:
  - Invalid email
  - Invalid password
  - Wrong credentials (generic error message)
  - Empty fields (HTML5 validation)
- Test rate limiting:
  - 5 failed attempts
  - Locked for 1 minute
  - Error message displayed
  - Unlocked po 1 minucie
- Test linków:
  - Forgot password (navigate do /forgot-password)
  - Register (navigate do /register)
- Test status messages:
  - Po reset password (flash message shown)
  - Auto-dismiss (opcjonalnie)
- Test responsywności (mobile, tablet, desktop)
- Test accessibility (keyboard navigation, screen reader)
- Test security:
  - Generic error message (nie ujawnia czy email exists)
  - CSRF protection
  - Password field masked

### Krok 10: Integration testy (30 min)
- Test flow: Register → Verify Email → Login → Dashboard
- Test flow: Login before verification → Dashboard z banner
- Test flow: Forgot Password → Reset → Login
- Test remember me across sessions
- Test throttle recovery

### Krok 11: Dokumentacja i cleanup (15 min)
- Remove console.logs
- Format code (Pint dla PHP, Prettier dla Vue)
- Add comments do rate limiting logic
- Update README jeśli dodane custom logic
- Git commit

**Całkowity szacowany czas:** 5-6 godzin

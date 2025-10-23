# Plan implementacji widoku Register

## 1. Przegląd

Widok Register umożliwia rejestrację nowego użytkownika w aplikacji Just In Case. Jest to pierwszy krok w onboardingu, kluczowy dla KPI "Time to First Custodianship" (<5 minut). Po rejestracji użytkownik otrzymuje email weryfikacyjny i zostaje przekierowany do Dashboard z bannerem przypominającym o weryfikacji.

### Kluczowe cele:
- Prosta i szybka rejestracja (minimalna liczba pól)
- Walidacja email (format, unikalność) i hasła (minimum 8 znaków)
- Automatyczne wysłanie linku aktywacyjnego
- Płynne przekierowanie do Dashboard
- Zgodność z Laravel Breeze + dostosowanie do stylistyki projektu (Tailwind CSS 4 + Shadcn-vue)

## 2. Routing widoku

**Ścieżka:** `/register`
**Middleware:** `guest` (tylko dla niezalogowanych użytkowników)
**HTTP Method:** GET (wyświetlenie formularza), POST (rejestracja)
**Redirect po sukcesie:** `/custodianships` (Dashboard) z email verification banner

## 3. Struktura komponentów

```
Register.vue (główna strona)
├── GuestLayout
│   ├── Logo/AppName (top)
│   └── Footer (opcjonalnie)
└── Card (centered)
    ├── Header
    │   └── Tytuł "Create your account"
    └── Form
        ├── FormField (Name)
        │   └── TextInput
        ├── FormField (Email)
        │   └── TextInput (type="email")
        ├── FormField (Password)
        │   └── TextInput (type="password")
        ├── FormField (Password Confirmation)
        │   └── TextInput (type="password")
        ├── FormField (Terms of Service - opcjonalnie MVP)
        │   ├── Checkbox
        │   └── Label z linkiem do ToS
        ├── FormActions
        │   └── Button (Register - primary)
        └── FooterLinks
            └── Link ("Already have an account? Log in")
```

## 4. Szczegóły komponentów

### 4.1 Register.vue (główna strona)

**Przeznaczenie:**
Główny kontener formularza rejestracji. Zarządza stanem formularza, walidacją server-side i procesem rejestracji.

**Główne elementy:**
- GuestLayout (layout bez navbar, z logo na górze)
- Centered card z formularzem
- Header: "Create your account" (h1 lub h2)
- Formularz z polami: name, email, password, password_confirmation
- Optional: Terms of Service checkbox
- Submit button "Create Account" / "Register"
- Link do Login dla istniejących użytkowników

**Obsługiwane interakcje:**
- Wypełnienie wszystkich pól formularza
- Akceptacja Terms of Service (jeśli włączone)
- Submisja formularza (POST request)
- Nawigacja do Login page

**Obsługiwana walidacja:**
- **Name:** required, max 255 znaków (server-side)
- **Email:** required, valid email format, unique w bazie (server-side)
- **Password:** required, minimum 8 znaków (server-side)
- **Password Confirmation:** required, must match password (server-side)
- **Terms:** required checkbox jeśli pole włączone (server-side)
- Frontend: HTML5 basic validation (email format, required attributes, minlength)

**Propsy (z Inertii):**
Brak props - nowy użytkownik, pusty formularz

**Local State:**
- `form` (Inertia useForm) zawierający: name, email, password, password_confirmation, terms (opcjonalnie)
- `processing: boolean` - czy request w toku (używane przez Inertia automatycznie)

### 4.2 GuestLayout.vue

**Przeznaczenie:**
Layout wrapper dla stron niezalogowanych (Register, Login, Forgot Password, Reset Password).

**Główne elementy:**
- Container z centered content
- Logo/App name na górze (clickable, link do landing page)
- Main content area (slot)
- Footer z linkami (opcjonalnie: Privacy Policy, Terms of Service)

**Obsługiwane interakcje:**
- Kliknięcie logo → nawigacja do home/landing page

**Propsy:**
Brak

**Slots:**
- `default` - główna treść (formularz)

### 4.3 TextInput.vue

**Przeznaczenie:**
Reużywalny komponent input text/email/password z consistent styling i error states.

**Główne elementy:**
- Input field z odpowiednim type
- Tailwind styling dla różnych stanów (normal, focus, error, disabled)
- Error state: red border

**Obsługiwane interakcje:**
- Wpisanie tekstu (v-model)
- Focus/blur events
- Walidacja HTML5 (email format, required, minlength)

**Propsy:**
- `modelValue: string` (v-model binding)
- `type: string` (default: 'text', options: 'text', 'email', 'password')
- `placeholder?: string`
- `required?: boolean`
- `autocomplete?: string` (np. 'name', 'email', 'new-password')
- `disabled?: boolean`
- `minlength?: number`

**Emits:**
- `update:modelValue(value: string)`

### 4.4 FormField.vue

**Przeznaczenie:**
Wrapper component dla pól formularza z automatycznym wyświetlaniem błędów z Inertia form.

**Główne elementy:**
- Label element (z required indicator * jeśli required)
- Slot dla input component
- Error message area (wyświetlany gdy form.errors[name] exists)
- Helper text area (opcjonalnie, dla dodatkowych informacji)

**Obsługiwane interakcje:**
Brak - pure display wrapper

**Propsy:**
- `label: string`
- `name: string` (używane do mapowania błędów z form.errors)
- `error?: string` (z form.errors[name])
- `required?: boolean`
- `helperText?: string` (opcjonalny tekst pomocy)

**Slots:**
- `default` - input component (TextInput, Select, Checkbox, etc.)

### 4.5 Checkbox.vue

**Przeznaczenie:**
Komponent checkbox dla Terms of Service acceptance (opcjonalnie w MVP).

**Główne elementy:**
- Checkbox input (stylowany przez Tailwind lub Shadcn)
- Label slot (umożliwia wstawienie tekstu z linkiem)

**Obsługiwane interakcje:**
- Toggle checkbox (v-model)
- Kliknięcie label

**Propsy:**
- `modelValue: boolean` (v-model binding)
- `disabled?: boolean`
- `required?: boolean`

**Emits:**
- `update:modelValue(value: boolean)`

### 4.6 Button.vue

**Przeznaczenie:**
Reużywalny button component z różnymi wariantami i loading state.

**Główne elementy:**
- Button element
- Loading spinner (pokazywany gdy processing=true)
- Slot dla tekstu przycisku
- Disabled styling

**Obsługiwane interakcje:**
- Click event (jeśli nie disabled/processing)

**Propsy:**
- `type?: 'button' | 'submit' | 'reset'` (default: 'button')
- `variant?: 'primary' | 'secondary' | 'danger' | 'ghost'` (default: 'primary')
- `disabled?: boolean`
- `processing?: boolean` (pokazuje spinner, disables button)
- `fullWidth?: boolean` (opcjonalnie, dla mobile)

**Slots:**
- `default` - tekst przycisku

## 5. Typy

### ViewModels

Brak ViewModels specyficznych dla Register (pusty formularz dla nowego użytkownika).

### Form Data

**RegisterFormData:**
```typescript
{
  name: string
  email: string
  password: string
  password_confirmation: string
  terms?: boolean // opcjonalnie jeśli ToS checkbox włączony
}
```

### Component Props Interfaces

**TextInputProps:**
```typescript
{
  modelValue: string
  type?: 'text' | 'email' | 'password'
  placeholder?: string
  required?: boolean
  autocomplete?: string
  disabled?: boolean
  minlength?: number
}
```

**FormFieldProps:**
```typescript
{
  label: string
  name: string
  error?: string
  required?: boolean
  helperText?: string
}
```

**CheckboxProps:**
```typescript
{
  modelValue: boolean
  disabled?: boolean
  required?: boolean
}
```

**ButtonProps:**
```typescript
{
  type?: 'button' | 'submit' | 'reset'
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
  disabled?: boolean
  processing?: boolean
  fullWidth?: boolean
}
```

### Page Props (Inertia)

**RegisterPageProps:**
```typescript
{
  // Brak dodatkowych props
  // Używamy tylko globalnych shared props (csrf token, flash messages)
}
```

## 6. Zarządzanie stanem

### Global State (Inertia Props)
Brak globalnego stanu specyficznego dla Register page. Używamy standardowych shared props od Inertia.

### Local State (Register.vue)

**Inertia Form:**
- Form object utworzony przez `useForm<RegisterFormData>`
- Inicjalne wartości: wszystkie pola puste (name: '', email: '', password: '', password_confirmation: '', terms: false)
- Automatyczne zarządzanie: processing, errors, wasSuccessful

**Computed Properties:**
- `canSubmit: boolean` - sprawdza czy wszystkie required pola wypełnione (opcjonalnie, może być po prostu disabled gdy processing)
- `hasErrors: boolean` - czy form.errors nie jest pusty

**Methods:**
- `submit()` - handler submisji formularza (wywołuje form.post('/register'))
- `navigateToLogin()` - helper do nawigacji na /login (router.visit)

### Custom Composables

Brak custom composables specyficznych dla Register. Używamy standardowego Inertia `useForm`.

## 7. Dane Testowe (Mock Data)

**Dla widoku Register NIE MA danych testowych** - jest to pusty formularz dla nowego użytkownika.

### Development Helper (opcjonalnie)

Dla wygody podczas development można stworzyć helper do auto-fill formularza testowymi danymi.

**Lokalizacja (opcjonalnie):**
`resources/js/data/mockRegister.ts`

**Zawartość (przykład dla dev convenience):**
```typescript
mockRegisterData = {
  name: 'John Doe'
  email: 'john.doe.test@example.com'
  password: 'password123'
  password_confirmation: 'password123'
  terms: true
}
```

**UWAGA:** To tylko dla development testing, NIE używać w production. Można dodać button "Fill with test data" tylko w dev mode (import.meta.env.DEV).

## 8. Interakcje użytkownika

### 8.1 Wypełnienie formularza
**Trigger:** User wpisuje dane w pola

**Flow:**
1. User wypełnia name field (required, max 255 chars)
2. User wypełnia email field (required, email format)
3. User wypełnia password field (required, min 8 chars)
4. User wypełnia password confirmation field (required, must match password)
5. User akceptuje Terms of Service checkbox (jeśli pole włączone)
6. Submit button staje się enabled (jeśli wszystkie required fields wypełnione)

**Walidacja podczas wypełniania:**
- HTML5 validation na blur/submit
- Visual feedback: error state na polach z invalid data
- Real-time validation (opcjonalnie): password confirmation match indicator

### 8.2 Submisja formularza
**Trigger:** Kliknięcie "Create Account" / "Register" button

**Flow:**
1. User klika submit button
2. Browser HTML5 validation sprawdza podstawowe wymagania
   - Jeśli validation fails: pokazuje native browser error messages
   - Jeśli validation passes: kontynuuj
3. Inertia form: set processing=true (button pokazuje spinner, jest disabled)
4. POST request do `/register` z form data
5. Backend (Laravel):
   - Walidacja wszystkich pól server-side
   - Sprawdzenie czy email unique (nie istnieje w users table)
   - Utworzenie nowego user record z email_verified_at=null
   - Wysłanie verification email (Laravel notification)
   - Zalogowanie użytkownika (auth()->login())
6. Response handling:
   - **Success:** Redirect do `/custodianships` (Dashboard)
   - **Error:** Pozostanie na /register, form.errors wypełniony błędami
7. Set processing=false (Inertia automatycznie)

**Success scenario:**
- Redirect do Dashboard (`/custodianships`)
- EmailVerificationBanner wyświetlony na górze Dashboard
- Flash message (opcjonalnie): "Account created successfully. Check your email to verify your account."
- User może zacząć tworzyć custodianships (zapisywane jako draft do czasu weryfikacji email)

**Error scenario:**
- User pozostaje na /register page
- Błędy walidacji wyświetlane inline pod odpowiednimi polami (przez FormField)
- Submit button wraca do enabled state
- User może poprawić błędy i spróbować ponownie

**Możliwe błędy server-side:**
- Email already taken: "The email has already been taken."
- Password too short: "The password must be at least 8 characters."
- Passwords don't match: "The password confirmation does not match."
- Terms not accepted: "You must accept the terms of service." (jeśli ToS required)
- Invalid email format: "The email must be a valid email address."

### 8.3 Nawigacja do Login
**Trigger:** Kliknięcie "Already have an account? Log in" link

**Flow:**
1. User klika link
2. Inertia navigation: `router.visit('/login')`
3. Płynne przejście do Login page (bez full page reload)
4. Brak zachowania stanu formularza Register (reset)

## 9. Warunki i walidacja

### 9.1 Name field
**Warunek:** required, max 255 znaków
**Komponenty:** Register.vue (FormField + TextInput)
**Efekt:**
- Frontend: HTML5 required attribute, maxlength attribute
- Backend: validation rule 'required|string|max:255'
- Error messages:
  - "The name field is required."
  - "The name may not be greater than 255 characters."
- Visual: pole z red border gdy error, error text poniżej pola

### 9.2 Email field
**Warunek:** required, valid email format, unique w bazie
**Komponenty:** Register.vue (FormField + TextInput type="email")
**Efekt:**
- Frontend: HTML5 email validation (type="email"), required attribute
- Backend: validation rule 'required|email|unique:users,email'
- Error messages:
  - "The email field is required."
  - "The email must be a valid email address."
  - "The email has already been taken."
- Visual: pole z red border + error text poniżej

### 9.3 Password field
**Warunek:** required, minimum 8 znaków
**Komponenty:** Register.vue (FormField + TextInput type="password")
**Efekt:**
- Frontend: HTML5 minlength="8", required attribute
- Backend: validation rule 'required|string|min:8'
- Error messages:
  - "The password field is required."
  - "The password must be at least 8 characters."
- Visual: pole z red border + error text poniżej
- Security: type="password" maskuje znaki

### 9.4 Password Confirmation field
**Warunek:** required, must match password field
**Komponenty:** Register.vue (FormField + TextInput type="password")
**Efekt:**
- Frontend: required attribute, brak automatycznej walidacji match (tylko server-side)
- Backend: validation rule 'required|same:password'
- Error message: "The password confirmation does not match."
- Visual: pole z red border + error text gdy mismatch
- Opcjonalnie: real-time visual indicator (green checkmark gdy passwords match)

### 9.5 Terms of Service checkbox (opcjonalnie w MVP)
**Warunek:** required jeśli pole włączone
**Komponenty:** Register.vue (FormField + Checkbox)
**Efekt:**
- Frontend: required attribute na checkbox (jeśli włączone)
- Backend: validation rule 'accepted' (jeśli włączone)
- Error message: "You must accept the terms of service."
- Visual: checkbox z label, link do ToS w osobnym tab/window
- Submit button disabled dopóki nie checked (opcjonalnie)

### 9.6 Submit button state
**Warunek:** processing=false AND (all required fields filled OR no client-side check)
**Komponenty:** Register.vue (Button)
**Efekt:**
- Button disabled gdy processing=true
- Button pokazuje spinner gdy processing=true
- Opcjonalnie: button disabled gdy required fields empty (client-side check)

## 10. Obsługa błędów

### 10.1 Walidacja server-side failure
**Scenariusz:** Backend zwraca HTTP 422 Unprocessable Entity z validation errors

**Obsługa:**
- Inertia automatycznie mapuje errors do form.errors object
- FormField components wyświetlają error messages pod odpowiednimi polami
- Processing state wraca do false
- User pozostaje na /register page
- Form data pozostaje wypełniony (nie reset)
- Focus przeniesiony na pierwsze błędne pole (opcjonalnie, accessibility)

**Przykład:**
```
Email field:
[john@example.com_______________]
↓ (red text below)
The email has already been taken.
```

### 10.2 Email already taken
**Scenariusz:** User próbuje zarejestrować się z emailem który już istnieje w bazie

**Obsługa:**
- Backend validation fails: 'email' => 'unique:users'
- Error returned: form.errors.email = "The email has already been taken."
- Error wyświetlany pod email field (red text, FormField component)
- Sugestia dla usera: "Already have an account? Log in" link jest widoczny
- User może zmienić email i spróbować ponownie

### 10.3 Passwords don't match
**Scenariusz:** password !== password_confirmation

**Obsługa:**
- Backend validation fails: 'password_confirmation' => 'same:password'
- Error returned: form.errors.password_confirmation = "The password confirmation does not match."
- Error wyświetlany pod password confirmation field
- User może poprawić password lub password_confirmation i spróbować ponownie
- Opcjonalnie: show visual indicator (X icon) w real-time gdy passwords nie match

### 10.4 Network error / timeout
**Scenariusz:** POST request do /register fails z powodu network issue lub timeout

**Obsługa:**
- Inertia default timeout: 30 sekund
- Po timeout: error caught
- Toast notification (error): "Request timed out. Please check your connection and try again."
- Processing state wraca do false
- Form data preserved (nie reset)
- User może spróbować ponownie (kliknąć submit again)
- Opcjonalnie: automatic retry logic (max 2 retries)

### 10.5 Terms not accepted (jeśli required)
**Scenariusz:** User nie zaznaczył Terms of Service checkbox (jeśli pole jest required)

**Obsługa:**
- Frontend: submit button disabled dopóki checkbox nie checked (opcjonalnie)
- Backend: validation fails jeśli terms field missing lub false
- Error returned: form.errors.terms = "You must accept the terms of service."
- Error wyświetlany pod checkbox (red text)
- User musi zaznaczyć checkbox aby kontynuować

### 10.6 Server error (500)
**Scenariusz:** Unexpected server error podczas procesu rejestracji

**Obsługa:**
- Backend throws exception (np. database connection error)
- HTTP 500 returned
- Toast notification (error): "Something went wrong. Please try again later."
- Processing state wraca do false
- Form data preserved
- User może spróbować ponownie
- Opcjonalnie: log error details do monitoring (Nightwatch/Sentry)

### 10.7 Invalid email format (client-side)
**Scenariusz:** User wpisuje niepoprawny format email (przed submitem)

**Obsługa:**
- HTML5 validation (type="email") sprawdza format
- Browser native error message: "Please enter a valid email address."
- Visual indicator: invalid state na input (red border przez :invalid pseudo-class)
- Submit blocked dopóki email format nie poprawny
- Fallback: jeśli client-side validation ominięty, server-side validation catches

## 11. Kroki implementacji

### Krok 1: Przygotowanie typów (15 min)
- Utwórz `types/auth.ts` z RegisterFormData interface
- Dodaj do `types/components.ts`: TextInputProps, FormFieldProps, CheckboxProps, ButtonProps (jeśli nie istnieją)
- Dokumentuj każdy typ z JSDoc comments

### Krok 2: Layout GuestLayout (30 min)
- Utwórz `Layouts/GuestLayout.vue` (jeśli nie istnieje z Laravel Breeze)
- Centered card layout z logo na górze
- Responsive design (mobile-first)
- Footer z linkami (opcjonalnie)
- Slot dla main content

### Krok 3: Komponent TextInput (45 min)
- Utwórz `Components/TextInput.vue`
- Props: modelValue, type, placeholder, required, autocomplete, disabled, minlength
- Emits: update:modelValue
- Tailwind styling: różne stany (normal, focus, error, disabled)
- HTML5 validation attributes
- Accessibility: proper ARIA attributes

### Krok 4: Komponent FormField (30 min)
- Utwórz `Components/FormField.vue`
- Props: label, name, error, required, helperText
- Slots: default (dla input component)
- Label z required indicator (*) jeśli required=true
- Error message styling (red text, red border on input)
- Helper text styling (gray, smaller font)

### Krok 5: Komponent Checkbox (30 min)
- Utwórz `Components/Checkbox.vue`
- Props: modelValue, disabled, required
- Emits: update:modelValue
- Custom checkbox styling (Tailwind lub Shadcn)
- Label slot dla tekstu z linkami
- Accessible (keyboard navigation, ARIA)

### Krok 6: Komponent Button (30 min)
- Utwórz `Components/Button.vue`
- Props: type, variant, disabled, processing, fullWidth
- Slots: default (tekst przycisku)
- Variants: primary (blue), secondary (gray), danger (red), ghost
- Loading state: spinner icon (Heroicon lub podobny)
- Disabled state: opacity + cursor-not-allowed
- Responsive: fullWidth na mobile

### Krok 7: Główna strona Register.vue (2h)
- Utwórz `Pages/Auth/Register.vue`
- Użyj GuestLayout jako wrapper
- Setup Inertia useForm z RegisterFormData
- Wszystkie FormFields:
  - Name (TextInput)
  - Email (TextInput type="email")
  - Password (TextInput type="password")
  - Password Confirmation (TextInput type="password")
  - Terms (Checkbox - opcjonalnie)
- Submit button (Button type="submit" variant="primary")
- Link do Login ("Already have an account? Log in")
- Submit handler: form.post('/register')
- Error handling: automatic przez Inertia + FormField

### Krok 8: Backend - RegisteredUserController (1h)
- Sprawdź czy Laravel Breeze już dostarcza controller
- Jeśli nie: utwórz `Http/Controllers/Auth/RegisteredUserController.php`
- Method `store()`:
  - Validate request: name (required|max:255), email (required|email|unique:users), password (required|min:8), password_confirmation (required|same:password), terms (accepted jeśli required)
  - Create user: User::create() z hashed password
  - Set email_verified_at = null
  - Send verification email: $user->sendEmailVerificationNotification()
  - Login user: auth()->login($user)
  - Redirect: Inertia::location('/custodianships') z flash message

### Krok 9: Routing (15 min)
- Sprawdź czy Laravel Breeze już dostarcza routes w `routes/auth.php`
- Jeśli nie: dodaj route GET/POST `/register` do RegisteredUserController
- Middleware: guest na obu routes

### Krok 10: Verification email notification (30 min)
- Sprawdź Laravel default VerifyEmail notification
- Customize jeśli potrzebne (mailable view):
  - Subject: "Verify Your Email Address - Just In Case"
  - Body: simple, clear message z verify button/link
  - Link valid for 24h (config w Laravel)
- Styling email template: inline CSS dla email clients

### Krok 11: Testy manualne (1h)
- Test wypełnienia formularza (wszystkie pola poprawnie)
- Test walidacji:
  - Empty fields (required validation)
  - Invalid email format
  - Password too short (<8 chars)
  - Passwords don't match
  - Email already taken (create duplicate account)
  - Terms not accepted (jeśli required)
- Test submisji success:
  - User utworzony w bazie
  - Verification email wysłany
  - User zalogowany
  - Redirect do Dashboard z banner
- Test submisji error:
  - Errors wyświetlane pod polami
  - Form data preserved
  - User może poprawić i retry
- Test linku do Login (nawigacja działa)
- Test responsywności (mobile, tablet, desktop)
- Test accessibility (keyboard navigation, screen reader)

### Krok 12: Integracja z EmailVerificationBanner (30 min)
- Sprawdź czy Dashboard wyświetla banner gdy user.email_verified_at === null
- Jeśli nie: utwórz EmailVerificationBanner component
- Test flow: Register → Dashboard shows banner → Click "Resend verification email" → Email sent

### Krok 13: Dokumentacja i cleanup (20 min)
- Remove console.logs
- Format code (Laravel Pint dla PHP, Prettier dla Vue)
- Add comments do complex logic
- Update README jeśli dodane nowe komponenty reusable
- Git commit z descriptive message

### Krok 14: Code review i refactoring (opcjonalnie, 30 min)
- Review komponentów pod kątem reusability
- Extract common logic do composables jeśli potrzebne
- Optimize performance (memo, lazy loading jeśli potrzebne)
- Security review (sanitization, CSRF, SQL injection protection)

**Całkowity szacowany czas:** 8-10 godzin

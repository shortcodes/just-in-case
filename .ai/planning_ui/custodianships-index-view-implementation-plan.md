# Plan implementacji widoku Dashboard (Custodianships Index)

## 1. Przegląd

Dashboard jest głównym widokiem aplikacji Just In Case, dostępnym po zalogowaniu użytkownika. Wyświetla listę wszystkich powiernictw z możliwością szybkich akcji i monitorowania statusów timerów w czasie rzeczywistym.

### Kluczowe cele:
- Natychmiastowy przegląd wszystkich powiernictw i ich statusów
- Szybki reset timera bez przechodzenia do widoku szczegółów (optimistic UI)
- Wyraźne ostrzeżenia o zbliżających się wygaśnięciach
- Intuicyjny onboarding dla nowych użytkowników (empty state)
- Responsywność i wydajność (dynamiczne timery aktualizowane co sekundę)

## 2. Routing widoku

**Ścieżka:** `/custodianships`
**Alias:** `/dashboard` (opcjonalnie)
**Middleware:** `auth`
**Query params:** `?status=active|draft|completed|failed` (filtrowanie)

## 3. Struktura komponentów

```
Index.vue (główna strona)
├── AuthenticatedLayout
│   └── Breadcrumbs
├── EmailVerificationBanner (warunkowy - !emailVerified)
├── ExpiringCustodianshipsBanner (warunkowy - expiringCount > 0)
├── Header
│   ├── Tytuł "My Custodianships"
│   ├── FilterDropdown
│   └── CreateButton
└── Content
    ├── EmptyState (warunkowy - brak powiernictw)
    └── CustodianshipList
        └── CustodianshipCard (wiele)
            ├── StatusBadge
            ├── TimerProgressBar
            ├── RecipientList (read-only)
            └── ActionButtons
                ├── ConfirmableButton (Reset)
                ├── Button (View)
                └── Button (Edit)
```

## 4. Szczegóły komponentów

### 4.1 Index.vue (główna strona)

**Przeznaczenie:**
Główny kontener zarządzający listą powiernictw, filtrowaniem, wyświetlaniem bannerów i obsługą akcji użytkownika.

**Główne elementy:**
- Layout z navbar i breadcrumbs
- Conditional banners (weryfikacja email, wygasające timery)
- Header z tytułem, filtrem statusu i przyciskiem tworzenia
- Lista kart powiernictw lub empty state
- Modale (limit powiernictw, potwierdzenie Reset All)

**Obsługiwane interakcje:**
- Filtrowanie po statusie
- Utworzenie nowego powiernictwa (sprawdzenie limitu 3)
- Ponowne wysłanie emaila weryfikacyjnego
- Reset wszystkich wygasających timerów (modal + batch)

**Walidacja:**
- Limit 3 powiernictw (freemium) → modal przy przekroczeniu
- Brak powiernictw → EmptyState
- Email niezweryfikowany → EmailVerificationBanner
- Powiernictwa <7 dni → ExpiringCustodianshipsBanner

**Propsy (z Inertii):**
- `user: UserViewModel`
- `custodianships: CustodianshipViewModel[]`
- `stats: DashboardStatsViewModel`

### 4.2 CustodianshipCard.vue

**Przeznaczenie:**
Karta pojedynczego powiernictwa z kluczowymi informacjami i akcjami.

**Główne elementy:**
- Header: nazwa + StatusBadge
- TimerProgressBar z dynamicznym countdownem
- RecipientList (max 2 widoczne + "and X more")
- Przyciski akcji (Reset, View, Edit)

**Obsługiwane interakcje:**
- Reset timera (inline confirmation + optimistic UI)
- Nawigacja do widoku szczegółów (View)
- Nawigacja do edycji (Edit)

**Walidacja:**
- Timer wygasły → disable Reset, amber background, tooltip
- Status completed/failed → disable Reset

**Propsy:**
- `custodianship: CustodianshipViewModel`
- `isResetting: boolean` (loading state z parenta)

**Emits:**
- `reset(custodianshipId)`

### 4.3 TimerProgressBar.vue

**Przeznaczenie:**
Dynamiczny progress bar z countdownem aktualizowanym co sekundę, zmieniający kolor według progów.

**Główne elementy:**
- Progress bar (Shadcn Progress)
- Tekst: "X days remaining of Y"
- Tooltip z dokładną datą wygaśnięcia
- Kolorowanie: green (>30d), yellow (7-30d), red (<7d)

**Obsługiwane interakcje:**
- Hover → tooltip z exact timestamp
- Countdown reaches 0 → emit `expired`

**Walidacja:**
- Brak nextTriggerAt (draft) → "Timer inactive" (gray)
- Timer wygasły → "Expired" (red, 0%)

**Propsy:**
- `nextTriggerAt: string | null`
- `intervalDays: number`
- `status: CustodianshipStatus`

**Emits:**
- `expired` (opcjonalnie)

**Używa composable:**
- `useTimerCountdown` (dynamiczny countdown, Page Visibility API)

### 4.4 StatusBadge.vue

**Przeznaczenie:**
Kolorowy badge ze statusem powiernictwa.

**Główne elementy:**
- Badge (Shadcn Badge)
- Ikona (opcjonalnie, Heroicon)
- Tekst statusu

**Obsługiwane interakcje:**
- Brak (pure display)

**Propsy:**
- `status: CustodianshipStatus | 'pending'`
- `deliveryStatus?: DeliveryStatus`

**Styling:**
- draft → gray
- active → green
- completed → blue
- pending → amber
- failed → red

### 4.5 ConfirmableButton.vue

**Przeznaczenie:**
Przycisk z inline confirmation (click-to-expand pattern).

**Główne elementy:**
- Initial: Single button
- Expanded: Mini-toolbar [Confirm] [Cancel]
- Auto-collapse po 5s lub click outside

**Obsługiwane interakcje:**
- Kliknięcie → expand
- Confirm → emit + collapse
- Cancel → collapse
- Auto-collapse (timeout, click outside)

**Propsy:**
- `label: string`
- `confirmLabel?: string`
- `cancelLabel?: string`
- `disabled?: boolean`
- `tooltipDisabled?: string`

**Emits:**
- `confirm`

### 4.6 RecipientList.vue

**Przeznaczenie:**
Lista odbiorców (read-only mode dla Index).

**Główne elementy:**
- Lista emaili
- "Added X days ago" (relative time z tooltipem)
- "and X more" dla >2 odbiorców (na Index)

**Propsy:**
- `recipients: RecipientViewModel[]`
- `readonly: boolean`
- `maxVisible?: number`

### 4.7 EmptyState.vue

**Przeznaczenie:**
Stan pusty dla nowych użytkowników bez powiernictw.

**Główne elementy:**
- Ikona (slot)
- Tytuł
- Opis
- Przycisk CTA (slot)

**Propsy:**
- `title: string`
- `description: string`

**Slots:**
- `icon`
- `action`

### 4.8 EmailVerificationBanner.vue

**Przeznaczenie:**
Banner przypominający o weryfikacji email.

**Główne elementy:**
- Alert (Shadcn Alert, warning)
- Tekst: "Please verify your email to activate custodianships."
- Przycisk "Resend verification email"

**Obsługiwane interakcje:**
- Kliknięcie Resend → emit + loading state

**Propsy:**
- `userEmail: string`

**Emits:**
- `resend`

### 4.9 ExpiringCustodianshipsBanner.vue

**Przeznaczenie:**
Alert o powiernictwach bliskich wygaśnięcia.

**Główne elementy:**
- Alert (Shadcn Alert, destructive)
- Tekst: "You have X custodianships expiring soon..."
- Przycisk "Reset All"

**Obsługiwane interakcje:**
- Kliknięcie Reset All → emit

**Propsy:**
- `expiringCount: number`
- `expiringCustodianships: CustodianshipViewModel[]`

**Emits:**
- `resetAll`

## 5. Typy

### ViewModels

**UserViewModel:**
```typescript
{
  id: number
  name: string
  email: string
  emailVerified: boolean
  emailVerifiedAt: string | null
  createdAt: string
}
```

**CustodianshipViewModel:**
```typescript
{
  id: number
  uuid: string
  name: string
  status: 'draft' | 'active' | 'completed'
  deliveryStatus: 'pending' | 'sent' | 'delivered' | 'failed' | 'bounced' | null
  interval: string // ISO 8601
  intervalDays: number
  lastResetAt: string | null
  nextTriggerAt: string | null
  activatedAt: string | null
  recipients: RecipientViewModel[]
  messageContent: string | null
  attachments: AttachmentViewModel[]
  createdAt: string
  updatedAt: string
}
```

**RecipientViewModel:**
```typescript
{
  id: number
  email: string
  createdAt: string
}
```

**AttachmentViewModel:**
```typescript
{
  id: number
  name: string
  fileName: string
  size: number
  mimeType: string
  createdAt: string
}
```

**DashboardStatsViewModel:**
```typescript
{
  totalCount: number
  draftCount: number
  activeCount: number
  completedCount: number
  failedCount: number
  expiringCount: number // <7 dni
}
```

**PageProps (Inertia):**
```typescript
{
  user: UserViewModel
  custodianships: CustodianshipViewModel[]
  stats: DashboardStatsViewModel
}
```

## 6. Zarządzanie stanem

### Global State (Inertia Props)
- `user` - dane użytkownika
- `custodianships` - lista powiernictw
- `stats` - statystyki dashboardu

### Local State (Index.vue)
- `selectedStatus: Ref<string>` - wybrany filtr ('all', 'draft', 'active', etc.)
- `isResetting: Ref<Record<number, boolean>>` - loading state per custodianship
- `isResendingVerification: Ref<boolean>`
- `isLimitModalOpen: Ref<boolean>`
- `isResetAllModalOpen: Ref<boolean>`

### Computed Properties
- `filteredCustodianships` - filtrowanie według selectedStatus
- `sortedCustodianships` - sortowanie: drafts last, completed/failed last, active by nextTriggerAt ASC
- `isEmpty` - czy lista pusta
- `canCreateNew` - czy można utworzyć nowe (length < 3)
- `expiringCustodianships` - lista z <7 dni do wygaśnięcia
- `showEmailBanner` - czy pokazać banner weryfikacji
- `showExpiringBanner` - czy pokazać banner wygasających

### Custom Composables

**useTimerCountdown:**
- Dynamiczny countdown aktualizowany co sekundę
- Page Visibility API (pause gdy tab nieaktywny)
- Re-sync przy powrocie do taba
- Return: daysRemaining, progressPercentage, colorClass, isExpired, formattedTimeRemaining, exactExpiryDate

**useCustodianshipFilters (opcjonalnie):**
- Filtrowanie po statusie
- Sync z URL query params
- Return: selectedStatus, filteredCustodianships, setStatusFilter

## 7. Dane Testowe (Mock Data)

### Lokalizacja
`resources/js/data/mockCustodianships.ts`

### Zawartość
- `mockUser` - user z emailVerified=false (dla testowania bannera)
- `mockCustodianships` - 3 przykłady:
  1. Active, wygasa za 5 dni (RED, expiring)
  2. Active, wygasa za 25 dni (YELLOW)
  3. Draft (GRAY, timer inactive)
- `mockStats` - obliczone z mockCustodianships
- `mockCustodianshipsIndexPageProps` - kompletne props dla strony

### Użycie
- Development: import i użyj jako props
- Production: dane z kontrolera Laravel przez Inertia

## 8. Interakcje użytkownika

### 8.1 Utworzenie nowego powiernictwa
**Trigger:** Kliknięcie "Create New Custodianship"

**Flow:**
1. Sprawdź `custodianships.length >= 3`
2. Jeśli TAK: otwórz modal z komunikatem o limicie
3. Jeśli NIE: `router.visit('/custodianships/create')`

### 8.2 Reset timera
**Trigger:** Kliknięcie "Reset Timer" → Confirm w mini-toolbar

**Flow:**
1. ConfirmableButton expand → Confirm
2. CustodianshipCard emit `reset(id)`
3. Index.vue:
   - Optimistic update: lokalnie ustaw lastResetAt=now, nextTriggerAt=now+interval
   - Set `isResetting[id]=true`
   - POST `/custodianships/{id}/reset`
   - On success: set `isResetting[id]=false`, brak toastu
   - On error: rollback wartości, set `isResetting[id]=false`, toast error

### 8.3 Filtrowanie po statusie
**Trigger:** Wybór opcji w FilterDropdown

**Flow:**
1. Update `selectedStatus.value`
2. Re-compute `filteredCustodianships`
3. Opcjonalnie: update URL query param + Inertia visit (preserveScroll)

### 8.4 Ponowne wysłanie emaila weryfikacyjnego
**Trigger:** Kliknięcie "Resend verification email"

**Flow:**
1. EmailVerificationBanner emit `resend`
2. Index.vue:
   - Set `isResendingVerification=true`
   - POST `/email/verification-notification`
   - On success: toast "Verification email sent"
   - On error: toast error
   - Set `isResendingVerification=false`

### 8.5 Reset wszystkich wygasających
**Trigger:** Kliknięcie "Reset All" w ExpiringCustodianshipsBanner

**Flow:**
1. ExpiringCustodianshipsBanner emit `resetAll`
2. Index.vue:
   - Otwórz modal z listą powiernictw
   - Po potwierdzeniu: loop przez expiringCustodianships
   - Dla każdego: wywołaj handleReset(id)
   - Toast: "Reset X custodianships successfully"

### 8.6 Nawigacja do szczegółów/edycji
**Trigger:** Kliknięcie "View" lub "Edit"

**Flow:**
- View: `router.visit('/custodianships/{uuid}')`
- Edit: `router.visit('/custodianships/{uuid}/edit')`

## 9. Warunki i walidacja

### 9.1 Limit powiernictw (freemium)
**Warunek:** `custodianships.length >= 3`
**Komponenty:** Index.vue (CreateButton)
**Efekt:** Modal z komunikatem o limicie, brak nawigacji do Create

### 9.2 Timer wygasły
**Warunek:** `nextTriggerAt <= now()`
**Komponenty:** TimerProgressBar, ConfirmableButton, CustodianshipCard
**Efekt:**
- Progress bar: 0%, red, "Expired"
- Reset button: disabled, tooltip "Cannot reset - message will be sent shortly"
- Card: amber background + border

### 9.3 Email niezweryfikowany
**Warunek:** `!user.emailVerified`
**Komponenty:** Index.vue (EmailVerificationBanner)
**Efekt:** Banner na górze z przyciskiem Resend

### 9.4 Powiernictwa bliskie wygaśnięcia
**Warunek:** `stats.expiringCount > 0`
**Komponenty:** Index.vue (ExpiringCustodianshipsBanner)
**Efekt:** Banner z przyciskiem "Reset All"

### 9.5 Brak powiernictw
**Warunek:** `custodianships.length === 0`
**Komponenty:** Index.vue (EmptyState)
**Efekt:** EmptyState zamiast listy kart

### 9.6 Status powiernictwa
**Warunek:** `custodianship.status`
**Komponenty:** StatusBadge, TimerProgressBar, CustodianshipCard
**Efekt:**
- draft: gray badge, "Timer inactive", Reset disabled
- active: green badge, dynamiczny timer, Reset enabled (jeśli !expired)
- completed: blue badge, "Sent", Reset disabled
- failed: red badge (deliveryStatus), Reset disabled

## 10. Obsługa błędów

### 10.1 Reset timer failure
**Scenariusz:** POST `/custodianships/{id}/reset` error

**Obsługa:**
- Rollback optimistic update (przywróć old values)
- Set `isResetting[id]=false`
- Toast error: "Failed to reset timer. Please try again."

### 10.2 Resend verification failure
**Scenariusz:** POST `/email/verification-notification` error

**Obsługa:**
- Set `isResendingVerification=false`
- Toast error: "Failed to send verification email. Please try again."

### 10.3 Brak danych
**Scenariusz:** `custodianships.length === 0`

**Obsługa:**
- Render EmptyState (nie error)

### 10.4 Invalid timer data
**Scenariusz:** `status=active` ale `nextTriggerAt=null`

**Obsługa:**
- TimerProgressBar fallback: "Invalid timer data" (gray)
- Console error log
- Reset button disabled

### 10.5 Network timeout
**Scenariusz:** Request przekracza timeout

**Obsługa:**
- Inertia timeout handling (default 30s)
- Rollback + error callback
- Toast: "Request timed out. Please check your connection and try again."

### 10.6 Race condition (multiple resets)
**Scenariusz:** User klika wielokrotnie Reset dla tego samego powiernictwa

**Obsługa:**
- Track `isResetting[id]` per custodianship
- Disable Reset button gdy `isResetting[id]=true`
- Ignore kolejne kliknięcia

### 10.7 Timer wygasa podczas viewing
**Scenariusz:** Countdown dochodzi do 0 podczas gdy user patrzy

**Obsługa:**
- TimerProgressBar emit `expired`
- CustodianshipCard update UI (disable Reset, amber bg, StatusBadge→"Pending")
- Brak auto-refresh (user może ręcznie odświeżyć)
- Brak intrusywnych toastów

## 11. Kroki implementacji

### Krok 1: Przygotowanie typów i mock data (30-45 min)
- Utwórz `types/models.ts` z ViewModels
- Utwórz `types/components.ts` z props/emits interfaces
- Utwórz `types/composables.ts` z return types
- Utwórz `data/mockCustodianships.ts` z przykładowymi danymi

### Krok 2: Composable useTimerCountdown (1-1.5h)
- Implementuj w `composables/useTimerCountdown.ts`
- Użyj dayjs dla obliczeń
- setInterval z 1s tickiem
- Page Visibility API (pause/resume)
- Return wszystkie computed zgodnie z interfejsem

### Krok 3: Composable useCustodianshipFilters (30-45 min, opcjonalnie)
- Implementuj w `composables/useCustodianshipFilters.ts`
- Filtrowanie + sync z URL query params
- Return selectedStatus, filteredCustodianships, setStatusFilter

### Krok 4: Komponent StatusBadge (30 min)
- Utwórz `Components/StatusBadge.vue`
- Shadcn Badge + computed styling map
- Obsłuż wszystkie statusy (draft, active, completed, pending, failed)

### Krok 5: Komponent TimerProgressBar (1h)
- Utwórz `Components/TimerProgressBar.vue`
- Użyj useTimerCountdown
- Shadcn Progress + Tooltip
- Obsłuż edge cases (draft, expired)

### Krok 6: Komponent ConfirmableButton (1h)
- Utwórz `Components/ConfirmableButton.vue`
- Click-to-expand pattern
- Auto-collapse (5s timeout, click outside)
- Disabled state z tooltipem

### Krok 7: Komponent RecipientList (30 min)
- Utwórz `Components/RecipientList.vue`
- Read-only mode
- maxVisible (2 dla Index) + "and X more"
- Relative time z tooltipem

### Krok 8: Komponent EmptyState (20 min)
- Utwórz `Components/EmptyState.vue`
- Slots dla icon i action
- Centered layout

### Krok 9: Komponent CustodianshipCard (1.5-2h)
- Utwórz `Components/CustodianshipCard.vue`
- Shadcn Card
- Komponuj wszystkie dzieci (StatusBadge, TimerProgressBar, RecipientList, ConfirmableButton)
- Computed dla disable states
- Amber background dla expired

### Krok 10: Komponent EmailVerificationBanner (30 min)
- Utwórz `Components/EmailVerificationBanner.vue`
- Shadcn Alert (warning)
- Przycisk Resend z loading state

### Krok 11: Komponent ExpiringCustodianshipsBanner (30 min)
- Utwórz `Components/ExpiringCustodianshipsBanner.vue`
- Shadcn Alert (destructive)
- Przycisk Reset All

### Krok 12: Główna strona Index.vue (2-3h)
- Utwórz `Pages/Custodianships/Index.vue`
- AuthenticatedLayout + Breadcrumbs
- Komponuj wszystkie komponenty
- Implementuj handlers (reset, resend, filter, create, resetAll)
- Computed properties (filtered, sorted)
- Conditional renders (banners, empty state)
- Modale (limit, reset all confirmation)

### Krok 13: Integracja z Laravel (1-2h)
- Kontroler `CustodianshipController@index`: query + map do ViewModels + Inertia render
- Kontroler `CustodianshipController@reset`: walidacja + update + reset log
- Routing w `routes/web.php`
- Test Inertia data flow

### Krok 14: Testy i debugging (1-2h)
- Test wszystkich scenariuszy interakcji
- Test edge cases i błędów
- Test responsywności (mobile, tablet, desktop)
- Test wydajności (timery, optimistic UI)
- Lighthouse audit (performance, accessibility)

### Krok 15: Dokumentacja i cleanup (30 min)
- Remove console.logs
- Format code
- JSDoc dla composables
- Update README
- Git commit

**Całkowity szacowany czas:** 12-17 godzin

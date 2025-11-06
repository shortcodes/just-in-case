# Plan implementacji widoku Show Custodianship

## 1. Przegląd

Widok Show Custodianship wyświetla szczegółowe informacje o pojedynczym powiernictwie. Użytkownik może tutaj zresetować timer, przeglądać pełną treść wiadomości, listę odbiorców, załączniki oraz historię resetów. Widok obsługuje różne statusy (draft, active, completed, failed) i odpowiednio dostosowuje dostępne akcje.

### Kluczowe cele:
- Pełny podgląd wszystkich informacji o powiernictwie
- Główna akcja: Reset Timer (prominent, green button)
- Przegląd historii resetów (opcjonalnie w MVP)
- Jasne rozróżnienie statusów (kolory, ikony, komunikaty)
- Danger Zone dla akcji destrukcyjnych (Delete)

## 2. Routing widoku

**Ścieżka:** `/custodianships/{uuid}`
**Middleware:** `auth`
**HTTP Method:** GET
**Route Model Binding:** przez UUID (nie ID)

## 3. Struktura komponentów

```
Show.vue (główna strona)
├── AuthenticatedLayout
│   └── Breadcrumbs
├── PageHeader
│   ├── Tytuł (custodianship name)
│   ├── StatusBadge
│   └── ActionButtons
│       ├── ConfirmableButton (Reset Timer - primary)
│       └── Button (Edit - outline)
├── TimerSection (Card)
│   ├── TimerProgressBar
│   └── TimerInfo (last reset, next trigger)
├── DetailsSection (Card)
│   ├── MessageContent (rendered markdown)
│   └── IntervalInfo
├── RecipientsSection (Card)
│   └── RecipientList (read-only, unlimited visible)
│       └── RecipientItem
│           ├── Email
│           └── AddedDate (relative + tooltip)
├── AttachmentsSection (Card)
│   └── AttachmentList
│       └── AttachmentItem
│           ├── Icon (file type)
│           ├── Name + Size
│           └── Button (Download)
├── ResetHistorySection (Card, collapsible, opcjonalnie)
│   ├── Header (clickable, chevron)
│   └── ResetHistoryTable
│       └── Rows (timestamp, method, IP)
└── DangerZone (Card, red border)
    ├── Warning text
    └── DeleteButton
        └── DeleteCustodianshipModal
```

## 4. Szczegóły komponentów

### 4.1 Show.vue (główna strona)

**Przeznaczenie:**
Główny kontener wyświetlający wszystkie informacje o powiernictwie i obsługujący akcje użytkownika.

**Główne elementy:**
- AuthenticatedLayout
- Breadcrumbs: Home > Custodianships > [Custodianship Name]
- Header z nazwą, badge, action buttons
- Sekcje: Timer, Details, Recipients, Attachments, Reset History (opcjonalnie), Danger Zone
- Modals: Delete confirmation

**Obsługiwane interakcje:**
- Reset timera (ConfirmableButton + optimistic UI)
- Nawigacja do Edit
- Usunięcie powiernictwa (modal confirmation)
- Download załączników
- Rozwijanie/zwijanie Reset History (opcjonalnie)

**Walidacja:**
- Timer expired → disable Reset, show warning
- Status completed/failed → disable Reset
- User ownership → authorization check (policy)

**Propsy (z Inertii):**
- `custodianship: CustodianshipDetailViewModel`
- `user: UserViewModel`
- `resetHistory?: ResetLogViewModel[]` (opcjonalnie w MVP)

**Local State:**
- `isResetting: Ref<boolean>` - loading state dla Reset
- `isDeleting: Ref<boolean>` - loading state dla Delete
- `isDeleteModalOpen: Ref<boolean>`
- `isHistoryExpanded: Ref<boolean>` (dla Reset History)

### 4.2 TimerSection.vue

**Przeznaczenie:**
Sekcja wyświetlająca timer z progress bar i szczegółowymi informacjami.

**Główne elementy:**
- TimerProgressBar (z countdownem)
- Timer info text:
  - "Last reset: X days ago" (relative time + tooltip)
  - "Next trigger: January 30, 2025 14:30" (exact timestamp)
  - "Timer expires in: 45 days" (countdown)

**Obsługiwane interakcje:**
- Brak (pure display, interakcja w parent - Reset button)

**Propsy:**
- `custodianship: CustodianshipDetailViewModel`

**Special states:**
- Draft: "Timer inactive" (gray)
- Expired: "Timer expired. Message will be sent shortly." (amber)
- Completed: "Message sent on [date]" (blue)

### 4.3 MessageContentViewer.vue

**Przeznaczenie:**
Wyświetlenie renderowanej treści wiadomości (markdown → HTML).

**Główne elementy:**
- Rendered markdown (v-html z sanityzacją)
- Fallback jeśli brak content: "(No message content)"

**Propsy:**
- `content: string | null` (markdown)

**Security:**
- Sanityzacja HTML przed renderowaniem (DOMPurify lub backend pre-sanitized)
- Brak user scripts/styles

### 4.4 AttachmentList.vue (read-only)

**Przeznaczenie:**
Lista załączników z możliwością pobrania.

**Główne elementy:**
- Lista AttachmentItem (read-only mode)
- Empty state jeśli brak: "(No attachments)"

**Obsługiwane interakcje:**
- Download pliku (button per attachment)

**Propsy:**
- `attachments: AttachmentViewModel[]`
- `custodianshipUuid: string` (dla download URL)

### 4.5 AttachmentItem.vue

**Przeznaczenie:**
Pojedynczy załącznik z ikoną typu pliku i przyciskiem download.

**Główne elementy:**
- Ikona typu pliku (PDF, DOC, generic - bez thumbnails w MVP)
- Nazwa pliku + rozmiar (formatted)
- Download button (opcjonalnie: download icon)

**Obsługiwane interakcje:**
- Kliknięcie Download → download pliku

**Propsy:**
- `attachment: AttachmentViewModel`
- `custodianshipUuid: string`

**Download URL:**
`/custodianships/{uuid}/attachments/{attachmentId}/download`

### 4.6 ResetHistoryTable.vue (opcjonalnie w MVP)

**Przeznaczenie:**
Tabela z historią resetów timera.

**Główne elementy:**
- Tabela (Shadcn Table)
- Kolumny: Timestamp, Method, IP Address
- Sortowanie: newest first (DESC)
- Empty state: "(No reset history yet)"

**Propsy:**
- `resetHistory: ResetLogViewModel[]`

**Formatowanie:**
- Timestamp: relative time ("2 days ago") + tooltip z exact date
- Method: "Manual" / "Post-Edit" (human-readable)
- IP: raw IP address

### 4.7 DeleteCustodianshipModal.vue

**Przeznaczenie:**
Modal confirmation dla usunięcia powiernictwa (REQ-010: hard delete).

**Główne elementy:**
- Shadcn Dialog
- Warning header (red)
- Warning text: "This action is permanent and cannot be undone. All data, attachments, and history will be permanently deleted."
- Checkbox: "I understand this action is permanent"
- Text input: "Type custodianship name to confirm: [name]"
- Buttons: Cancel (outline), Delete Permanently (destructive red)

**Obsługiwane interakcje:**
- Check checkbox
- Type custodianship name (case-insensitive validation)
- Enable/disable Delete button based on validation

**Propsy:**
- `open: boolean` (v-model)
- `custodianshipName: string`

**Emits:**
- `confirm` - emitted po kliknięciu Delete (parent obsługuje DELETE request)
- `update:open(value: boolean)` - close modal

**Validation:**
- Delete button enabled tylko gdy:
  - Checkbox checked
  - Typed name === custodianshipName (case-insensitive)

### 4.8 DangerZone.vue

**Przeznaczenie:**
Sekcja z destrukcyjnymi akcjami (Delete), wizualnie oddzielona.

**Główne elementy:**
- Card z red border
- Header: "Danger Zone"
- Description: "Deleting this custodianship is permanent and cannot be undone."
- Delete button (destructive, red outline)

**Obsługiwane interakcje:**
- Kliknięcie Delete → open DeleteCustodianshipModal

**Propsy:**
- Brak (używa event emits)

**Emits:**
- `delete` - parent obsługuje modal

## 5. Typy

### ViewModels

**CustodianshipDetailViewModel** (extend CustodianshipViewModel):
```typescript
{
  // Wszystkie pola z CustodianshipViewModel +
  user: {
    id: number
    name: string
  }
  resetCount?: number // opcjonalnie, dla "Reset X times"
}
```

**ResetLogViewModel:**
```typescript
{
  id: number
  resetMethod: 'manual_button' | 'post_edit_modal'
  ipAddress: string
  userAgent: string
  createdAt: string // ISO timestamp
}
```

**PageProps (Inertia):**
```typescript
{
  user: UserViewModel
  custodianship: CustodianshipDetailViewModel
  resetHistory?: ResetLogViewModel[] // opcjonalnie w MVP
}
```

## 6. Zarządzanie stanem

### Global State (Inertia Props)
- `user` - dane zalogowanego użytkownika
- `custodianship` - szczegóły powiernictwa
- `resetHistory` - opcjonalnie, historia resetów

### Local State (Show.vue)
- `isResetting: Ref<boolean>` - loading podczas Reset
- `isDeleting: Ref<boolean>` - loading podczas Delete
- `isDeleteModalOpen: Ref<boolean>`
- `isHistoryExpanded: Ref<boolean>` - rozwinięcie/zwinięcie Reset History
- `deleteConfirmationName: Ref<string>` - input value w modal

### Computed Properties
- `canReset: boolean` - czy można resetować timer (active + !expired)
- `isExpired: boolean` - czy timer wygasł
- `statusDisplay: string` - human-readable status
- `canDelete: boolean` - czy można usunąć (zawsze true dla ownera w MVP)
- `isDeleteButtonEnabled: boolean` - walidacja modal (checkbox + name match)

### Custom Composables

**useTimerCountdown:**
- Reużyty z Dashboard (już zaimplementowany)
- Dynamiczny countdown dla TimerProgressBar

**useCustodianshipActions:**
- Centralizacja akcji: reset, delete
- Obsługa optimistic UI dla reset
- Return: handleReset, handleDelete, isResetting, isDeleting

## 7. Dane Testowe (Mock Data)

### Lokalizacja
Extend `resources/js/data/mockCustodianships.ts`

### Zawartość

**mockCustodianshipDetail:**
```typescript
{
  ...mockCustodianships[0], // active custodianship z 5 dni do wygaśnięcia
  user: {
    id: 1,
    name: 'John Doe',
  },
  resetCount: 15,
}
```

**mockResetHistory:**
```typescript
[
  {
    id: 1,
    resetMethod: 'manual_button',
    ipAddress: '192.168.1.1',
    userAgent: 'Mozilla/5.0...',
    createdAt: dayjs().subtract(2, 'days').toISOString(),
  },
  {
    id: 2,
    resetMethod: 'post_edit_modal',
    ipAddress: '192.168.1.1',
    userAgent: 'Mozilla/5.0...',
    createdAt: dayjs().subtract(10, 'days').toISOString(),
  },
  // ... więcej entries
]
```

**mockShowPageProps:**
```typescript
{
  user: mockUser,
  custodianship: mockCustodianshipDetail,
  resetHistory: mockResetHistory, // opcjonalnie
}
```

## 8. Interakcje użytkownika

### 8.1 Reset timera
**Trigger:** Kliknięcie "Reset Timer" → Confirm w mini-toolbar

**Flow:**
1. ConfirmableButton expand → Confirm
2. Show.vue:
   - Sprawdź `canReset` (active + !expired)
   - Store old values (lastResetAt, nextTriggerAt)
   - Optimistic update: lokalnie ustaw lastResetAt=now, nextTriggerAt=now+interval
   - Set `isResetting=true`
   - POST `/custodianships/{uuid}/reset`
   - On success:
     - Set `isResetting=false`
     - Brak toastu (UI już zaktualizowany)
     - Opcjonalnie: refresh resetHistory
   - On error:
     - Rollback: przywróć old values
     - Set `isResetting=false`
     - Toast error: "Failed to reset timer. Please try again."

### 8.2 Nawigacja do Edit
**Trigger:** Kliknięcie "Edit" button

**Flow:**
- `router.visit(`/custodianships/{uuid}/edit`)`

### 8.3 Download załącznika
**Trigger:** Kliknięcie "Download" przy załączniku

**Flow:**
1. GET `/custodianships/{uuid}/attachments/{attachmentId}/download`
2. Backend:
   - Sprawdź authorization (user owns custodianship)
   - Log download (IP, user_agent, timestamp)
   - Fetch file z S3
   - Stream file do browser (Content-Disposition: attachment)
3. Browser: download file

### 8.4 Rozwinięcie/zwinięcie Reset History
**Trigger:** Kliknięcie header "Reset History (X)" (opcjonalnie w MVP)

**Flow:**
1. Toggle `isHistoryExpanded`
2. Jeśli expanded: render ResetHistoryTable
3. Jeśli collapsed: ukryj tabelę, show tylko header

### 8.5 Usunięcie powiernictwa
**Trigger:** Kliknięcie "Delete Custodianship" w Danger Zone

**Flow:**
1. Set `isDeleteModalOpen=true`
2. Modal opens:
   - User checks checkbox
   - User types custodianship name
   - Walidacja: enable Delete button gdy oba spełnione
3. User klika "Delete Permanently":
   - Set `isDeleting=true`
   - DELETE `/custodianships/{uuid}`
   - Backend:
     - Delete custodianship record
     - Delete wszystkie attachments z S3
     - Delete message (cascade)
     - Delete recipients (cascade)
     - Delete reset logs (cascade)
   - On success:
     - Redirect do `/custodianships` (Index)
     - Toast: "Custodianship permanently deleted"
   - On error:
     - Set `isDeleting=false`
     - Close modal
     - Toast error: "Failed to delete custodianship. Please try again."

## 9. Warunki i walidacja

### 9.1 Timer expired
**Warunek:** `nextTriggerAt <= now()`
**Komponenty:** Show.vue, TimerSection, ConfirmableButton
**Efekt:**
- Reset button: disabled
- Tooltip: "Cannot reset - message will be sent shortly"
- TimerSection: amber background, warning text
- StatusBadge: "Pending Delivery" (amber)

### 9.2 Status completed
**Warunek:** `status === 'completed'`
**Komponenty:** Show.vue, TimerSection
**Efekt:**
- Reset button: ukryty (nie ma sensu resetować zakończonego)
- TimerSection: "Message sent on [deliveredAt]" (blue info box)
- StatusBadge: "Completed" (blue)

### 9.3 Status delivery_failed
**Warunek:** `deliveryStatus === 'failed'`
**Komponenty:** Show.vue, TimerSection
**Efekt:**
- Reset button: ukryty
- Error banner: "Email delivery failed. Reason: [error code]"
- Akcja sugerowana: "Edit custodianship to fix recipient email"
- StatusBadge: "Delivery Failed" (red)

### 9.4 Status draft
**Warunek:** `status === 'draft'`
**Komponenty:** Show.vue, TimerSection
**Efekt:**
- Info banner: "This custodianship is a draft. Verify your email to activate it."
- Button "Activate" (jeśli email zweryfikowany) → zmień status na active
- Reset button: ukryty (draft nie ma timera)
- TimerSection: "Timer inactive" (gray)

### 9.5 User ownership
**Warunek:** `user.id === custodianship.userId`
**Komponenty:** Backend policy
**Efekt:**
- Jeśli NIE: HTTP 403 Forbidden
- Frontend: assume ownership (Inertia tylko dla ownera zwraca dane)

### 9.6 Delete confirmation validation
**Warunek:** checkbox checked + typed name matches
**Komponenty:** DeleteCustodianshipModal
**Efekt:**
- Delete button disabled do momentu spełnienia
- Visual feedback: input border green gdy match

## 10. Obsługa błędów

### 10.1 Reset timer failure
**Scenariusz:** POST `/custodianships/{uuid}/reset` error

**Obsługa:**
- Rollback optimistic update
- Set `isResetting=false`
- Toast error: "Failed to reset timer. Please try again."

### 10.2 Download attachment failure
**Scenariusz:** GET `/custodianships/{uuid}/attachments/{id}/download` error (404, 403, 500)

**Obsługa:**
- 404: Toast "Attachment not found"
- 403: Toast "Access denied"
- 500: Toast "Failed to download attachment. Please try again."

### 10.3 Delete custodianship failure
**Scenariusz:** DELETE `/custodianships/{uuid}` error

**Obsługa:**
- Set `isDeleting=false`
- Close modal
- Toast error: "Failed to delete custodianship. Please try again."
- Pozostań na stronie Show

### 10.4 Unauthorized access (403)
**Scenariusz:** User próbuje otworzyć cudze powiernictwo

**Obsługa:**
- Backend policy: return 403
- Frontend: redirect do `/custodianships` (Index)
- Toast: "You don't have permission to access this custodianship"

### 10.5 Not found (404)
**Scenariusz:** UUID nie istnieje lub powiernictwo usunięte

**Obsługa:**
- Backend: return 404
- Frontend: redirect do `/custodianships` (Index)
- Toast: "Custodianship not found"

### 10.6 Timer wygasa podczas viewing
**Scenariusz:** Countdown dochodzi do 0 podczas gdy user patrzy

**Obsługa:**
- TimerProgressBar emit `expired`
- Show.vue update UI:
  - Disable Reset button
  - TimerSection amber background
  - StatusBadge → "Pending Delivery"
- Brak auto-refresh
- Brak intrusywnych toastów

### 10.7 Invalid attachment download (rate limiting)
**Scenariusz:** User klika Download wielokrotnie (rate limit: 10/h per IP)

**Obsługa:**
- Backend: return 429 Too Many Requests
- Frontend: Toast error: "Too many download attempts. Please try again in 1 hour."
- Disable Download button z tooltipem

## 11. Kroki implementacji

### Krok 1: Przygotowanie typów i mock data (20 min)
- Dodaj do `types/models.ts`: CustodianshipDetailViewModel, ResetLogViewModel
- Extend `data/mockCustodianships.ts` z mockCustodianshipDetail, mockResetHistory
- Dodaj mockShowPageProps

### Krok 2: Composable useCustodianshipActions (1h, opcjonalnie)
- Implementuj w `composables/useCustodianshipActions.ts`
- Centralizacja handleReset, handleDelete
- Optimistic UI dla reset
- Return loading states

### Krok 3: Komponent TimerSection (45 min)
- Utwórz `Components/TimerSection.vue`
- TimerProgressBar + timer info text
- Special states (draft, expired, completed)
- Conditional rendering based on status

### Krok 4: Komponent MessageContentViewer (20 min)
- Utwórz `Components/MessageContentViewer.vue`
- V-html z sanityzacją (DOMPurify)
- Fallback dla null content
- Styling dla rendered markdown (Tailwind Typography)

### Krok 5: Komponent AttachmentItem (30 min)
- Utwórz `Components/AttachmentItem.vue`
- Ikona typu pliku (conditional, based on mimeType)
- Nazwa + rozmiar (formatowanie)
- Download button/link

### Krok 6: Komponent AttachmentList (read-only) (30 min)
- Extend lub utwórz `Components/AttachmentList.vue`
- Read-only mode: lista AttachmentItem
- Empty state "(No attachments)"
- Download URL construction

### Krok 7: Komponent ResetHistoryTable (45 min, opcjonalnie w MVP)
- Utwórz `Components/ResetHistoryTable.vue`
- Shadcn Table
- Kolumny: Timestamp (relative + tooltip), Method, IP
- Empty state
- Sortowanie DESC (newest first)

### Krok 8: Komponent DeleteCustodianshipModal (1h)
- Utwórz `Components/DeleteCustodianshipModal.vue`
- Shadcn Dialog
- Warning text + checkbox + text input
- Walidacja: enable Delete button
- Case-insensitive name comparison
- Emits confirm

### Krok 9: Komponent DangerZone (20 min)
- Utwórz `Components/DangerZone.vue`
- Red border card
- Warning text
- Delete button (emits delete event)

### Krok 10: Główna strona Show.vue (2.5h)
- Utwórz `Pages/Custodianships/Show.vue`
- AuthenticatedLayout + Breadcrumbs
- PageHeader (nazwa, StatusBadge, action buttons)
- Wszystkie sekcje (Timer, Details, Recipients, Attachments, Reset History, Danger Zone)
- Handlers (reset, delete, download)
- Optimistic UI dla reset
- DeleteCustodianshipModal integration
- Conditional rendering based on status

### Krok 11: Backend - show endpoint (30 min)
- Route: GET `/custodianships/{custodianship:uuid}`
- Controller: CustodianshipController@show
- Route model binding by UUID
- Authorization policy (tylko owner)
- Eager load: recipients, message, media, user
- Optional: reset history (query z tabeli resets)
- Map do CustodianshipDetailViewModel
- Inertia render

### Krok 12: Backend - download attachment endpoint (1h)
- Route: GET `/custodianships/{custodianship:uuid}/attachments/{media}/download`
- Controller: AttachmentController@download
- Authorization (user owns custodianship)
- Rate limiting (10 per hour per IP)
- Log download (tabela downloads)
- Fetch file z S3 (presigned URL lub stream)
- Return streamed file (Content-Disposition: attachment)

### Krok 13: Backend - delete endpoint (45 min)
- Route: DELETE `/custodianships/{custodianship:uuid}`
- Controller: CustodianshipController@destroy
- Authorization policy
- Delete attachments z S3 (loop przez media)
- Delete custodianship record (cascade deletes: message, recipients, resets, deliveries, downloads)
- Redirect do Index z success message

### Krok 14: Backend - activate draft endpoint (30 min, opcjonalnie)
- Route: POST `/custodianships/{custodianship:uuid}/activate`
- Controller: CustodianshipController@activate
- Walidacja: status=draft, user.emailVerified=true
- Update: status='active', lastResetAt=now, nextTriggerAt=now+interval, activatedAt=now
- Return success

### Krok 15: Testy (1.5h)
- Test wyświetlenia wszystkich sekcji
- Test różnych statusów (draft, active, expired, completed, failed)
- Test Reset timer (optimistic UI + POST)
- Test Download attachment
- Test Delete custodianship (modal validation + DELETE)
- Test authorization (403 dla cudzego powiernictwa)
- Test responsywności

### Krok 16: Dokumentacja i cleanup (20 min)
- Remove console.logs
- Format code
- Update README
- Git commit

**Całkowity szacowany czas:** 12-16 godzin

# Plan implementacji widoku Edit Custodianship

## 1. Przegląd

Widok Edit Custodianship umożliwia użytkownikowi edycję istniejącego powiernictwa. Jest bardzo podobny do widoku Create, ale z preładowanymi danymi. Kluczowa różnica: edycja **NIE resetuje automatycznie timera** - użytkownik decyduje czy chce zresetować po zapisaniu zmian.

### Kluczowe cele:
- Edycja wszystkich pól powiernictwa
- Zachowanie istniejących załączników + możliwość dodania/usunięcia
- Brak automatycznego resetu timera po zapisaniu (zgodnie z decyzją planistyczną)
- Unsaved changes warning przy próbie opuszczenia
- Walidacja limitów freemium (jak w Create)

## 2. Routing widoku

**Ścieżka:** `/custodianships/{uuid}/edit`
**Middleware:** `auth`
**HTTP Method:** GET (wyświetlenie formularza), PUT/PATCH (zapisanie)
**Redirect po zapisaniu:** `/custodianships/{uuid}` (Show page)

## 3. Struktura komponentów

```
Edit.vue (główna strona)
├── AuthenticatedLayout
│   └── Breadcrumbs
├── PageHeader
│   └── Tytuł "Edit Custodianship"
└── Form (identyczny jak Create)
    ├── FormField (Name)
    │   └── TextInput (pre-filled)
    ├── FormField (Message Content)
    │   └── RichTextEditor (pre-filled)
    ├── FormField (Interval)
    │   └── Select (pre-selected)
    ├── FormField (Recipients)
    │   └── RecipientList (pre-filled, editable)
    ├── FormField (Attachments)
    │   └── AttachmentUploader
    │       ├── ExistingAttachments (z delete option)
    │       └── NewAttachments (upload do temp)
    └── FormActions
        ├── Button (Cancel - with dirty check)
        └── Button (Save - primary)
```

## 4. Szczegóły komponentów

### 4.1 Edit.vue (główna strona)

**Przeznaczenie:**
Główny kontener formularza edycji. Prawie identyczny z Create.vue, ale z pre-filled data.

**Główne elementy:**
- AuthenticatedLayout
- Breadcrumbs: Home > Custodianships > [Name] > Edit
- Header z tytułem "Edit Custodianship"
- Formularz z wszystkimi polami (pre-filled)
- Action buttons (Cancel, Save)

**Obsługiwane interakcje:**
- Edycja wszystkich pól
- Upload nowych załączników
- Usunięcie istniejących załączników
- Dodanie/usunięcie odbiorców
- Zapisanie zmian (PUT)
- Anulowanie (Cancel z dirty check)

**Walidacja:**
- Identyczna jak w Create
- Dodatkowo: sprawdzenie czy interwał zmieniony (może wpłynąć na nextTriggerAt)

**Propsy (z Inertii):**
- `user: UserViewModel`
- `custodianship: CustodianshipDetailViewModel` (z wszystkimi danymi)
- `intervals: IntervalOption[]`

**Local State:**
- `form` (Inertia useForm) - pre-filled z custodianship data
- `uploadedNewAttachments: Ref<TempAttachment[]>` - nowe pliki w temp storage
- `existingAttachments: Ref<AttachmentViewModel[]>` - istniejące (z możliwością usunięcia)
- `attachmentsToDelete: Ref<number[]>` - IDs do usunięcia
- `isUploading: Ref<boolean>`
- `totalAttachmentSize: Ref<number>` - suma existing + new

### 4.2 ExistingAttachmentList.vue

**Przeznaczenie:**
Lista istniejących załączników z możliwością usunięcia (przed zapisem formularza).

**Główne elementy:**
- Lista ExistingAttachmentItem
- Każdy item: ikona, nazwa, rozmiar, delete button (X)

**Obsługiwane interakcje:**
- Usunięcie załącznika (mark for deletion, nie usuwa od razu z S3)

**Propsy:**
- `attachments: AttachmentViewModel[]`
- `deletedIds: number[]` (v-model, IDs zaznaczone do usunięcia)

**Emits:**
- `update:deletedIds(ids: number[])`

**Visual:**
- Attachments zaznaczone do usunięcia: opacity 50%, strike-through, czerwony border

### 4.3 ExistingAttachmentItem.vue

**Przeznaczenie:**
Pojedynczy istniejący załącznik z delete button.

**Główne elementy:**
- Ikona typu pliku
- Nazwa + rozmiar
- Delete button (X, red)

**Obsługiwane interakcje:**
- Kliknięcie Delete → dodaj ID do deletedIds (lub usuń jeśli już był)

**Propsy:**
- `attachment: AttachmentViewModel`
- `isMarkedForDeletion: boolean`

**Emits:**
- `toggleDelete(id: number)`

**Visual:**
- Normal: standard styling
- Marked for deletion: opacity-50, line-through, red border

## 5. Typy

### ViewModels

**EditCustodianshipFormData:**
```typescript
{
  name: string
  messageContent: string | null
  interval: string // ISO 8601
  recipients: string[] // emails
  newAttachments: string[] // temp attachment IDs (nowe)
  deleteAttachments: number[] // IDs istniejących do usunięcia
}
```

**PageProps (Inertia):**
```typescript
{
  user: UserViewModel
  custodianship: CustodianshipDetailViewModel
  intervals: IntervalOption[]
}
```

## 6. Zarządzanie stanem

### Global State (Inertia Props)
- `user` - dane użytkownika
- `custodianship` - szczegóły powiernictwa do edycji
- `intervals` - lista dostępnych interwałów

### Local State (Edit.vue)

**Inertia Form (pre-filled):**
```typescript
const form = useForm<EditCustodianshipFormData>({
  name: props.custodianship.name,
  messageContent: props.custodianship.messageContent,
  interval: props.custodianship.interval,
  recipients: props.custodianship.recipients.map(r => r.email),
  newAttachments: [], // nowe temp IDs
  deleteAttachments: [], // IDs do usunięcia
})
```

**Upload State:**
- `uploadedNewAttachments: Ref<TempAttachment[]>` - nowe pliki
- `existingAttachments: Ref<AttachmentViewModel[]>` - kopia z props (dla local mutations)
- `isUploading: Ref<boolean>`

**Computed:**
- `totalAttachmentSize: number` - suma (existing - deleted) + new
- `canAddAttachment: boolean` - totalSize < 10MB
- `canAddRecipient: boolean` - recipients.length < 2
- `isDirty: boolean` - czy formularz zmieniony
- `intervalChanged: boolean` - czy interval zmieniony (info dla usera o wpływie na timer)

### Custom Composables

**useFormDirtyCheck:**
- Reużyty z Create
- Porównanie initial props vs current form

**useAttachmentUpload:**
- Reużyty z Create
- Upload nowych plików do temp storage

## 7. Dane Testowe (Mock Data)

### Lokalizacja
Extend `resources/js/data/mockCustodianships.ts`

### Zawartość

**mockEditPageProps:**
```typescript
{
  user: mockUser,
  custodianship: mockCustodianshipDetail, // z Show
  intervals: mockIntervals,
}
```

## 8. Interakcje użytkownika

### 8.1 Edycja pól formularza
**Trigger:** User modyfikuje dowolne pole

**Flow:**
1. Zmiana w input → update form field
2. Set `isDirty = true` (dla unsaved warning)
3. Jeśli interval zmieniony: pokaż info message "Changing interval will recalculate next trigger date without resetting timer"

### 8.2 Dodanie nowego załącznika
**Trigger:** Drag & drop lub browse nowego pliku

**Flow:**
1. Sprawdź `totalAttachmentSize + newFileSize <= 10MB`
2. Jeśli NIE: error message
3. Jeśli TAK:
   - Upload do temp storage (jak w Create)
   - Dodaj do `uploadedNewAttachments`
   - Dodaj tempId do `form.newAttachments`

### 8.3 Usunięcie istniejącego załącznika
**Trigger:** Kliknięcie X przy istniejącym załączniku

**Flow:**
1. Dodaj ID do `form.deleteAttachments`
2. Visual: mark attachment (opacity, strike-through, red border)
3. Re-calculate `totalAttachmentSize` (exclude marked)
4. Attachment nie jest usuwany z S3 do momentu submit formularza

### 8.4 Cofnięcie usunięcia załącznika
**Trigger:** Ponowne kliknięcie X przy zaznaczonym załączniku (toggle)

**Flow:**
1. Usuń ID z `form.deleteAttachments`
2. Visual: przywróć normalny styling
3. Re-calculate `totalAttachmentSize`

### 8.5 Usunięcie nowego załącznika (nie zapisanego jeszcze)
**Trigger:** Kliknięcie X przy nowo uploadowanym

**Flow:**
1. Usuń z `uploadedNewAttachments`
2. Usuń tempId z `form.newAttachments`
3. Opcjonalnie: DELETE `/api/attachments/temp/{id}` (cleanup)
4. Re-calculate `totalAttachmentSize`

### 8.6 Zapisanie zmian
**Trigger:** Kliknięcie "Save"

**Flow:**
1. Walidacja frontend (basic)
2. Submit form:
   - `form.put(`/custodianships/{uuid}`, options)`
3. Backend:
   - Walidacja server-side
   - Update custodianship fields (name, interval, messageContent)
   - Jeśli interval zmieniony: recalculate nextTriggerAt = lastResetAt + new interval
   - Update message (re-encrypt z nową treścią)
   - Update recipients:
     - Delete old recipients nie na nowej liście
     - Create new recipients
   - Handle attachments:
     - Delete z S3: attachments w deleteAttachments array
     - Assign temp attachments: newAttachments → permanent storage
   - **NIE resetuj timera** (lastResetAt bez zmian)
4. Redirect: `/custodianships/{uuid}` (Show)
5. Toast: "Custodianship updated"

### 8.7 Anulowanie edycji
**Trigger:** Kliknięcie "Cancel"

**Flow:**
1. Sprawdź `isDirty`
2. Jeśli NIE: redirect do `/custodianships/{uuid}` (Show)
3. Jeśli TAK:
   - Confirmation dialog: "Discard changes?"
   - Cancel → zostań na Edit
   - Confirm → redirect do Show

### 8.8 Próba opuszczenia z unsaved changes
**Trigger:** Browser back, nawigacja, close tab

**Flow:**
- Identyczny jak w Create
- useFormDirtyCheck → browser confirmation

## 9. Warunki i walidacja

### 9.1 Wszystkie pola formularza
**Warunki:** Identyczne jak w Create
- Nazwa: required, max 255
- Recipients: min 1, max 2, valid emails
- Attachments: total <= 10MB
- Interval: required, jedna z opcji

### 9.2 Zmiana interwału
**Warunek:** `form.interval !== custodianship.interval`
**Komponenty:** Edit.vue
**Efekt:**
- Info message: "Changing interval will recalculate next trigger date. Current remaining time will be preserved."
- Wyjaśnienie: nextTriggerAt = lastResetAt + new_interval (nie reset timera)
- Jeśli nowy nextTriggerAt < now: warning "New interval is shorter than time since last reset. Timer will expire immediately."

### 9.3 Usunięcie wszystkich załączników
**Warunek:** `existingAttachments.length - deleteAttachments.length + newAttachments.length === 0`
**Komponenty:** Edit.vue
**Efekt:**
- Dozwolone (attachments są opcjonalne)
- Brak error message

### 9.4 Usunięcie wszystkich odbiorców
**Warunek:** `recipients.length === 0`
**Komponenty:** RecipientList
**Efekt:**
- NIE dozwolone
- Min 1 odbiorca required
- Ostatni recipient ma disabled delete button

### 9.5 Przekroczenie limitu załączników
**Warunek:** `totalSize > 10MB` (existing + new - deleted)
**Komponenty:** AttachmentUploader, StorageIndicator
**Efekt:**
- Error message: "Total attachment size cannot exceed 10MB"
- Prevent upload nowych plików
- Sugestia: "Delete existing attachments to free up space"

## 10. Obsługa błędów

### 10.1 Walidacja server-side failure
**Scenariusz:** Backend zwraca validation errors (422)

**Obsługa:**
- Identyczna jak w Create
- Inertia mapuje błędy do form.errors
- FormField wyświetla errors
- Scroll do pierwszego błędu

### 10.2 Upload nowego załącznika failure
**Scenariusz:** POST `/api/attachments/temp-upload` error

**Obsługa:**
- Identyczna jak w Create
- Toast error
- Plik nie dodany

### 10.3 Save failure (network, server error)
**Scenariusz:** PUT `/custodianships/{uuid}` error

**Obsługa:**
- Toast error: "Failed to update custodianship. Please try again."
- Formularz pozostaje wypełniony
- User może poprawić i spróbować ponownie

### 10.4 Concurrent modification
**Scenariusz:** Inne okno/urządzenie edytowało powiernictwo w międzyczasie

**Obsługa (post-MVP):**
- Backend: optimistic locking (updated_at check)
- Error: "Custodianship was modified by another session. Please refresh and try again."
- MVP: brak obsługi, last write wins

### 10.5 Attachment not found (do usunięcia)
**Scenariusz:** User zaznaczy załącznik do usunięcia, ale załącznik już nie istnieje w S3

**Obsługa:**
- Backend: ignore 404 przy delete z S3
- Continue z update
- Log warning
- Brak error dla usera

### 10.6 Interval zmieniony na krótszy → immediate expiry
**Scenariusz:** New interval < time since last reset

**Obsługa:**
- Frontend warning przed submitem:
  - "Warning: New interval (30 days) is shorter than time since last reset (85 days). Timer will expire immediately upon save."
  - Confirmation: "Continue anyway?" / "Cancel"
- User może:
  - Cancel → zmienić interval na dłuższy
  - Confirm → zapisać (timer wygaśnie natychmiast, cron job wyśle wiadomość)

## 11. Kroki implementacji

### Krok 1: Przygotowanie typów i mock data (15 min)
- Dodaj do `types/models.ts`: EditCustodianshipFormData
- Extend `data/mockCustodianships.ts` z mockEditPageProps

### Krok 2: Komponent ExistingAttachmentItem (30 min)
- Utwórz `Components/ExistingAttachmentItem.vue`
- Ikona + nazwa + rozmiar + delete button
- Toggle delete (emit)
- Visual states (normal vs marked for deletion)

### Krok 3: Komponent ExistingAttachmentList (45 min)
- Utwórz `Components/ExistingAttachmentList.vue`
- Lista ExistingAttachmentItem
- V-model dla deletedIds array
- Empty state jeśli wszystkie usunięte

### Krok 4: Extend AttachmentUploader dla Edit mode (1h)
- Modyfikuj `Components/AttachmentUploader.vue`
- Nowy prop: `existingAttachments?: AttachmentViewModel[]`
- Nowy prop: `deletedIds?: number[]` (v-model)
- Renderuj ExistingAttachmentList jeśli existing
- Renderuj upload zone dla nowych
- Total size calculation: existing + new - deleted

### Krok 5: Główna strona Edit.vue (2.5h)
- Utwórz `Pages/Custodianships/Edit.vue`
- Prawie identyczna z Create.vue, ale:
  - Pre-fill form z custodianship data
  - Recipients pre-filled
  - AttachmentUploader z existingAttachments
  - Info message jeśli interval zmieniony
  - Warning jeśli new interval < time since reset
- Submit: PUT zamiast POST
- Breadcrumbs: Home > Custodianships > [Name] > Edit

### Krok 6: Backend - edit endpoint (show form) (30 min)
- Route: GET `/custodianships/{custodianship:uuid}/edit`
- Controller: CustodianshipController@edit
- Authorization policy (tylko owner)
- Eager load: recipients, message, media
- Map do CustodianshipDetailViewModel
- Inertia render Edit page z intervals

### Krok 7: Backend - update endpoint (1.5h)
- Route: PUT/PATCH `/custodianships/{custodianship:uuid}`
- Controller: CustodianshipController@update
- Authorization policy
- Validation: wszystkie pola
- Update logic:
  - Update custodianship fields
  - Jeśli interval changed: recalculate nextTriggerAt (NIE reset lastResetAt)
  - Update message (re-encrypt)
  - Update recipients (delete old, create new)
  - Delete attachments z S3 (deleteAttachments array)
  - Assign new temp attachments → permanent
- Redirect do Show z success message

### Krok 8: Backend - interval change logic (30 min)
- W CustodianshipController@update
- Jeśli interval changed:
  ```php
  $newNextTriggerAt = $custodianship->last_reset_at->add(CarbonInterval::fromString($request->interval));

  if ($newNextTriggerAt->isPast()) {
    // Timer wygaśnie natychmiast
    $custodianship->next_trigger_at = now();
  } else {
    $custodianship->next_trigger_at = $newNextTriggerAt;
  }
  ```
- Brak zmiany lastResetAt

### Krok 9: Frontend - interval change warning (45 min)
- W Edit.vue
- Computed: `intervalChanged`, `newNextTriggerAt`
- Jeśli intervalChanged:
  - Info banner: "Changing interval will recalculate next trigger date"
- Jeśli newNextTriggerAt < now:
  - Warning banner: "Timer will expire immediately"
  - Confirmation dialog przed submitem

### Krok 10: Testy (1.5h)
- Test edycji wszystkich pól
- Test dodania nowych załączników
- Test usunięcia istniejących załączników
- Test zmiany interwału (warning, recalculation)
- Test walidacji (limits, required fields)
- Test unsaved changes warning
- Test save (PUT) + redirect do Show
- Test authorization (403)

### Krok 11: Dokumentacja i cleanup (20 min)
- Remove console.logs
- Format code
- Update README
- Git commit

**Całkowity szacowany czas:** 10-14 godzin

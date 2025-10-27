# Plan implementacji widoku Create Custodianship

## 1. Przegląd

Widok Create Custodianship umożliwia użytkownikowi utworzenie nowego powiernictwa. Jest to kluczowy widok w onboardingu - pierwszy kontakt użytkownika z funkcjonalnością aplikacji. Formularz jest single-page (wszystkie pola na jednej stronie) dla maksymalnej szybkości wypełnienia, zgodnie z KPI <5 minut od rejestracji do pierwszego aktywnego powiernictwa.

### Kluczowe cele:
- Szybkie i intuicyjne utworzenie powiernictwa
- Walidacja limitów freemium (max 2 odbiorców, max 10MB załączników)
- Automatyczny zapis jako draft (wszystkie nowo utworzone powiernictwa)
- Dwuetapowy upload załączników (temp storage → przypisanie do powiernictwa)
- Unsaved changes warning przy próbie opuszczenia strony

## 2. Routing widoku

**Ścieżka:** `/custodianships/create`
**Middleware:** `auth`
**HTTP Method:** GET (wyświetlenie formularza), POST (zapisanie)
**Redirect po zapisaniu:** `/custodianships/{uuid}` (Show page)

## 3. Struktura komponentów

```
Create.vue (główna strona)
├── AuthenticatedLayout
│   └── Breadcrumbs
├── PageHeader
│   └── Tytuł "Create New Custodianship"
└── Form
    ├── FormField (Name)
    │   └── TextInput
    ├── FormField (Message Content)
    │   └── RichTextEditor (Tiptap)
    ├── FormField (Interval)
    │   └── Select (predefiniowane opcje)
    ├── FormField (Recipients)
    │   └── RecipientList (editable mode)
    │       └── RecipientInput (wiele, max 2)
    │           ├── TextInput (email)
    │           └── Button (remove)
    │       └── Button (Add recipient, max 2)
    ├── FormField (Attachments)
    │   └── AttachmentUploader
    │       ├── DropZone (drag & drop)
    │       ├── FileInput (browse)
    │       ├── ProgressBar (per file)
    │       ├── AttachmentList (uploaded)
    │       └── StorageIndicator (X MB / 10 MB)
    └── FormActions
        ├── Button (Cancel - with dirty check)
        └── Button (Save - primary)
```

## 4. Szczegóły komponentów

### 4.1 Create.vue (główna strona)

**Przeznaczenie:**
Główny kontener formularza tworzenia powiernictwa. Zarządza stanem formularza, walidacją, uploadem załączników i zapisem.

**Główne elementy:**
- AuthenticatedLayout
- Breadcrumbs: Home > Custodianships > New Custodianship
- Header z tytułem
- Formularz ze wszystkimi polami
- Action buttons (Cancel, Save)

**Obsługiwane interakcje:**
- Wypełnienie formularza (wszystkie pola)
- Upload załączników (auto-upload do temp storage)
- Dodanie/usunięcie odbiorców (max 2)
- Zapisanie formularza (POST)
- Anulowanie (Cancel z dirty check)

**Walidacja:**
- Nazwa: required, max 255 znaków
- Odbiorcy: minimum 1, max 2, valid email format
- Załączniki: max 10MB total
- Interwał: required, jedna z predefiniowanych opcji
- Message content: opcjonalne

**Propsy (z Inertii):**
- `user: UserViewModel`
- `intervals: IntervalOption[]` (predefiniowane opcje interwałów)
- Brak innych props (nowe powiernictwo)

**Local State:**
- `form` (Inertia useForm) ze wszystkimi polami formularza
- `uploadedAttachments: Ref<TempAttachment[]>` - pliki w temp storage
- `isUploading: Ref<boolean>` - czy upload w toku
- `totalAttachmentSize: Ref<number>` - suma rozmiarów załączników

### 4.2 RichTextEditor.vue

**Przeznaczenie:**
Edytor WYSIWYG dla treści wiadomości, oparty na Tiptap z ograniczonym toolbarem.

**Główne elementy:**
- Toolbar: Bold, Italic, Bulleted List, Numbered List, Link
- Editor area (contenteditable)
- Character/word counter (opcjonalnie)

**Obsługiwane interakcje:**
- Formatowanie tekstu (toolbar buttons)
- Paste (sanityzacja HTML)
- Link insertion (modal z URL input)

**Walidacja:**
- Output sanitization (backend)
- Brak user-injected HTML/scripts

**Propsy:**
- `modelValue: string` (v-model, markdown)
- `placeholder?: string`
- `disabled?: boolean`

**Emits:**
- `update:modelValue(value: string)`

**Używa:**
- Tiptap library z extensions: StarterKit (limited), Link

### 4.3 RecipientList.vue (editable mode)

**Przeznaczenie:**
Lista odbiorców w trybie edycji, umożliwiająca dodawanie/usuwanie emaili z walidacją limitu.

**Główne elementy:**
- Lista RecipientInput (dynamic rows)
- Przycisk "+ Add recipient" (disabled gdy 2/2)
- Komunikat limitu (tooltip na disabled button)

**Obsługiwane interakcje:**
- Dodanie odbiorcy (+ button, max 2)
- Usunięcie odbiorcy (X button, min 1)
- Edycja email (input change)

**Walidacja:**
- Email format (HTML5 email input)
- Unique emails (nie może być duplikatów)
- Min 1, max 2 odbiorców

**Propsy:**
- `modelValue: string[]` (v-model, lista emaili)
- `maxRecipients: number` (default 2)
- `readonly: boolean` (default false)

**Emits:**
- `update:modelValue(emails: string[])`

### 4.4 RecipientInput.vue

**Przeznaczenie:**
Pojedynczy wiersz dla email odbiorcy z przyciskiem usunięcia.

**Główne elementy:**
- TextInput (type="email")
- Button (remove - ikona X)

**Obsługiwane interakcje:**
- Wpisanie/zmiana email
- Usunięcie wiersza (X button)

**Walidacja:**
- Email format (HTML5)

**Propsy:**
- `modelValue: string` (v-model, email)
- `canRemove: boolean` (czy można usunąć - false jeśli last)

**Emits:**
- `update:modelValue(email: string)`
- `remove`

### 4.5 AttachmentUploader.vue

**Przeznaczenie:**
Komponent do uploadu załączników z drag & drop, progress tracking i storage indicator.

**Główne elementy:**
- DropZone (drag & drop area)
- File input (browse button, hidden)
- Lista uploadowanych plików z progress bars
- Storage indicator: "X MB / 10 MB used"
- Delete buttons per file

**Obsługiwane interakcje:**
- Drag & drop plików
- Browse i wybór plików (input)
- Auto-upload do temp storage (POST `/api/attachments/temp-upload`)
- Usunięcie pliku (przed zapisem formularza)
- Progress tracking per file

**Walidacja:**
- Frontend: suma rozmiarów <= 10MB przed uploadem
- Backend: enforce 10MB limit, file type check
- Komunikat błędu przy przekroczeniu limitu

**Propsy:**
- `modelValue: TempAttachment[]` (v-model, lista uploadowanych)
- `maxSize: number` (default 10485760 - 10MB)

**Emits:**
- `update:modelValue(attachments: TempAttachment[])`

**Local State:**
- `uploadProgress: Record<string, number>` - progress per file (0-100)
- `isDragging: boolean` - czy drag over

### 4.6 StorageIndicator.vue

**Przeznaczenie:**
Wizualizacja zapełnienia limitu załączników (X MB / 10 MB).

**Główne elementy:**
- Progress bar (Shadcn Progress)
- Tekst: "X MB / 10 MB used"
- Color coding: green (<7MB), yellow (7-9MB), red (>9MB)

**Propsy:**
- `usedSize: number` (bytes)
- `maxSize: number` (bytes, default 10MB)

**Computed:**
- `percentage: number` - (usedSize / maxSize) * 100
- `colorClass: string` - na podstawie percentage
- `formattedUsed: string` - formatowanie bytes → MB
- `formattedMax: string`

### 4.7 FormField.vue

**Przeznaczenie:**
Wrapper dla pól formularza z automatycznym error handling.

**Główne elementy:**
- Label (z required indicator jeśli required)
- Slot dla input component
- Error message (z form.errors, red text)
- Helper text (opcjonalnie, gray text)

**Propsy:**
- `label: string`
- `name: string` (dla error mapping)
- `error?: string` (z form.errors[name])
- `required?: boolean`
- `helperText?: string`

**Slots:**
- `default` - input component

## 5. Typy

### ViewModels

**IntervalOption:**
```typescript
{
  value: string // ISO 8601 duration, np. "P30D"
  label: string // display, np. "1 month (30 days)"
  days: number // dla obliczeń
}
```

**TempAttachment:**
```typescript
{
  id: string // temporary ID (UUID)
  name: string // original filename
  size: number // bytes
  mimeType: string
  tempPath: string // path w temp storage
  uploadProgress: number // 0-100
}
```

**CreateCustodianshipFormData:**
```typescript
{
  name: string
  messageContent: string | null
  interval: string // ISO 8601
  recipients: string[] // emails
  attachments: string[] // temp attachment IDs
}
```

**PageProps (Inertia):**
```typescript
{
  user: UserViewModel
  intervals: IntervalOption[]
}
```

## 6. Zarządzanie stanem

### Global State (Inertia Props)
- `user` - dane użytkownika (dla sprawdzenia emailVerified)
- `intervals` - lista dostępnych interwałów

### Local State (Create.vue)

**Inertia Form:**
```typescript
const form = useForm<CreateCustodianshipFormData>({
  name: '',
  messageContent: null,
  interval: 'P90D', // default 90 dni
  recipients: [''], // 1 pusty email na start
  attachments: [], // temp attachment IDs
})
```

**Upload State:**
- `uploadedAttachments: Ref<TempAttachment[]>` - pliki w temp storage
- `isUploading: Ref<boolean>` - global upload status
- `uploadProgress: Ref<Record<string, number>>` - progress per file

**Computed:**
- `totalAttachmentSize: number` - suma rozmiarów uploadedAttachments
- `canAddRecipient: boolean` - czy można dodać kolejnego (< 2)
- `canAddAttachment: boolean` - czy można dodać plik (< 10MB)
- `isDirty: boolean` - czy formularz zmieniony (dla unsaved warning)

### Custom Composables

**useFormDirtyCheck:**
- Sprawdza czy formularz ma niewysłane zmiany
- Browser beforeunload event + Inertia onBefore hook
- Pokazuje confirmation dialog przy próbie opuszczenia
- Return: isDirty, confirmNavigation

**useAttachmentUpload:**
- Obsługa uploadu załączników do temp storage
- POST `/api/attachments/temp-upload` (multipart/form-data)
- Progress tracking (xhr.upload.onprogress)
- Return: uploadFile, removeFile, uploadProgress

## 7. Dane Testowe (Mock Data)

### Lokalizacja
`resources/js/data/mockIntervals.ts`

### Zawartość

**mockIntervals:**
```typescript
[
  { value: 'P30D', label: '1 month (30 days)', days: 30 },
  { value: 'P60D', label: '2 months (60 days)', days: 60 },
  { value: 'P90D', label: '3 months (90 days)', days: 90 },
  { value: 'P180D', label: '6 months (180 days)', days: 180 },
  { value: 'P365D', label: '1 year (365 days)', days: 365 },
]
```

**mockCreatePageProps:**
```typescript
{
  user: mockUser, // z mockCustodianships.ts
  intervals: mockIntervals,
}
```

### Użycie
- Development: import i użyj jako props
- Production: dane z kontrolera Laravel przez Inertia

## 8. Interakcje użytkownika

### 8.1 Wypełnienie formularza
**Trigger:** User wpisuje dane w pola

**Flow:**
1. User wypełnia nazwę (required)
2. User wypełnia treść wiadomości (opcjonalnie, rich text)
3. User wybiera interwał z dropdown (default: 90 dni)
4. User wpisuje email odbiorcy (min 1, max 2)
5. User uploaduje załączniki (opcjonalnie, max 10MB)

### 8.2 Dodanie odbiorcy
**Trigger:** Kliknięcie "+ Add recipient"

**Flow:**
1. Sprawdź `recipients.length < 2`
2. Jeśli TAK: dodaj pusty string do `form.recipients`
3. Jeśli NIE: button disabled, tooltip "Max 2 recipients in free plan"

### 8.3 Usunięcie odbiorcy
**Trigger:** Kliknięcie X przy odbiorcy

**Flow:**
1. Sprawdź `recipients.length > 1`
2. Jeśli TAK: usuń email z `form.recipients`
3. Jeśli NIE: button disabled (musi być min 1 odbiorca)

### 8.4 Upload załącznika
**Trigger:** Drag & drop lub wybór pliku (browse)

**Flow:**
1. Sprawdź `totalAttachmentSize + newFileSize <= 10MB`
2. Jeśli NIE: błąd "You've reached the 10MB attachment limit..."
3. Jeśli TAK:
   - Set `isUploading = true`
   - Utwórz temporary ID dla pliku
   - POST `/api/attachments/temp-upload` (FormData z plikiem)
   - Track progress w `uploadProgress[fileId]`
   - On success:
     - Dodaj do `uploadedAttachments` (z tempPath z response)
     - Dodaj tempId do `form.attachments`
     - Set `isUploading = false`
   - On error:
     - Toast error
     - Set `isUploading = false`

### 8.5 Usunięcie załącznika
**Trigger:** Kliknięcie X przy załączniku

**Flow:**
1. Usuń z `uploadedAttachments`
2. Usuń z `form.attachments`
3. Opcjonalnie: DELETE `/api/attachments/temp/{id}` (cleanup temp storage)
4. Re-calculate `totalAttachmentSize`

### 8.6 Zapisanie formularza
**Trigger:** Kliknięcie "Save"

**Flow:**
1. Walidacja frontend (basic):
   - Nazwa wypełniona
   - Min 1 odbiorca z valid email
   - Interwał wybrany
2. Submit form:
   - `form.post('/custodianships', options)`
3. Backend:
   - Walidacja server-side (wszystkie pola)
   - Utworzenie custodianship ze statusem 'draft'
   - Przypisanie temp attachments do custodianship (media-library)
   - Utworzenie message (encrypted)
   - Utworzenie recipients
   - Set activated_at=null, last_reset_at=null, next_trigger_at=null
4. Redirect: `/custodianships/{uuid}` (Show page)
5. Toast: "Custodianship created successfully"

### 8.7 Anulowanie (Cancel)
**Trigger:** Kliknięcie "Cancel"

**Flow:**
1. Sprawdź `isDirty` (czy formularz zmieniony)
2. Jeśli NIE: bezpośredni redirect do `/custodianships`
3. Jeśli TAK:
   - Pokaż confirmation dialog: "Discard changes?"
   - Cancel → zostań na stronie
   - Confirm → redirect do `/custodianships`

### 8.8 Próba opuszczenia strony (unsaved changes)
**Trigger:** User próbuje nawigować pryważ, zamknąć tab, etc.

**Flow:**
1. `useFormDirtyCheck` detektuje isDirty
2. Browser beforeunload event → native confirmation dialog
3. Inertia onBefore hook → Inertia confirmation dialog
4. User wybiera: Stay / Leave

## 9. Warunki i walidacja

### 9.1 Nazwa powiernictwa
**Warunek:** required, max 255 znaków
**Komponenty:** Create.vue (FormField + TextInput)
**Efekt:**
- Frontend: HTML5 required attribute
- Backend: validation rule `required|max:255`
- Error message: "The name field is required" / "The name may not be greater than 255 characters"

### 9.2 Limit odbiorców (max 2)
**Warunek:** `recipients.length <= 2`
**Komponenty:** RecipientList, RecipientInput
**Efekt:**
- Button "+ Add recipient" disabled gdy `length >= 2`
- Tooltip: "Max 2 recipients in free plan"
- Backend validation: `array|min:1|max:2`

### 9.3 Walidacja email odbiorców
**Warunek:** valid email format, unique
**Komponenty:** RecipientInput (TextInput type="email")
**Efekt:**
- HTML5 email validation (frontend)
- Backend validation: `required|email` per recipient
- Unique check: brak duplikatów w liście (frontend computed)

### 9.4 Limit załączników (10MB)
**Warunek:** `totalAttachmentSize <= 10MB`
**Komponenty:** AttachmentUploader, StorageIndicator
**Efekt:**
- Frontend: sprawdzenie przed uploadem
- Error message: "You've reached the 10MB attachment limit. Delete existing attachments or reduce file size."
- Backend validation: `max:10240` (KB) total
- DropZone disabled gdy limit osiągnięty

### 9.5 Interwał czasowy
**Warunek:** required, jedna z predefiniowanych opcji
**Komponenty:** Create.vue (Select)
**Efekt:**
- Frontend: Select z options z `intervals` prop
- Backend validation: `required|in:P30D,P60D,P90D,P180D,P365D`

### 9.6 Wszystkie powiernictwa tworzone jako draft
**Warunek:** Zawsze
**Komponenty:** Create.vue
**Efekt:**
- Informacyjny banner na górze formularza: "Custodianship will be created as a draft. You can activate it later from the custodianship details page."
- Po zapisaniu: status = 'draft', timer nieaktywny
- Redirect do Show page z informacją o draft status i możliwością aktywacji

## 10. Obsługa błędów

### 10.1 Walidacja server-side failure
**Scenariusz:** Backend zwraca validation errors (422)

**Obsługa:**
- Inertia automatycznie mapuje błędy do `form.errors`
- FormField wyświetla error message pod polem
- Scroll do pierwszego błędu (opcjonalnie)
- Focus na pierwszym błędnym polu

### 10.2 Upload załącznika failure
**Scenariusz:** POST `/api/attachments/temp-upload` error

**Obsługa:**
- Set `isUploading = false`
- Remove file z uploading queue
- Toast error: "Failed to upload {filename}. Please try again."
- Plik nie dodany do `uploadedAttachments`

### 10.3 Przekroczenie limitu załączników
**Scenariusz:** `totalSize + newFileSize > 10MB`

**Obsługa:**
- Prevent upload (nie wysyłaj POST)
- Error message w AttachmentUploader (red border + text)
- Komunikat: "You've reached the 10MB attachment limit. Current: X MB, trying to add: Y MB."

### 10.4 Network timeout przy zapisie
**Scenariusz:** POST `/custodianships` timeout

**Obsługa:**
- Inertia timeout (default 30s)
- Toast error: "Request timed out. Please check your connection and try again."
- Formularz pozostaje wypełniony (nie reset)
- User może spróbować ponownie

### 10.5 Duplikat email odbiorcy
**Scenariusz:** User wpisuje ten sam email 2x

**Obsługa:**
- Frontend computed check: filtruj duplikaty przed submitem
- Komunikat warning (nie error): "Duplicate recipient emails will be merged"
- Backend: unique constraint na (custodianship_id, email)

### 10.6 Browser crash podczas uploadu
**Scenariusz:** Browser zamknięty podczas uploadu pliku

**Obsługa:**
- Temp files pozostają na serwerze
- Backend cron job: cleanup temp files starszych niż 24h
- User musi re-upload przy następnej wizycie

### 10.7 Unsaved changes - user ignores warning
**Scenariusz:** User klika Leave w confirmation dialog

**Obsługa:**
- Nawigacja następuje (zmiany tracone)
- Temp attachments pozostają na serwerze (cleanup cron)
- Brak auto-save (MVP nie ma drafts auto-save)

## 11. Kroki implementacji

### Krok 1: Przygotowanie typów i mock data (20 min)
- Dodaj do `types/models.ts`: IntervalOption, TempAttachment, CreateCustodianshipFormData
- Utwórz `data/mockIntervals.ts` z opcjami interwałów
- Dodaj mockCreatePageProps

### Krok 2: Composable useFormDirtyCheck (45 min)
- Implementuj w `composables/useFormDirtyCheck.ts`
- Browser beforeunload event
- Inertia onBefore hook
- Return isDirty, confirmNavigation

### Krok 3: Composable useAttachmentUpload (1h)
- Implementuj w `composables/useAttachmentUpload.ts`
- POST `/api/attachments/temp-upload` z FormData
- XMLHttpRequest z progress tracking
- Return uploadFile, removeFile, uploadProgress

### Krok 4: Komponent FormField (30 min)
- Utwórz `Components/FormField.vue`
- Label + slot + error message + helper text
- Required indicator (*)
- ARIA attributes

### Krok 5: Komponent RichTextEditor (1.5h)
- Utwórz `Components/RichTextEditor.vue`
- Tiptap setup z limited extensions
- Toolbar: Bold, Italic, Lists, Link
- Markdown output
- Placeholder support

### Krok 6: Komponent RecipientInput (30 min)
- Utwórz `Components/RecipientInput.vue`
- TextInput type="email" + remove button
- Disabled state dla remove (jeśli canRemove=false)

### Krok 7: Komponent RecipientList (editable) (1h)
- Utwórz lub extend `Components/RecipientList.vue`
- Editable mode: dynamic RecipientInput rows
- Add/remove buttons z walidacją limitów
- V-model dla array of emails

### Krok 8: Komponent StorageIndicator (20 min)
- Utwórz `Components/StorageIndicator.vue`
- Progress bar + tekst "X MB / 10 MB"
- Color coding (green/yellow/red)
- Formatowanie bytes → MB

### Krok 9: Komponent AttachmentUploader (2h)
- Utwórz `Components/AttachmentUploader.vue`
- Drag & drop zone
- File input (hidden, triggered by button)
- Auto-upload do temp storage
- Progress bars per file
- Lista uploadowanych z delete buttons
- StorageIndicator integration
- Error handling

### Krok 10: Główna strona Create.vue (2.5h)
- Utwórz `Pages/Custodianships/Create.vue`
- AuthenticatedLayout + Breadcrumbs
- Inertia useForm setup
- Wszystkie FormFields (Name, Message, Interval, Recipients, Attachments)
- Draft banner (jeśli !emailVerified)
- Action buttons (Cancel z dirty check, Save)
- Submit handler
- useFormDirtyCheck integration

### Krok 11: Backend - temp upload endpoint (1h)
- API route: POST `/api/attachments/temp-upload`
- Controller: TempAttachmentController@upload
- Walidacja: max 10MB per file, allowed mime types
- Store w temp folder (storage/app/temp)
- Return: temp ID, filename, size, mime, tempPath

### Krok 12: Backend - create custodianship (1.5h)
- Route: POST `/custodianships`
- Controller: CustodianshipController@store
- Validation: wszystkie pola
- Create custodianship (status based on emailVerified)
- Create message (encrypted content)
- Create recipients
- Assign temp attachments (media-library: move temp → permanent)
- Set timer jeśli active
- Redirect z success message

### Krok 13: Backend - cleanup cron (30 min)
- Command: CleanupTempAttachments
- Schedule: daily
- Delete temp files > 24h old
- Log cleanup results

### Krok 14: Testy (1.5h)
- Test wypełnienia formularza (wszystkie pola)
- Test walidacji (required, email format, limits)
- Test upload załączników (success, error, limit)
- Test unsaved changes warning
- Test zapisania (draft vs active based on emailVerified)
- Test responsywności

### Krok 15: Dokumentacja i cleanup (20 min)
- Remove console.logs
- Format code
- Update README
- Git commit

**Całkowity szacowany czas:** 14-18 godzin

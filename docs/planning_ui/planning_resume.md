# UI Architecture Planning Summary - Just In Case MVP

## Decisions

### Round 1 Decisions

1. **Page Structure**: Use separate pages (Index, Show, Form) instead of modals for main navigation. Modals only for confirmations.
2. **Timer Display**: Dynamic countdown updated every second (not static snapshot).
3. **Form Layout**: Single-page form for custodianship creation/editing (not multi-step wizard).
4. **File Upload**: Two-stage upload process - files first uploaded to temp storage, then associated with custodianship via media-library upon form submission. Upload through Laravel backend as proxy.
5. **Reset Timer Confirmation**: Requires confirmation in some form (inline confirmation pattern).
6. **Component Structure**: Follow recommendation - organized by Pages/, Components/, Layouts/, Composables/.
7. **Status Colors**: Hardcoded in separate TypeScript config file (not in components).
8. **Form Validation**: Server-side only via Laravel Form Requests (no client-side validation).
9. **Empty State**: Single reusable EmptyState component with slots for CTA and icon.
10. **Status Filters**: Omit in MVP (users have max 3 custodianships).

### Round 2 Decisions

11. **Breadcrumbs Structure**: Home ’ Custodianships (index) ’ [Custodianship Name or "New Custodianship"] ’ "Edit" (if editing).
12. **Reset Confirmation Pattern**: Click-to-expand with Confirm/Cancel buttons, auto-collapse after 5 seconds.
13. **Timer Visibility Optimization**: Use Page Visibility API to pause countdown when tab inactive.
14. **Automatic Upload**: Files automatically upload in background after selection.
15. **Unsaved Changes Warning**: Browser prompt with dirty state tracking.
16. **Bulk Actions**: Only single actions in MVP (no bulk reset/delete).
17. **Show Page Layout**: All sections expanded on single page with clear card-based sections.
18. **Error Display**: Inline errors under form fields using Inertia `form.errors` (no top banner).
19. **Constants Config**: Single central file `resources/js/config/custodianship.ts`.
20. **EmptyState Implementation**: Slots for icon + props for title/description.

### Round 3 Decisions

21. **Navigation Elements**: Breadcrumbs (only previous elements clickable, current page as plain text).
22. **Timer Expiration Handling**: Visual change (progress ’ 0, color ’ red) + status badge to "Pending". No toast notifications. Reset button disabled when timer reaches zero.
23. **Save Button Logic**: Single "Save" button with intelligent backend logic (auto-draft if email unverified, active if verified).
24. **Rich Text Editor**: Controlled editor that converts to markdown (for non-technical users, not plain markdown).
25. **Recipient List UI**: Each recipient as row with email input + remove button.
26. **Drag & Drop Support**: Both drag & drop and file input browse.
27. **Attachment Display**: Name + size + file type icon (no image previews in MVP).
28. **Interval Selector**: Dropdown with predefined values with min/max range configured in Laravel config.
29. **Edit Button Prominence**: Primary button (not secondary).
30. **Reset History**: Collapsible section, initially collapsed.

### Round 4 Decisions

31. **Rich Text Editor Choice**: Tiptap (MIT license, free) with limited formatting options (Bold, Italic, Lists, Link). Outputs markdown.
32. **Interval Configuration**: Dropdown with human-readable labels ("1 month (30 days)"), values in days. Config in Laravel `config/custodianship.php` with allowed intervals, min/max.
33. **Inline Confirmation Consistency**: Same inline confirmation for Reset Timer on both Index and Show pages.
34. **Expired Status Display**: Badge "Pending" (orange/amber) + card amber background + disabled Reset button with tooltip.
35. **Form Integration**: Use Inertia `useForm()` with explicit `:error="form.errors.fieldname"` binding.
36. **Index Card Content**: Metadata only (name, status, timer, recipients, actions) - no message preview.
37. **Responsive Layout**: Always stack layout (single column) for simplicity.
38. **Navigation Menu**: Top navbar with hamburger menu on mobile.
39. **Post-Create Redirect**: Redirect to Show page of newly created custodianship.
40. **Placeholder Examples**: Use placeholders with examples in form fields.

### Round 5 Decisions

41. **MVP Language**: English only (no Polish pluralization).
42. **Reset Feedback**: Optimistic update with error toast only (no success toast).
43. **Attachment Limit Display**: Dynamic progress "X MB / 10 MB used" with validation error if exceeded.
44. **Recipient Metadata**: Email + date added (no verification status).
45. **Delete Confirmation**: Modal with checkbox + name input confirmation.
46. **No Status Filters**: Removed from MVP scope (max 3 custodianships).

### Round 6 Decisions

47. **Attachment Progress Display**: Dynamic "X MB / 10 MB used" with color-coded progress bar (green <7MB, yellow 7-9MB, red >9MB).
48. **Date Format**: Relative time with tooltip showing full date (using vue-tippy).
49. **Case Sensitivity**: Case-insensitive matching for delete confirmation.
50. **Edit Navigation**: Separate Edit page (not inline editing).
51. **Active Page Indicator**: Navigation links highlight active page.
52. **Cancel Button**: Both "Cancel" button (with dirty check) and breadcrumb navigation.
53. **Timer Tooltip**: Static text below bar + tooltip on hover with exact timestamp.
54. **Reset Animation**: 300ms transition animation for progress bar reset.
55. **File Size Format**: Dynamic formatting with 2 decimal places when needed.
56. **Deactivate Feature**: Post-MVP (not in initial release).

## Matched Recommendations

### Core Architecture

1. **Component Structure**
   - `resources/js/Pages/` - Inertia views (Dashboard, Custodianship/Index, Custodianship/Show, Custodianship/Create, Custodianship/Edit)
   - `resources/js/Components/` - Reusable components (CustodianshipCard, TimerProgressBar, RecipientList, AttachmentUploader, EmptyState)
   - `resources/js/Layouts/` - Layout wrappers (AuthenticatedLayout with navbar)
   - `resources/js/Composables/` - Shared logic (useTimerCountdown, useFileUpload, useFormDirtyCheck)
   - `resources/js/Components/ui/` - Shadcn-vue components (Button, Dialog, Toast, Tooltip, Tabs)

2. **Form Components**
   - Separate components: `TextInput.vue`, `TextArea.vue`, `Select.vue`
   - Each with props: `label`, `name`, `modelValue`, `error`, `placeholder`
   - Integration with Inertia `useForm()` for automatic error handling
   - Example: `<TextInput label="Name" v-model="form.name" :error="form.errors.name" />`

3. **Configuration Files**
   - `resources/js/config/custodianship.ts` - Central constants:
     ```typescript
     export const TIMER_THRESHOLDS = {
       SAFE_DAYS: 30,
       WARNING_DAYS: 7
     }
     export const STATUS_COLORS = {
       safe: 'green',
       warning: 'yellow',
       danger: 'red'
     }
     ```
   - Laravel: `config/custodianship.php` - Interval config with allowed values, min/max ranges

### Key Components

4. **TimerProgressBar Component**
   - Dynamic countdown updated every second via `setInterval`
   - Page Visibility API integration (pause when tab inactive, resync on return)
   - Composable `useTimerCountdown` with `document.visibilityState`
   - Color coding: green (>30 days), yellow (7-30 days), red (<7 days)
   - Text: "X days remaining of Y"
   - Tooltip on hover: exact expiration timestamp
   - 300ms transition animation on reset
   - Disabled state when timer expired with tooltip message

5. **ConfirmableButton Component**
   - Click-to-expand pattern: button ’ mini-toolbar with Confirm/Cancel
   - Auto-collapse after 5 seconds or click outside
   - Reusable on both Index and Show pages
   - Emits events for parent handling
   - Optimistic update on confirm + Inertia reload in background
   - Error toast on failure, no toast on success

6. **AttachmentUploader Component**
   - Drag & drop + file input browse support
   - Two-stage upload: temp storage ’ associate on save
   - Individual progress bar per file
   - Queue management for multiple uploads
   - Dynamic limit display: "X MB / 10 MB used"
   - Color-coded progress: green (<7MB), yellow (7-9MB), red (>9MB)
   - Validation error if limit exceeded
   - File list display: icon + name + size + remove button
   - No image previews in MVP

7. **RichTextEditor Component**
   - Tiptap with limited toolbar: Bold, Italic, Bulleted List, Numbered List, Link
   - Outputs markdown
   - Props: `modelValue` for v-model support
   - TypeScript integration

8. **RecipientList Component**
   - Each recipient as row: email input + remove icon button
   - "+ Add Recipient" button (disabled at 2/2)
   - Display format: email + relative date ("Added 2 days ago")
   - Tooltip on date hover showing full timestamp (vue-tippy)
   - Max 2 recipients enforced

9. **EmptyState Component**
   ```vue
   <template>
     <div class="empty-state">
       <slot name="icon"><!-- default icon --></slot>
       <h3>{{ title }}</h3>
       <p>{{ description }}</p>
       <slot name="action"><!-- CTA button --></slot>
     </div>
   </template>
   ```
   - Props: `title`, `description`
   - Slots for icon and action button

10. **Breadcrumbs Component**
    - Props: `items: Array<{label: string, href?: string}>`
    - Structure: Home ’ Custodianships ’ [Name/New] ’ Edit
    - Only previous elements clickable, current as plain text (gray-500)

### Page Layouts & Flows

11. **Custodianship Index Page**
    - Stack layout (single column) for all screen sizes
    - Card per custodianship showing:
      - Name (h3)
      - Status badge (color-coded)
      - Timer progress bar with text
      - Recipients list (email + date)
      - Actions: Reset (confirmable), Edit, Delete
    - EmptyState when no custodianships
    - No status filters in MVP (max 3 items)
    - "New Custodianship" button in header

12. **Custodianship Show Page**
    - All sections expanded on single page (white cards with margins)
    - Structure:
      - Header: name + status badge
      - Timer Progress section
      - Details: message content, interval
      - Recipients: list with email + date added
      - Attachments: list with download links
      - Reset History: collapsible section (initially collapsed)
      - Danger Zone: Delete button with modal confirmation
    - Top actions: Reset Timer (primary), Edit (primary)
    - Tooltips for additional context

13. **Custodianship Create/Edit Page**
    - Single-page form (not wizard)
    - Fields: Name, Message (rich text), Interval (dropdown), Recipients, Attachments
    - Placeholders with examples ("e.g., Bank account passwords")
    - Single "Save" button (intelligent logic: draft if unverified email, active if verified)
    - "Cancel" button with dirty state check
    - Breadcrumb navigation
    - Browser prompt on navigate away with unsaved changes
    - Inline validation errors under fields
    - Post-save redirect to Show page

14. **Delete Confirmation Modal**
    - Client-side Vue modal (Shadcn Dialog)
    - Warning text about irreversible action
    - Checkbox: "I understand the consequences"
    - Text input: "Type custodianship name to confirm" (case-insensitive)
    - Buttons: Cancel (secondary) + Delete Permanently (danger primary)
    - On confirm: `router.delete()` to backend

### Navigation & Layout

15. **AuthenticatedLayout**
    - Top navbar with hamburger on mobile
    - Desktop: horizontal links (Dashboard, Custodianships, Profile)
    - Mobile (<768px): hamburger icon ’ slide-in overlay menu
    - Active page indicator: `bg-gray-100 text-primary` for current route
    - Breadcrumbs in content area

16. **Interval Selector**
    - Dropdown with human-readable labels:
      - "1 month (30 days)"
      - "2 months (60 days)"
      - "3 months (90 days)"
      - "6 months (180 days)"
      - "1 year (365 days)"
    - Values: days (30, 60, 90, 180, 365)
    - Config from Laravel backend via Inertia shared data
    - Min/max validation in `config/custodianship.php`

### State Management & Data Flow

17. **Form State with Inertia useForm()**
    - All forms use Inertia `useForm()` helper
    - Automatic error binding: `form.errors.fieldname`
    - Dirty state tracking via `useFormDirtyCheck` composable
    - Optimistic updates for non-destructive actions (timer reset)
    - `preserveScroll: true` for partial updates

18. **Timer State Management**
    - Backend passes `next_trigger_at`, `interval_days`, `last_reset_at`
    - Frontend computes remaining time in `useTimerCountdown` composable
    - `setInterval` for real-time countdown
    - Page Visibility API for performance optimization
    - Computed properties for color coding and status

19. **File Upload State**
    - Component tracks array: `uploads: [{name, progress, status, size}]`
    - Total size computed from array
    - Individual progress tracking per file
    - Temp storage on upload, association on form save
    - Backend validation for 10MB total limit

### Utility Functions & Helpers

20. **Date Formatting**
    - Library: `dayjs` with `relativeTime` plugin
    - Format: `dayjs(date).fromNow()` for relative
    - Tooltip: full date format on hover
    - Integration with vue-tippy for tooltips

21. **File Size Formatting**
    ```typescript
    formatFileSize(bytes: number): string {
      if (bytes < 1024) return bytes + ' B'
      if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
      return (bytes / 1048576).toFixed(2) + ' MB'
    }
    ```

22. **Status Color Mapping**
    - Function computing color based on days remaining
    - References constants from `custodianship.ts` config
    - Returns Tailwind color classes

### UX Patterns

23. **Feedback & Notifications**
    - Success actions: optimistic update + animation (no toast)
    - Error actions: toast notification with error message
    - Animations: 300ms transitions for smooth feedback
    - Tooltips for additional context (vue-tippy)

24. **Danger Zone Pattern**
    - Destructive actions separated at bottom of page
    - Red "Delete" button with prominent warning
    - Multi-step confirmation (checkbox + text input)
    - Clear consequence messaging

25. **Responsive Design**
    - Mobile-first approach with Tailwind
    - Stack layout for simplicity
    - Hamburger menu on mobile
    - Touch-friendly button sizes
    - No complex grid layouts in MVP

## UI Architecture Planning Summary

### Overview

The UI architecture for Just In Case MVP is built on **Vue 3 + Inertia.js + TypeScript + Tailwind CSS 4 + Shadcn-vue**, creating a modern, type-safe SPA without the complexity of a full REST API. The architecture emphasizes simplicity, clear user flows, and robust state management while maintaining the flexibility to scale post-MVP.

### Core Architectural Principles

#### 1. Page-Based Navigation (Not Modal-Heavy)
- **Separate pages** for Index, Show, Create, Edit (no modals for primary navigation)
- **Modals only** for confirmations (delete, discard changes)
- **Breadcrumb navigation** throughout for context and easy back-navigation
- **Inertia routing** for SPA-like experience with server-side rendering benefits

#### 2. Component Organization

```
resources/js/
   Pages/
      Dashboard.vue
      Custodianship/
          Index.vue          # List view
          Show.vue           # Detail view
          Create.vue         # Creation form
          Edit.vue           # Edit form
   Components/
      ui/                    # Shadcn-vue components
         Button.vue
         Dialog.vue
         Toast.vue
         Tooltip.vue
         ...
      CustodianshipCard.vue  # Index list item
      TimerProgressBar.vue   # Real-time countdown
      RecipientList.vue      # Recipient management
      AttachmentUploader.vue # File upload with progress
      RichTextEditor.vue     # Tiptap wrapper
      ConfirmableButton.vue  # Inline confirmation pattern
      EmptyState.vue         # Reusable empty state
      Breadcrumbs.vue        # Navigation breadcrumbs
      TextInput.vue          # Form inputs
      TextArea.vue
      Select.vue
   Layouts/
      AuthenticatedLayout.vue # Top navbar + content wrapper
   Composables/
      useTimerCountdown.ts   # Timer logic with visibility API
      useFileUpload.ts       # Upload queue management
      useFormDirtyCheck.ts   # Unsaved changes detection
      useFormErrors.ts       # Error handling helper
   config/
      custodianship.ts       # Constants (thresholds, colors)
   utils/
       formatFileSize.ts      # File size formatter
       formatDate.ts          # Date utilities
```

#### 3. State Management Strategy

**Inertia-First Approach:**
- Primary state managed via **Inertia props** from Laravel backend
- No Vuex/Pinia needed for MVP
- Local component state for UI-only concerns (modals, animations)
- **Inertia `useForm()`** for all form state with automatic error handling

**Real-Time Updates:**
- Timer countdown via `useTimerCountdown` composable with `setInterval`
- Page Visibility API integration to pause timers when tab inactive
- Optimistic updates for quick actions (timer reset) with background sync

### Key User Flows

#### Flow 1: Create Custodianship
1. Dashboard ’ "New Custodianship" button
2. Create page with single-page form:
   - Name (text input with placeholder example)
   - Message (Tiptap rich text editor ’ markdown output)
   - Interval (dropdown: "1 month (30 days)", etc.)
   - Recipients (email + add/remove, max 2)
   - Attachments (drag & drop or browse, 10MB total limit)
3. Single "Save" button (intelligent: draft if email unverified, active otherwise)
4. On save ’ redirect to Show page
5. Success feedback via visual confirmation (no toast)

**Validations:**
- Server-side only via Laravel Form Requests
- Errors displayed inline under fields via `form.errors.fieldname`
- Global errors (freemium limits) as alert banner (optional)

#### Flow 2: View & Monitor Custodianship
1. Index page ’ click custodianship card
2. Show page with sections:
   - **Header:** Name + status badge (color-coded)
   - **Timer Section:** Live countdown progress bar with color coding
     - Green: >30 days remaining
     - Yellow: 7-30 days
     - Red: <7 days
     - Amber "Pending": timer expired
   - **Details:** Message content + interval
   - **Recipients:** Email + "Added X days ago" with tooltip
   - **Attachments:** File list with download links
   - **History:** Collapsible reset log (initially collapsed)
   - **Danger Zone:** Delete button

**Actions:**
- **Reset Timer:** Primary button with inline confirmation (expand ’ Confirm/Cancel)
  - On confirm: optimistic update (300ms animation) + background Inertia call
  - Disabled when timer expired
- **Edit:** Primary button ’ separate Edit page
- **Delete:** Red button ’ modal with checkbox + name input confirmation

#### Flow 3: Reset Timer
1. Click "Reset Timer" (Index or Show page)
2. Button expands ’ "Confirm Reset" (green) | "Cancel" (gray)
3. Auto-collapse after 5 seconds or click outside
4. On confirm:
   - Immediate visual update (progress bar animates to 100%)
   - Background POST request via Inertia
   - Error toast only if failure (no success toast)
5. Timer countdown restarts from new `next_trigger_at`

#### Flow 4: Upload Attachments
1. Drag files to drop zone or click browse
2. **Automatic upload** to temp storage (individual progress bars)
3. File appears in list: `[icon] filename.pdf (1.2 MB) [X remove]`
4. Dynamic limit display: "3.5 MB / 10 MB used" (color-coded progress bar)
5. On form save: backend associates uploaded files with custodianship via media-library
6. Validation error if exceeds 10MB (prevent save)

#### Flow 5: Delete Custodianship
1. Show page ’ scroll to Danger Zone ’ "Delete Custodianship" (red)
2. Client-side modal (Shadcn Dialog):
   - Warning: "This action is irreversible"
   - Checkbox: "I understand the consequences"
   - Text input: "Type custodianship name to confirm" (case-insensitive)
   - Buttons: Cancel | Delete Permanently (danger)
3. On confirm: `router.delete()` ’ hard delete backend
4. Redirect to Index with confirmation

### Critical Features Implementation

#### Real-Time Timer Countdown

**Composable: `useTimerCountdown.ts`**
```typescript
export function useTimerCountdown(nextTriggerAt: string, intervalDays: number) {
  const remainingSeconds = ref(0)
  const isExpired = computed(() => remainingSeconds.value <= 0)
  const daysRemaining = computed(() => Math.ceil(remainingSeconds.value / 86400))

  // Color coding based on constants
  const statusColor = computed(() => {
    if (isExpired.value) return 'red'
    if (daysRemaining.value < TIMER_THRESHOLDS.WARNING_DAYS) return 'red'
    if (daysRemaining.value < TIMER_THRESHOLDS.SAFE_DAYS) return 'yellow'
    return 'green'
  })

  // Update every second
  let interval: NodeJS.Timeout
  onMounted(() => {
    interval = setInterval(() => {
      if (document.visibilityState === 'visible') {
        // Recalculate from timestamp (resync)
        remainingSeconds.value = Math.max(0, dayjs(nextTriggerAt).diff(dayjs(), 'second'))
      }
    }, 1000)
  })

  onUnmounted(() => clearInterval(interval))

  return { remainingSeconds, daysRemaining, isExpired, statusColor }
}
```

**Features:**
- Updates every second when tab visible
- Pauses when tab hidden (Page Visibility API)
- Resyncs with server timestamp on visibility change
- Prevents battery drain on mobile
- Color-coded based on configured thresholds

#### Two-Stage File Upload

**Flow:**
1. User selects files ’ `AttachmentUploader.vue` component
2. Component immediately uploads to Laravel endpoint `/temp-uploads`
3. Backend stores in temp storage, returns UUID + metadata
4. Component displays file in "ready to attach" list with progress
5. On form submit: passes UUIDs to backend
6. Backend associates temp files with custodianship via Spatie Media Library
7. Cleanup job removes orphaned temp files after 24h

**Benefits:**
- Better UX (no wait on form submit)
- Progress feedback during upload
- Backend controls all security (virus scan, validation)
- Easy rollback if form submit fails

#### Rich Text Editor (Controlled)

**Tiptap Configuration:**
```typescript
// RichTextEditor.vue
const editor = useEditor({
  extensions: [
    StarterKit.configure({
      heading: false,        // Disable headings
      codeBlock: false,      // Disable code
      horizontalRule: false, // Disable HR
    }),
    Bold,
    Italic,
    BulletList,
    OrderedList,
    Link,
  ],
  content: props.modelValue,
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.storage.markdown.getMarkdown())
  }
})
```

**Limited Toolbar:**
- Bold, Italic (text emphasis)
- Bulleted List, Numbered List (structure)
- Link (URLs)
- NO: Headings, code blocks, images, custom HTML
- Output: Clean markdown for security

#### Inline Confirmation Pattern

**ConfirmableButton.vue:**
- Single-click ’ expands to mini-toolbar
- Shows "Confirm" (green) + "Cancel" (gray)
- Auto-collapses after 5 seconds
- Click outside ’ collapse
- Emits `confirmed` event for parent handling
- Reusable across Index and Show pages

**Alternative to intrusive modals while maintaining safety.**

### Responsive Design Strategy

#### Mobile-First with Tailwind
- **Stack layout** (single column) for all screen sizes in MVP
- No complex grids that break on mobile
- Touch-friendly button sizes (min 44x44px)
- Hamburger navigation on mobile (<768px)
- Full navbar on desktop

#### Breakpoint Strategy
- Mobile: <768px ’ hamburger menu, full-width cards
- Tablet: 768-1024px ’ same layout, wider cards
- Desktop: >1024px ’ same layout with margins

**Rationale:** With max 3 custodianships, grid layout provides minimal value. Stack is simpler, more maintainable, universally works.

### Form Handling & Validation

#### Inertia useForm() Integration

**All forms use Inertia form helper:**
```vue
<script setup>
const form = useForm({
  name: '',
  message: '',
  interval_days: 30,
  recipients: [{ email: '' }],
  attachment_uuids: []
})

const submit = () => {
  form.post('/custodianships', {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by backend
    },
    onError: () => {
      // Errors in form.errors automatically
    }
  })
}
</script>

<template>
  <TextInput
    label="Name"
    v-model="form.name"
    :error="form.errors.name"
    placeholder="e.g., Bank account passwords"
  />
</template>
```

#### Server-Side Validation Only
- Laravel Form Requests handle all validation
- Errors passed via Inertia `$page.props.errors`
- Frontend displays errors inline under fields
- No client-side validation to maintain (single source of truth)

#### Dirty State Detection

**useFormDirtyCheck composable:**
```typescript
export function useFormDirtyCheck(form: InertiaForm, initialData: any) {
  const isDirty = computed(() => {
    return JSON.stringify(form.data()) !== JSON.stringify(initialData)
  })

  onMounted(() => {
    window.addEventListener('beforeunload', (e) => {
      if (isDirty.value) {
        e.preventDefault()
        e.returnValue = ''
      }
    })
  })

  return { isDirty }
}
```

**Prevents accidental data loss on navigation.**

### Navigation & Breadcrumbs

#### Breadcrumb Structure
- **Home** (dashboard) ’ **Custodianships** (index) ’ **[Name or "New Custodianship"]** ’ **Edit** (if editing)
- Only previous elements clickable
- Current page as plain text (gray color)
- Always visible for context and quick navigation

#### Top Navigation
- **Desktop:** Horizontal navbar with links (Dashboard, Custodianships, Profile)
- **Mobile:** Hamburger icon ’ slide-in overlay menu
- **Active indicator:** Current page highlighted with `bg-gray-100 text-primary`
- **Implementation:** Inertia `usePage().url` matching for active state

### Styling & UI Components

#### Shadcn-vue Integration
- Copy-paste components in `resources/js/Components/ui/`
- Tailwind-native (no CSS-in-JS)
- Accessible by default (WCAG 2.1)
- Components used:
  - Button (primary, secondary, danger variants)
  - Dialog (delete confirmation)
  - Toast (error notifications)
  - Tooltip (vue-tippy for additional context)
  - Progress (timer bar, upload progress)
  - Alert (validation errors, limits)

#### Color Coding System

**Timer Status:**
- Green (>30 days): Safe, no action needed
- Yellow (7-30 days): Warning, reset soon
- Red (<7 days): Danger, urgent reset needed
- Amber (expired): Pending delivery, no reset possible

**File Upload Limit:**
- Green (<7MB): Plenty of space
- Yellow (7-9MB): Approaching limit
- Red (>9MB): Near/at limit, cannot upload more

#### Typography & Spacing
- Tailwind default scale
- Consistent spacing: `space-y-4` for lists, `gap-4` for grids
- Card-based layout: white background, shadow, rounded corners
- Clear visual hierarchy: h1 (page title), h2 (section), h3 (card title)

### Performance Optimizations

#### 1. Page Visibility API for Timers
- Pause `setInterval` when tab hidden
- Resync with server timestamp on return
- Prevents battery drain on mobile
- Implemented in `useTimerCountdown` composable

#### 2. Optimistic Updates
- Timer reset updates UI immediately
- Background Inertia request syncs with backend
- Rollback only if server error
- Provides instant feedback without waiting for network

#### 3. Lazy Loading (Post-MVP)
- Components loaded on-demand
- Route-based code splitting via Vite
- Not critical for MVP (small app)

#### 4. Preserve Scroll
- `preserveScroll: true` on Inertia calls
- Prevents jarring scroll-to-top on updates
- Especially important for Index page with reset actions

### Security Considerations

#### 1. XSS Prevention
- Rich text editor outputs markdown (not HTML)
- Backend sanitizes markdown before email send
- No user HTML allowed in system

#### 2. File Upload Security
- Backend validates file types and sizes
- Virus scanning (post-MVP)
- Files stored in non-public S3 bucket
- Tokenized download URLs (UUID)
- Rate limiting on download endpoints

#### 3. CSRF Protection
- Inertia handles CSRF tokens automatically
- All POST/PUT/DELETE requests protected

#### 4. Authorization
- Laravel Policies ensure only owner can edit custodianships
- Frontend hides actions user can't perform
- Backend enforces all permissions

### Accessibility (WCAG 2.1)

#### 1. Keyboard Navigation
- All actions accessible via keyboard
- Focus indicators visible
- Tab order logical
- Escape key closes modals

#### 2. Screen Reader Support
- Semantic HTML (`<button>`, `<form>`, `<nav>`)
- ARIA labels where needed
- Status announcements for timer updates (aria-live)
- Descriptive link text (not "click here")

#### 3. Color Contrast
- Tailwind default colors meet WCAG AA
- Color not sole indicator (icons + text with color)
- High contrast mode support (browser native)

#### 4. Form Accessibility
- Labels for all inputs
- Error messages associated with fields (aria-describedby)
- Required fields marked
- Helpful placeholders (not replacing labels)

### Internationalization (i18n)

#### MVP: English Only
- All UI text in English
- Date/time formatting in English (dayjs default locale)
- No translation infrastructure in MVP

#### Post-MVP Preparation
- Extract all UI strings to separate files
- Use vue-i18n for translation keys
- Support for Polish (primary target market)
- Date/time localization with dayjs locales

### Error Handling Strategy

#### 1. Form Validation Errors
- Display inline under fields
- Highlight field with red border
- Focus first error field on submit
- Clear error on field change (optional)

#### 2. Network Errors
- Toast notification for failed actions
- Specific error messages from backend
- Retry option for transient failures
- Fallback to generic "Something went wrong" if no details

#### 3. Empty States
- Dedicated EmptyState component
- Contextual messages ("No custodianships yet")
- Clear CTA ("Create your first custodianship")
- Helpful illustrations (optional, post-MVP)

#### 4. Loading States
- Button spinner during form submit
- Progress bars for file uploads
- Skeleton loaders (post-MVP)
- Prevent double-submit via button disable

### Configuration Management

#### Frontend Config
```typescript
// resources/js/config/custodianship.ts
export const TIMER_THRESHOLDS = {
  SAFE_DAYS: 30,
  WARNING_DAYS: 7
}

export const STATUS_COLORS = {
  safe: 'green',
  warning: 'yellow',
  danger: 'red',
  pending: 'amber'
}

export const UPLOAD_LIMITS = {
  MAX_SIZE_MB: 10,
  MAX_FILES: 10 // reasonable default
}
```

#### Backend Config Sync
```php
// config/custodianship.php
return [
    'allowed_intervals' => [30, 60, 90, 180, 365],
    'min_interval' => 30,
    'max_interval' => 365,
    'max_recipients_free' => 2,
    'max_custodianships_free' => 3,
    'max_attachment_size_mb' => 10,
];
```

**Frontend accesses via Inertia shared data for validation.**

### Testing Considerations

#### Component Testing
- Test TimerProgressBar countdown logic
- Test ConfirmableButton expand/collapse
- Test AttachmentUploader file validation
- Test form dirty state detection
- Use Vitest for Vue component tests

#### E2E Testing (Post-MVP)
- Critical user flows (create, reset, delete)
- Cross-browser compatibility
- Mobile responsiveness
- Use Cypress or Playwright

### Future Enhancements (Post-MVP)

#### 1. Advanced Features
- Bulk reset action (reset all custodianships)
- Deactivate custodianship (pause timer without delete)
- Duplicate custodianship
- Export custodianship data (GDPR compliance)

#### 2. UX Improvements
- Grid layout for desktop (when many custodianships)
- Inline editing for simple fields
- Message preview in Index cards (expandable)
- Advanced filtering and sorting
- Search custodianships by name

#### 3. Polish & Performance
- Skeleton loaders instead of spinners
- Micro-interactions and transitions
- Illustration library for empty states
- Code splitting and lazy loading
- PWA capabilities (offline support)

#### 4. Internationalization
- vue-i18n integration
- Polish language support
- Date/time localization
- Currency formatting (if monetization added)

## Unresolved Issues

### Areas Requiring Further Clarification

#### 1. Authentication & Onboarding
- **Question:** What should the onboarding flow look like for first-time users?
  - Welcome modal with app explanation?
  - Interactive tutorial/tooltips?
  - Straight to "Create first custodianship" CTA?
- **Impact:** Affects time-to-first-custodianship KPI (<5 min target)
- **Recommendation:** User testing needed to validate best approach

#### 2. Email Verification Flow
- **Question:** How prominent should the "Verify email" reminder be for users with unverified emails?
  - Persistent banner on all pages?
  - Only on custodianship pages?
  - Dismissible or always visible?
- **Impact:** User activation rate, draft-to-active conversion
- **Recommendation:** A/B test different prominence levels

#### 3. Timer Expiration Edge Cases
- **Question:** What happens if timer expires while user is on Show/Edit page?
  - Should page auto-refresh to show new status?
  - Should actions become disabled?
  - Should a warning appear?
- **Impact:** Data consistency, user confusion
- **Recommendation:** Define exact behavior and implement graceful degradation

#### 4. Attachment Download UX
- **Question:** Should attachments downloaded by recipients require any authentication or just UUID token?
  - Rate limiting details (10/hour confirmed, but per recipient or global?)
  - Should download trigger notification to custodianship creator? (Post-MVP)
- **Impact:** Security vs. ease of access tradeoff
- **Recommendation:** Security review before production

#### 5. Dark Mode Support
- **Question:** Should MVP include dark mode?
  - Tailwind 4.0 makes it easier, but adds testing overhead
  - User preference system needed
- **Impact:** Development time vs. user satisfaction
- **Recommendation:** Post-MVP unless strong user demand

#### 6. Mobile App Considerations
- **Question:** Any mobile-specific features to consider for responsive web before native app?
  - Add to home screen prompts?
  - Push notifications (browser notifications)?
  - Touch gestures for swipe actions?
- **Impact:** Mobile UX quality
- **Recommendation:** Focus on responsive web first, evaluate native post-MVP

#### 7. Freemium Upgrade Path
- **Question:** How should "upgrade" CTAs be presented when users hit limits?
  - Modal with pricing?
  - Banner with "Upgrade" button?
  - Inline message in limit warnings?
- **Impact:** Conversion to paid plan
- **Recommendation:** Design upgrade flow even if pricing TBD

#### 8. Analytics & Tracking
- **Question:** What user actions should be tracked for product analytics?
  - Page views, button clicks, form submissions?
  - Timer reset frequency?
  - Time spent on pages?
- **Impact:** Product iteration based on data
- **Recommendation:** Define analytics plan before launch

#### 9. Notification Preferences
- **Question:** Should users have control over notification emails in MVP?
  - Opt-out of reminder emails (7 days before expiration)?
  - Frequency settings?
- **Impact:** Email deliverability, user satisfaction
- **Recommendation:** Start with default notifications, add preferences post-MVP

#### 10. Browser Compatibility
- **Question:** Which browsers must be supported in MVP?
  - Modern browsers only (Chrome, Firefox, Safari, Edge latest 2 versions)?
  - IE11 support needed? (hopefully not)
  - Mobile browsers (Safari iOS, Chrome Android)?
- **Impact:** Development time, polyfill requirements
- **Recommendation:** Define support matrix before development

#### 11. Loading States & Skeletons
- **Question:** Should initial page loads show skeleton screens or simple spinners?
  - Skeleton screens better UX but more code
  - When is Inertia page transition slow enough to need indicator?
- **Impact:** Perceived performance
- **Recommendation:** Test on slow connections, implement if needed

#### 12. Help & Documentation
- **Question:** Should there be in-app help/documentation links?
  - FAQ page?
  - Contextual help icons with tooltips?
  - External documentation site?
- **Impact:** Support ticket volume
- **Recommendation:** Minimal in-app help in MVP, expand based on support requests

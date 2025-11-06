# Custodianship Show View - Testing Guide

## Overview
This guide provides instructions for testing the Custodianship Show view implementation.

## Accessing the View

### Using Mock Data (Development)
The Show view is currently configured to use mock data for development testing.

**URL:** `/custodianships/{uuid}`

Example URLs to test with mock data:
- `/custodianships/a1b2c3d4-e5f6-4a5b-9c8d-7e6f5a4b3c2d` - Active custodianship with expired timer
- `/custodianships/b2c3d4e5-f6a7-4b5c-9d8e-7f6a5b4c3d2e` - Active custodianship expiring soon
- `/custodianships/c3d4e5f6-a7b8-4c5d-9e8f-7a6b5c4d3e2f` - Draft custodianship

## Test Scenarios

### 1. Active Custodianship with Expired Timer
**Mock UUID:** `a1b2c3d4-e5f6-4a5b-9c8d-7e6f5a4b3c2d`

**Expected UI:**
- Status badge: "Pending Delivery" (amber)
- Timer section: Amber background with "Timer expired. Message will be sent shortly."
- Amber banner: "Your timer has expired. The message will be sent shortly unless you reset it."
- Reset Timer button: Disabled with tooltip "Cannot reset - message will be sent shortly"
- Edit button: Enabled

**Test Actions:**
- Verify timer progress bar shows red/expired state
- Verify Reset Timer button is disabled
- Verify tooltip shows on hover over disabled Reset button
- Click Edit button to navigate to Edit view

### 2. Active Custodianship Expiring Soon
**Mock UUID:** `b2c3d4e5-f6a7-4b5c-9d8e-7f6a5b4c3d2e`

**Expected UI:**
- Status badge: "Active" (green)
- Timer section: Shows countdown with time remaining
- Timer progress bar: Yellow or red based on time remaining
- Reset Timer button: Enabled (green button)
- No warning banners

**Test Actions:**
- Verify timer countdown updates every second
- Click Reset Timer button
- Verify confirmation buttons appear (Confirm Reset / Cancel)
- Click Confirm Reset
- Verify timer resets and optimistic UI update occurs
- Verify last reset time updates

### 3. Draft Custodianship
**Mock UUID:** `c3d4e5f6-a7b8-4c5d-9e8f-7a6b5c4d3e2f`

**Expected UI:**
- Status badge: "Draft" (gray)
- Timer section: "Timer inactive" with gray progress bar
- Blue banner: "This custodianship is a draft. Verify your email to activate it."
- Reset Timer button: Not visible (drafts don't have active timers)
- Edit button: Enabled

**Test Actions:**
- Verify timer shows inactive state
- Verify no Reset button is shown
- Click Edit button to navigate to Edit view

### 4. Recipients Section
**All custodianships:**

**Expected UI:**
- Card title shows count: "Recipients (X)"
- Each recipient shows:
  - Email address
  - Relative time added (e.g., "Added 2 days ago")
- Empty state: "(No recipients)" if no recipients

**Test Actions:**
- Verify all recipients are displayed
- Verify relative timestamps are correct
- Hover over timestamp to see tooltip with exact date/time

### 5. Attachments Section
**All custodianships:**

**Expected UI:**
- Card title shows count: "Attachments (X)"
- Each attachment shows:
  - File type icon (PDF, DOC, image, etc.)
  - File name
  - File size (formatted: KB, MB)
  - Download button
- Empty state: "(No attachments)" if no attachments

**Test Actions:**
- Verify file type icons are correct
- Verify file sizes are formatted properly
- Click Download button
- Verify download URL opens in new tab: `/custodianships/{uuid}/attachments/{id}/download`

### 6. Message Content Section
**All custodianships:**

**Expected UI:**
- Shows message content as plain text (no markdown rendering in MVP)
- Empty state: "(No message content)" if content is null/empty
- Shows check-in interval in days

**Test Actions:**
- Verify message content displays correctly
- Verify line breaks are preserved
- Verify interval shows correct number of days

### 7. Reset History Section (Collapsible)
**Custodianships with reset history:**

**Expected UI:**
- Card header shows count: "Reset History (X)"
- Chevron icon indicates collapsed/expanded state
- When expanded:
  - Table with columns: Timestamp, Method, IP Address
  - Timestamps show relative time with tooltip for exact date/time
  - Methods show human-readable labels: "Manual" or "Post-Edit"
  - IP addresses in monospace font
- Empty state: "(No reset history yet)" if no history

**Test Actions:**
- Click header to expand/collapse section
- Verify chevron icon changes direction
- Hover over timestamps to see exact date/time tooltips
- Verify reset methods are human-readable
- Verify IP addresses display correctly

### 8. Danger Zone
**All custodianships:**

**Expected UI:**
- Red-bordered card with warning icon
- Title: "Danger Zone"
- Description: "Deleting this custodianship is permanent and cannot be undone."
- Delete Custodianship button (red outline)

**Test Actions:**
- Click "Delete Custodianship" button
- Verify modal opens with warning message
- Verify checkbox: "I understand this action is permanent"
- Verify text input: "Type [custodianship name] to confirm"
- Try clicking Delete without checking box - should be disabled
- Check the checkbox - Delete still disabled
- Type incorrect name - Delete still disabled
- Type correct name (case-insensitive) - Delete becomes enabled
- Input border turns green when name matches
- Click Cancel - modal closes
- Reopen modal and complete deletion flow
- Verify redirect to /custodianships on successful deletion

### 9. Breadcrumbs
**All custodianships:**

**Expected UI:**
- Home > Custodianships > [Custodianship Name]
- Home and Custodianships are links
- Current page (custodianship name) is bold, not a link

**Test Actions:**
- Click "Home" - navigate to /dashboard
- Click "Custodianships" - navigate to /custodianships
- Verify current page is not clickable

### 10. Responsive Design
**All custodianships:**

**Test Actions:**
- Test on desktop (1920x1080)
  - Verify two-column layout (Timer/Details left, Recipients/Attachments right)
- Test on tablet (768px)
  - Verify layout remains two-column or stacks appropriately
- Test on mobile (375px)
  - Verify single-column layout
  - Verify all buttons are accessible
  - Verify modal is responsive

## Development Notes

### Mock Data Configuration
The Show view currently uses mock data from `resources/js/data/mockCustodianships.ts`.

To switch to real data from backend:
1. Remove the mock data import and usage in `Show.vue`
2. Use the props directly: `const custodianship = computed(() => props.custodianship)`
3. Ensure backend controller returns data matching `CustodianshipDetailViewModel` interface

### Component Dependencies
- TimerSection.vue - Shows timer progress and status
- MessageContentViewer.vue - Displays message content
- AttachmentList.vue + AttachmentItem.vue - Shows attachments with download
- ResetHistoryTable.vue - Displays reset history in table format
- DeleteCustodianshipModal.vue - Confirmation modal for deletion
- DangerZone.vue - Warning section for destructive actions
- ConfirmableButton.vue - Two-step confirmation button for Reset Timer
- StatusBadge.vue - Displays status with color coding

### Shadcn-vue Components Used
- Card, CardContent, CardHeader, CardTitle
- Button
- Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
- Input, Label, Checkbox
- Table, TableBody, TableCell, TableHead, TableHeader, TableRow
- Tooltip, TooltipContent, TooltipProvider, TooltipTrigger
- Progress
- Badge
- Alert (for banners)

## Known Limitations (MVP)
1. Message content is displayed as plain text (no markdown rendering)
2. Attachment thumbnails are not implemented
3. No attachment preview functionality
4. Reset history section is optional and may not be included in initial MVP
5. Download functionality uses simple window.open() (no progress tracking)
6. No rate limiting UI for downloads
7. No real-time timer updates when timer expires

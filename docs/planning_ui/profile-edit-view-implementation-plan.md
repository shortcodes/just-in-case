# Profile Edit View - Implementation Plan

## 1. Overview

Profile Edit view (`Edit.vue`) provides authenticated users the ability to update their profile information, change email address, update password, and delete their account. This view consists of three distinct sections: Profile Information Update, Password Update, and Account Deletion. Each section operates independently with its own form submission and validation.

**Core Features:**
- Update profile information (name)
- Change email address with reverification requirement
- Update password with current password confirmation
- Delete account with password confirmation
- Independent form sections with separate submissions
- Real-time validation feedback
- Success notifications for updates
- Security confirmations for sensitive actions
- Responsive design for all device sizes

**User Flow:**
1. User navigates to profile settings
2. User views current profile information
3. User updates desired fields in specific section
4. User submits section form
5. System validates and processes update
6. Success message displayed
7. Form reflects updated information

**Implementation Time Estimate:** 10-12 hours

---

## 2. Routing

### Route Definition
**Route Name:** `profile.edit`
**Path:** `/profile`
**HTTP Method:** GET (display view)
**Controller:** `ProfileController@edit`
**Middleware:** `auth`, `verified`

### Update Routes

**Profile Information Update:**
- Route Name: `profile.update`
- Path: `/profile`
- HTTP Method: PATCH
- Controller: `ProfileController@update`
- Middleware: `auth`, `verified`

**Password Update:**
- Route Name: `password.update`
- Path: `/password`
- HTTP Method: PUT
- Controller: `PasswordController@update`
- Middleware: `auth`, `verified`

**Account Deletion:**
- Route Name: `profile.destroy`
- Path: `/profile`
- HTTP Method: DELETE
- Controller: `ProfileController@destroy`
- Middleware: `auth`, `verified`

### Inertia Response Structure
```typescript
{
  component: 'Profile/Edit',
  props: {
    mustVerifyEmail: boolean, // Whether app requires email verification
    status?: string // Success message if present
  }
}
```

### Related Routes
- `dashboard` - User dashboard (link in navigation)
- `verification.notice` - Email verification (if email changed)
- `login` - Login page (redirect after account deletion)

---

## 3. Component Structure

### Main Component
**File:** `resources/js/Pages/Profile/Edit.vue`

**Component Hierarchy:**
```
Edit.vue (Page Component)
├── AuthenticatedLayout (Main app layout)
│   ├── Head (Page metadata)
│   └── Navigation (Top navigation)
└── Content Container
    ├── Page Header
    │   └── Title: "Profile Settings"
    ├── UpdateProfileInformationForm (Section 1)
    │   ├── Section Header
    │   ├── Name Input Field
    │   ├── Email Input Field
    │   ├── Email Verification Notice (if email unverified)
    │   ├── Save Button
    │   └── Success Message
    ├── UpdatePasswordForm (Section 2)
    │   ├── Section Header
    │   ├── Current Password Input
    │   ├── New Password Input
    │   ├── Confirm Password Input
    │   ├── Save Button
    │   └── Success Message
    └── DeleteUserForm (Section 3)
        ├── Section Header
        ├── Warning Message
        ├── Delete Account Button
        └── Confirmation Modal
            ├── Warning Text
            ├── Password Input (confirmation)
            ├── Cancel Button
            └── Confirm Delete Button
```

**Key Characteristics:**
- Three independent form sections
- Each section has its own submit button and state
- Each section handles its own validation and success messages
- Delete section uses modal for confirmation
- Responsive grid layout for desktop/tablet
- Stack layout for mobile

---

## 4. Component Details

### 4.1 Edit.vue (Main Page Component)

**Purpose:**
Container page component that displays profile edit view and houses three form sections for updating profile information, password, and account deletion.

**Props:**
- `mustVerifyEmail` (boolean, required) - Whether app requires email verification
- `status` (string, optional) - Status message for feedback

**Emits:**
None - forms handle their own submissions

**Key Responsibilities:**
- Layout and organize three form sections
- Pass necessary props to child sections
- Provide consistent page structure
- Handle responsive layout

**Template Structure:**
- Page title and metadata
- Main heading: "Profile Settings"
- Three distinct sections with visual separation
- Responsive grid layout

**Styling Approach:**
- Clean, organized layout with clear sections
- Card-based design for each section
- Consistent spacing and borders
- Tailwind CSS utilities
- Responsive breakpoints for mobile/tablet/desktop

### 4.2 UpdateProfileInformationForm.vue (Section Component)

**Purpose:**
Form section for updating user's name and email address.

**Props:**
- `mustVerifyEmail` (boolean) - Whether email verification is required
- `status` (string, optional) - Success message

**Key Responsibilities:**
- Display current name and email
- Handle profile information updates
- Trigger email reverification if email changed
- Display verification notice if email unverified
- Show success message after save

**Form Fields:**
- Name (text input)
- Email (email input)

**Validation:**
- Name: required, string, max 255 characters
- Email: required, valid email, unique in users table (except current user)

**Special Behavior:**
- If email changed: user must reverify new email
- Display unverified badge if email_verified_at is null
- Link to resend verification email

### 4.3 UpdatePasswordForm.vue (Section Component)

**Purpose:**
Form section for changing user's password with current password confirmation.

**Props:**
None - operates independently

**Key Responsibilities:**
- Validate current password before allowing change
- Ensure new password meets requirements
- Confirm new password matches
- Clear form after successful update
- Display success message

**Form Fields:**
- Current Password (password input)
- New Password (password input)
- Confirm New Password (password input)

**Validation:**
- Current Password: required, must match user's actual password
- New Password: required, min 8 characters, password complexity rules
- Confirm Password: required, must match new password

**Special Behavior:**
- All password fields have show/hide toggles
- Form cleared after successful submission
- Success message displayed temporarily

### 4.4 DeleteUserForm.vue (Section Component)

**Purpose:**
Form section for permanent account deletion with password confirmation.

**Props:**
None - operates independently

**Key Responsibilities:**
- Display warning about permanent deletion
- Open confirmation modal on delete request
- Require password for confirmation
- Handle account deletion
- Redirect to login after deletion

**Confirmation Modal:**
- Warning about data loss
- Password input for verification
- Cancel and Confirm buttons
- Closes on cancel or successful deletion

**Validation:**
- Password: required, must match user's current password

**Special Behavior:**
- Deletion is permanent and immediate
- All user data deleted (account, custodianships, recipients, etc.)
- User logged out and redirected to login
- Confirmation modal prevents accidental deletion

---

## 5. Types and Interfaces

### Props Type
```typescript
interface EditProps {
  mustVerifyEmail: boolean
  status?: string
}
```

### Profile Information Form Type
```typescript
interface ProfileInformationForm {
  name: string
  email: string
}
```

### Password Update Form Type
```typescript
interface PasswordUpdateForm {
  current_password: string
  password: string
  password_confirmation: string
}
```

### Account Delete Form Type
```typescript
interface AccountDeleteForm {
  password: string
}
```

### Validation Errors Type
```typescript
interface ProfileErrors {
  name?: string
  email?: string
}

interface PasswordErrors {
  current_password?: string
  password?: string
  password_confirmation?: string
}

interface DeleteErrors {
  password?: string
}
```

---

## 6. State Management

### Profile Information Section State

**Form Fields:**
- `name` - User's display name
- `email` - User's email address

**Initial Values:**
- Populated from `$page.props.auth.user`

**UI State:**
- `processing` - Form submission in progress
- `recentlySuccessful` - Shows success message for 2 seconds

### Password Update Section State

**Form Fields:**
- `current_password` - Current password for verification
- `password` - New password
- `password_confirmation` - Confirm new password

**Initial Values:**
- All fields empty strings

**UI State:**
- `processing` - Form submission in progress
- `recentlySuccessful` - Shows success message for 2 seconds
- `showCurrentPassword` - Toggle visibility
- `showNewPassword` - Toggle visibility
- `showConfirmPassword` - Toggle visibility

### Account Delete Section State

**Modal State:**
- `confirmingDeletion` - Boolean, shows/hides modal

**Form Fields:**
- `password` - Password for deletion confirmation

**Initial Values:**
- `password` - Empty string

**UI State:**
- `processing` - Deletion in progress

---

## 7. Mock Data / Example Content

### Sample Inertia Props
```typescript
{
  mustVerifyEmail: true,
  status: 'profile-updated',
  auth: {
    user: {
      id: 1,
      name: 'Jan Kowalski',
      email: 'jan.kowalski@example.com',
      email_verified_at: '2025-01-15T10:30:00.000000Z'
    }
  }
}
```

### Content Text

**Page Header:**
"Profile Settings"

**Profile Information Section:**
- Header: "Profile Information"
- Description: "Update your account's profile information and email address."
- Name Label: "Name"
- Email Label: "Email"
- Unverified Notice: "Your email address is unverified. Click here to resend verification email."
- Save Button: "Save" (normal) / "Saving..." (loading)
- Success Message: "Profile information updated successfully."

**Password Update Section:**
- Header: "Update Password"
- Description: "Ensure your account is using a long, random password to stay secure."
- Current Password Label: "Current Password"
- New Password Label: "New Password"
- Confirm Password Label: "Confirm Password"
- Save Button: "Save" (normal) / "Saving..." (loading)
- Success Message: "Password updated successfully."

**Account Delete Section:**
- Header: "Delete Account"
- Description: "Permanently delete your account and all associated data."
- Warning: "Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain."
- Delete Button: "Delete Account"
- Modal Heading: "Are you sure you want to delete your account?"
- Modal Warning: "Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
- Password Label: "Password"
- Cancel Button: "Cancel"
- Confirm Button: "Delete Account" (normal) / "Deleting..." (loading)

---

## 8. User Interactions

### Profile Information Update Flow

**Step 1: View Current Information**
- User sees current name and email populated
- If email unverified, sees verification notice

**Step 2: Edit Information**
- User modifies name field
- Or user modifies email field
- Save button becomes enabled (if changes detected)

**Step 3: Submit Changes**
- User clicks "Save" button
- Button enters loading state
- Form data submitted via PATCH request

**Step 4: Handle Response**
- Success: "Profile information updated successfully" message
- If email changed: redirect to verification notice page
- Errors: Display under respective fields

### Password Update Flow

**Step 1: Open Password Section**
- All password fields initially empty
- Show/hide toggles available for each field

**Step 2: Enter Passwords**
- User enters current password
- User enters new password
- User enters new password confirmation
- Toggle visibility as needed

**Step 3: Submit Changes**
- User clicks "Save" button
- Button enters loading state
- Form data submitted via PUT request

**Step 4: Handle Response**
- Success: "Password updated successfully" message, form cleared
- Error: Display under respective field (e.g., wrong current password)

### Account Deletion Flow

**Step 1: Initiate Deletion**
- User clicks "Delete Account" button
- Confirmation modal opens
- Focus moves to modal

**Step 2: Confirm Deletion**
- User reads warning message
- User enters password for confirmation
- User can cancel or confirm

**Step 3: Cancel Deletion**
- User clicks "Cancel" button
- Modal closes
- No changes made

**Step 4: Confirm Deletion**
- User clicks "Delete Account" button in modal
- Button enters loading state
- Form data submitted via DELETE request
- Password validated

**Step 5: Deletion Complete**
- Account and all data deleted
- User logged out
- Redirect to login page
- Message: "Your account has been deleted."

---

## 9. Validation and Error Handling

### Profile Information Validation

**Server-Side Rules:**
- Name: required, string, max:255
- Email: required, email, unique:users,email (except current user)

**Error Messages:**
- Name required: "The name field is required."
- Name too long: "The name must not exceed 255 characters."
- Email required: "The email field is required."
- Email invalid: "Please provide a valid email address."
- Email taken: "This email address is already in use."

**Special Cases:**
- Email unchanged: No reverification needed
- Email changed: User redirected to verification notice
- Email format invalid: Show error immediately

### Password Update Validation

**Server-Side Rules:**
- Current Password: required, current_password
- New Password: required, min:8, password rules
- Password Confirmation: required, same:password

**Error Messages:**
- Current password required: "Please enter your current password."
- Current password incorrect: "The current password is incorrect."
- New password required: "Please enter a new password."
- New password too short: "Password must be at least 8 characters."
- Password complexity: "Password must include letters, numbers, and symbols."
- Confirmation mismatch: "Password confirmation does not match."

**Special Cases:**
- Current password wrong: Specific error, don't proceed
- New password same as current: Warning (optional enhancement)
- Form cleared after success

### Account Deletion Validation

**Server-Side Rules:**
- Password: required, current_password

**Error Messages:**
- Password required: "Please enter your password to confirm deletion."
- Password incorrect: "The password is incorrect."

**Special Cases:**
- Deletion is permanent and immediate
- All related data deleted (cascading)
- User logged out automatically
- Cannot be undone

### Error Display Strategy
- Field-level errors below each input
- Error styling: red border on input, red text for message
- Errors cleared on field interaction
- Success messages auto-dismiss after 2 seconds
- Modal errors displayed within modal

---

## 10. Implementation Steps

### Step 1: Create Routes and Controllers (1.5 hours)
- Define GET route for profile edit page
- Define PATCH route for profile information update
- Define PUT route for password update
- Define DELETE route for account deletion
- Create ProfileController with edit, update, destroy methods
- Create PasswordController with update method
- Apply auth and verified middleware
- Implement validation rules for each endpoint

### Step 2: Create Main Edit Component (1 hour)
- Create `resources/js/Pages/Profile/Edit.vue`
- Define component props
- Set up AuthenticatedLayout wrapper
- Create page structure with three sections
- Add Head component for page metadata
- Implement responsive layout with Tailwind

### Step 3: Create UpdateProfileInformationForm Component (2 hours)
- Create `resources/js/Components/Profile/UpdateProfileInformationForm.vue`
- Build form with name and email fields
- Populate initial values from auth user
- Implement form submission with Inertia
- Add email verification notice (conditional)
- Add success message display
- Style consistently with design system

### Step 4: Create UpdatePasswordForm Component (2 hours)
- Create `resources/js/Components/Profile/UpdatePasswordForm.vue`
- Build form with three password fields
- Add show/hide toggles for each password field
- Implement form submission with Inertia
- Add form clearing after success
- Add success message display
- Style password fields and toggles

### Step 5: Create DeleteUserForm Component (2 hours)
- Create `resources/js/Components/Profile/DeleteUserForm.vue`
- Build delete button and warning text
- Create confirmation modal component
- Implement modal open/close logic
- Add password input in modal
- Implement deletion with Inertia
- Handle logout and redirect after deletion
- Style modal and warning messages

### Step 6: Implement Form Validation and Errors (1 hour)
- Display validation errors for each form section
- Style error messages consistently
- Implement error clearing on field interaction
- Add specific error handling for each endpoint
- Test all validation rules

### Step 7: Add Success Messages and Feedback (0.5 hours)
- Implement success message display for each section
- Add auto-dismiss after 2 seconds
- Style success messages (green/success color)
- Ensure messages don't overlap between sections

### Step 8: Testing and Refinement (2-2.5 hours)
- Test profile information update (name only)
- Test email change and reverification flow
- Test password update with correct current password
- Test password update with wrong current password
- Test password validation rules
- Test account deletion with correct password
- Test account deletion with wrong password
- Verify modal can be canceled
- Test all error scenarios
- Verify form clearing after password update
- Test responsive layout on mobile/tablet
- Verify accessibility (keyboard navigation, screen readers)
- Test with long names/emails
- Final styling and UX polish

---

## Notes and Considerations

### Security Considerations
- Current password required for password change
- Current password required for account deletion
- Email changes trigger reverification
- All forms use CSRF protection
- Deletion is logged for audit trail
- Password fields never pre-populated
- Secure password comparison (timing-safe)

### UX Considerations
- Three independent sections reduce cognitive load
- Clear visual separation between sections
- Success messages provide immediate feedback
- Confirmation modal prevents accidental deletion
- Password visibility toggles improve usability
- Form clearing after password change prevents confusion
- Descriptive labels and help text guide user

### Data Deletion Considerations
- Account deletion is permanent and immediate
- All related data must be deleted (cascading):
  - Custodianships created by user
  - Recipients added by user
  - Messages authored by user
  - Attachments uploaded by user
  - User sessions
- Consider soft delete alternative (future enhancement)
- Email notification to user after deletion (future)

### Email Change Implications
- User must reverify new email address
- Old email no longer valid for login
- User can access app during reverification
- Notification to old email about change (future enhancement)
- Rate limiting on email changes (prevent abuse)

### Accessibility Requirements
- Proper form labels and ARIA attributes
- Keyboard navigation for all interactions
- Modal focus trap and escape key support
- Screen reader announcements for success/error
- Sufficient color contrast
- Focus indicators on all interactive elements
- Password field show/hide accessible via keyboard

### Future Enhancements
- Two-factor authentication management section
- Profile photo upload
- Additional profile fields (bio, location, etc.)
- Email change confirmation (link to old email)
- Account deactivation (soft delete) instead of deletion
- Download user data before deletion (GDPR compliance)
- Password strength indicator
- Password history (prevent reuse)
- Activity log (recent logins, changes)
- Notification preferences section

### Integration Points
- Laravel's authentication system
- Email verification system
- Password hashing (bcrypt/argon2)
- Session management
- Database cascading deletes
- Queue system for email notifications
- Flash messages for cross-page feedback

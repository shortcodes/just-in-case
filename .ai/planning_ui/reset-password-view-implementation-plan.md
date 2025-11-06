# Reset Password View - Implementation Plan

## 1. Overview

Reset Password view (`ResetPassword.vue`) provides the interface for users to set a new password after requesting a password reset. This view is accessed through a signed URL sent via email containing a token and email parameter. The view allows users to create a new password with confirmation, validates the token validity and expiration, and provides appropriate feedback throughout the process.

**Core Features:**
- Token and email validation from URL parameters
- Password reset form with new password and confirmation fields
- Server-side validation with real-time error display
- Token expiration handling (1 hour validity)
- Rate limiting protection
- Automatic redirect to login after successful reset
- Security measures against token reuse and manipulation

**User Flow:**
1. User clicks password reset link from email
2. System validates token and email from URL
3. User enters new password and confirmation
4. System validates password strength and match
5. System resets password if token valid and not expired
6. User redirected to login with success message

**Implementation Time Estimate:** 6-7 hours

---

## 2. Routing

### Route Definition
**Route Name:** `password.reset`
**Path:** `/reset-password/{token}`
**HTTP Method:** GET (form display), POST (form submission)
**Controller:** `Auth\NewPasswordController`
**Middleware:** `guest`, `throttle:5,1` (5 attempts per minute)

### Route Parameters
- `token` - Password reset token from email (path parameter)
- `email` - User's email address (query parameter)

### Inertia Response Structure
```typescript
{
  component: 'Auth/ResetPassword',
  props: {
    token: string,
    email: string,
    status?: string // Success message if present
  }
}
```

### Related Routes
- `password.request` - Forgot Password form (link in case of expired token)
- `login` - Login page (redirect after successful reset)

---

## 3. Component Structure

### Main Component
**File:** `resources/js/Pages/Auth/ResetPassword.vue`

**Component Hierarchy:**
```
ResetPassword.vue (Page Component)
├── GuestLayout (Layout wrapper)
│   ├── ApplicationLogo (Brand logo)
│   └── Head (Page metadata)
└── Form Container
    ├── Status Message (Success notification)
    ├── Email Field (Read-only display)
    ├── Password Field (New password input)
    ├── Password Confirmation Field
    ├── Submit Button
    └── Error Messages (Field-specific errors)
```

**Key Characteristics:**
- Single-page component with no sub-components
- Uses Inertia form helper for submission
- Server-side validation only
- Displays email from URL as read-only field
- Token passed as hidden value in form submission

---

## 4. Component Details

### 4.1 ResetPassword.vue (Main Component)

**Purpose:**
Main page component that handles the password reset form, validates token, and processes new password submission.

**Props:**
- `token` (string, required) - Password reset token from URL
- `email` (string, required) - User email from URL query parameter
- `status` (string, optional) - Status message for feedback

**Emits:**
None - form submission handled via Inertia POST

**Key Responsibilities:**
- Display password reset form with token and email validation
- Manage form state and validation errors
- Submit new password to backend
- Display validation errors and success/failure messages
- Handle token expiration and invalid token scenarios
- Provide user feedback throughout the process

**Template Structure:**
- Page title and metadata
- Status message display (if present)
- Form header with descriptive text
- Email field (read-only) showing the account being reset
- Password input field with show/hide toggle
- Password confirmation input field with show/hide toggle
- Password requirements hint text
- Submit button with loading state
- Link to request new reset token if current one expired
- Error message display for each field

**Styling Approach:**
- Consistent with authentication pages design
- Uses GuestLayout for branding consistency
- Tailwind CSS for styling
- Responsive design (mobile-first)
- Clear visual hierarchy emphasizing password fields
- Accessible form labels and error messages
- Password strength indicator (visual feedback)

---

## 5. Types and Interfaces

### Form Data Type
```typescript
interface ResetPasswordForm {
  token: string
  email: string
  password: string
  password_confirmation: string
}
```

### Props Type
```typescript
interface ResetPasswordProps {
  token: string
  email: string
  status?: string
}
```

### Validation Errors Type
```typescript
interface ResetPasswordErrors {
  token?: string
  email?: string
  password?: string
  password_confirmation?: string
}
```

---

## 6. State Management

### Form State
**Fields:**
- `token` - Hidden field, initialized from props
- `email` - Read-only field, initialized from props
- `password` - User input for new password
- `password_confirmation` - User input for password confirmation

**Initial Values:**
- `token` - From URL path parameter
- `email` - From URL query parameter
- `password` - Empty string
- `password_confirmation` - Empty string

### UI State
**Loading States:**
- `processing` - True during form submission
- Button shows loading spinner and is disabled

**Visibility States:**
- `showPassword` - Toggle for password field visibility
- `showPasswordConfirmation` - Toggle for confirmation field visibility

**Error States:**
- `errors` - Object containing validation errors from server
- Managed automatically by Inertia form helper

---

## 7. Mock Data / Example Content

### Sample Inertia Props
```typescript
{
  token: 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0',
  email: 'jan.kowalski@example.com',
  status: null
}
```

### Password Requirements Text
"Password must be at least 8 characters long and contain a mix of letters, numbers, and special characters."

### Success Status Message
"Your password has been reset successfully. You can now log in with your new password."

### Form Field Labels and Placeholders
- Email: "Email" (read-only, no placeholder)
- Password: "New Password" / "Enter your new password"
- Password Confirmation: "Confirm Password" / "Re-enter your new password"
- Submit Button: "Reset Password" (normal) / "Resetting..." (loading)

### Error Messages Examples
- Token invalid: "This password reset link is invalid. Please request a new one."
- Token expired: "This password reset link has expired. Please request a new one."
- Password too short: "Password must be at least 8 characters."
- Passwords don't match: "Password confirmation does not match."
- Email mismatch: "The provided email does not match the reset token."

---

## 8. User Interactions

### Form Interaction Flow

**Step 1: Page Load**
- System validates token format and email presence
- If token or email missing: redirect to forgot password page
- Email field populated and disabled
- Password fields empty and focused (first field)

**Step 2: Password Entry**
- User enters new password in first field
- Show/hide toggle allows viewing password
- Real-time visual feedback for password strength (optional)
- User enters same password in confirmation field
- Show/hide toggle for confirmation field

**Step 3: Form Submission**
- User clicks "Reset Password" button
- Button enters loading state (disabled, shows spinner)
- Form data sent to server via POST request

**Step 4: Validation Response**
- If validation fails: errors displayed under respective fields
- Button returns to normal state
- User corrects errors and resubmits

**Step 5: Success**
- Success message displayed briefly
- Automatic redirect to login page after 2 seconds
- Flash message on login page confirms password reset

**Step 6: Token Expiration**
- If token expired: specific error message displayed
- Link to request new reset token shown
- User can click link to return to forgot password page

### Button States
- **Default:** "Reset Password" (primary button styling)
- **Hover:** Slight color change, cursor pointer
- **Loading:** "Resetting..." with spinner, disabled
- **Disabled:** Grayed out when form invalid or processing

### Interactive Elements
1. Password visibility toggles (eye icon buttons)
2. Submit button with loading state
3. Link to forgot password page (if token expired)
4. Form fields with focus states

---

## 9. Validation and Error Handling

### Client-Side Validation
**No client-side validation implemented** - all validation performed server-side for security reasons.

**Rationale:**
- Token validation must be server-side
- Password complexity rules may change
- Prevents validation bypass attempts
- Server is source of truth

### Server-Side Validation Rules

**Token Field:**
- Required
- Must exist in password_reset_tokens table
- Must not be expired (1 hour validity)
- Must match provided email
- One-time use only (invalidated after successful reset)

**Email Field:**
- Required
- Must be valid email format
- Must exist in users table
- Must match token's associated email

**Password Field:**
- Required
- Minimum 8 characters
- Maximum 255 characters
- Must contain mix of character types (letters, numbers, symbols)
- Cannot be common password (Laravel's password rule)

**Password Confirmation Field:**
- Required
- Must match password field exactly

### Error Display Strategy
- **Field-level errors:** Displayed immediately below each field
- **Token errors:** Displayed as general form error above fields
- **Generic vs specific:** Token/email errors are specific, password errors are detailed
- **Error persistence:** Errors cleared on field interaction or form resubmission
- **Rate limit errors:** Displayed as general message with retry timer

### Edge Cases

**Expired Token:**
- Error message: "This password reset link has expired."
- Action: Display link to request new token
- No form submission allowed

**Invalid Token:**
- Error message: "This password reset link is invalid."
- Action: Display link to request new token
- Possible cause: Token already used or never existed

**Token/Email Mismatch:**
- Error message: "The provided email does not match this reset request."
- Security: Prevents token reuse with different email
- Action: User must request new token

**Rate Limiting:**
- Limit: 5 attempts per minute per IP
- Error: "Too many password reset attempts. Please try again in X seconds."
- Prevents brute force attacks

**Network Errors:**
- Timeout after 30 seconds
- Display: "Unable to reset password. Please check your connection and try again."
- Retry: User can resubmit form

**Token Already Used:**
- Error: "This password reset link has already been used."
- Action: Display link to request new token
- Security: Prevents token replay attacks

---

## 10. Implementation Steps

### Step 1: Create Route and Controller (1 hour)
- Define GET route for displaying form with token parameter
- Define POST route for processing password reset
- Create NewPasswordController with store method
- Apply guest and throttle middleware
- Implement token and email validation in controller
- Set up password reset logic using Laravel's PasswordBroker
- Configure redirect to login after success

### Step 2: Create ResetPassword Component (1.5 hours)
- Create `resources/js/Pages/Auth/ResetPassword.vue`
- Define component props (token, email, status)
- Set up GuestLayout wrapper
- Create form structure with all fields
- Implement Inertia form helper for state management
- Add Head component for page metadata
- Set up basic styling with Tailwind

### Step 3: Implement Form Fields (1 hour)
- Add email field (read-only, populated from props)
- Add password input with show/hide toggle
- Add password confirmation input with show/hide toggle
- Style fields consistently with other auth forms
- Add password requirements hint text
- Implement focus management (auto-focus first field)

### Step 4: Add Form Submission (0.5 hours)
- Configure Inertia form POST to password.update route
- Pass token, email, password, password_confirmation
- Handle form submission with loading state
- Disable button during processing
- Implement error handling for submission failures

### Step 5: Implement Error Display (1 hour)
- Display validation errors under respective fields
- Add general error message display for token issues
- Style error messages consistently
- Implement error clearing on field interaction
- Add specific messaging for token expiration
- Display link to request new token on failure

### Step 6: Add Success Handling (0.5 hours)
- Display success message on password reset
- Implement automatic redirect to login after 2 seconds
- Pass flash message to login page
- Ensure token is invalidated after success

### Step 7: Testing and Edge Cases (1.5 hours)
- Test with valid token and email
- Test with expired token (after 1 hour)
- Test with invalid token
- Test with token/email mismatch
- Test password validation rules
- Test password mismatch error
- Test rate limiting (6+ attempts)
- Test with already used token
- Test network failure scenarios
- Verify accessibility (keyboard navigation, screen readers)
- Test on mobile devices (responsive design)

### Step 8: Security Review and Polish (0.5-1 hour)
- Verify token is one-time use
- Confirm token expiration is enforced
- Check rate limiting is working
- Ensure no sensitive data in client-side code
- Verify CSRF protection is active
- Review error messages for information leakage
- Test password visibility toggles
- Final styling and UX improvements

---

## Notes and Considerations

### Security Considerations
- Token must be validated server-side only
- One-time use token prevents replay attacks
- Rate limiting prevents brute force attempts
- Generic error messages prevent user enumeration
- Token expiration (1 hour) limits attack window
- CSRF protection required on POST request
- Email parameter prevents token theft scenarios

### UX Considerations
- Clear messaging about password requirements upfront
- Show/hide toggles for password fields improve usability
- Read-only email field provides context
- Automatic redirect after success reduces user steps
- Helpful error messages guide user to resolution
- Link to request new token if current one expired

### Accessibility Requirements
- Proper form labels and ARIA attributes
- Keyboard navigation support
- Screen reader friendly error messages
- Sufficient color contrast for all text
- Focus indicators on interactive elements
- Password visibility toggles accessible via keyboard

### Future Enhancements
- Real-time password strength indicator
- Visual confirmation when passwords match
- More detailed password requirements tooltip
- Suggested strong password generation
- Two-factor authentication option after reset
- Account activity notification after password change

### Integration Points
- Laravel's PasswordBroker for token management
- Email notification system for reset links
- User authentication system for password update
- Session management for flash messages
- Database for token validation and storage

# Verify Email View - Implementation Plan

## 1. Overview

Verify Email view (`VerifyEmail.vue`) provides the interface for users who have registered but not yet verified their email address. This view acts as a gate before users can access the main application features. The view displays the user's current email address, explains the verification requirement, provides a button to resend verification email, and allows logging out if needed.

**Core Features:**
- Display verification requirement message
- Show user's current email address
- Resend verification email functionality
- Rate limiting on resend requests
- Success feedback after resending
- Logout option
- Automatic redirect to dashboard after verification (via email link)
- Clear instructions and status messages

**User Flow:**
1. User registers and is redirected to verify email page
2. User checks email inbox for verification link
3. If email not received, user clicks "Resend Verification Email"
4. System sends new verification email
5. Success message confirms email sent
6. User clicks verification link in email
7. System verifies email and redirects to dashboard

**Implementation Time Estimate:** 5-6 hours

---

## 2. Routing

### Route Definition
**Route Name:** `verification.notice`
**Path:** `/verify-email`
**HTTP Method:** GET (display view), POST (resend email)
**Controller:** `Auth\EmailVerificationNotificationController`
**Middleware:** `auth`, `throttle:6,1` (6 resend attempts per minute)

### Route Parameters
None - user identified from authenticated session

### Inertia Response Structure
```typescript
{
  component: 'Auth/VerifyEmail',
  props: {
    status?: string // Success message if verification email resent
  }
}
```

### Related Routes
- `verification.verify` - Email verification link handler (GET with signature)
- `logout` - User logout
- `dashboard` - Redirect destination after successful verification

---

## 3. Component Structure

### Main Component
**File:** `resources/js/Pages/Auth/VerifyEmail.vue`

**Component Hierarchy:**
```
VerifyEmail.vue (Page Component)
├── AppLayout (Authenticated layout)
│   ├── Head (Page metadata)
│   └── Navigation (Top navigation with logout)
└── Content Container
    ├── Status Message (Success notification after resend)
    ├── Verification Notice (Main message)
    ├── Email Display (Current user email)
    ├── Instructions Text
    ├── Action Buttons Container
    │   ├── Resend Email Button (Primary action)
    │   └── Logout Button (Secondary action)
    └── Help Text (Check spam folder message)
```

**Key Characteristics:**
- Simple informational page with minimal interaction
- Uses authenticated layout (user is logged in but unverified)
- Two main actions: resend email and logout
- Status-driven UI (shows success message after resend)
- No form fields, only action buttons

---

## 4. Component Details

### 4.1 VerifyEmail.vue (Main Component)

**Purpose:**
Main page component that displays email verification requirement, shows user's email, and provides options to resend verification email or logout.

**Props:**
- `status` (string, optional) - Status message after resending verification email

**Emits:**
None - actions handled via Inertia POST and router navigation

**Key Responsibilities:**
- Display verification requirement message clearly
- Show authenticated user's email address
- Provide resend verification email functionality
- Handle rate limiting feedback
- Display success message after resend
- Provide logout option
- Explain next steps to user

**Template Structure:**
- Page title and metadata
- Status message display (if email just resent)
- Main heading: "Verify Your Email Address"
- Explanatory text about verification requirement
- Display of user's email address (from auth user)
- Instructions: "Click the link in the verification email to continue"
- Resend verification email button with loading state
- Secondary text: "Didn't receive the email? Check your spam folder"
- Logout button (secondary styling)
- Help text with support contact (optional)

**Styling Approach:**
- Clean, centered layout
- Large, clear typography for main message
- Email address prominently displayed
- Primary button for resend action
- Secondary/ghost button for logout
- Status message in success color (green)
- Responsive design (mobile-first)
- Accessible color contrast and spacing

---

## 5. Types and Interfaces

### Props Type
```typescript
interface VerifyEmailProps {
  status?: string
}
```

### User Type (from auth)
```typescript
interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
}
```

### Resend Form Type
```typescript
interface ResendForm {
  // Empty - no data needed, user from auth
}
```

---

## 6. State Management

### Component State
**No form data required** - user identification from authenticated session

**UI States:**
- `resending` - Boolean indicating if resend request in progress
- `recentlySent` - Boolean tracking if email recently sent (prevents spam)

### Computed Properties
**User Email:**
- Accessed from `$page.props.auth.user.email`
- Displayed in read-only format

**Show Status:**
- Checks if `status` prop exists
- Conditionally displays success message

### Button States
**Resend Button:**
- Default: "Resend Verification Email"
- Loading: "Sending..." with spinner
- Disabled: When resending or recently sent (60 second cooldown)

**Logout Button:**
- Always enabled
- Posts to logout route

---

## 7. Mock Data / Example Content

### Sample Inertia Props
```typescript
{
  status: 'A new verification link has been sent to your email address.',
  auth: {
    user: {
      id: 1,
      name: 'Jan Kowalski',
      email: 'jan.kowalski@example.com',
      email_verified_at: null
    }
  }
}
```

### Content Text

**Main Heading:**
"Verify Your Email Address"

**Description Text:**
"Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another."

**Email Display:**
"Verification email sent to: jan.kowalski@example.com"

**Instructions:**
"Please check your inbox and click the verification link to activate your account."

**Help Text:**
"Didn't receive the email? Check your spam or junk folder. If you still don't see it, you can request a new verification email."

**Button Labels:**
- Primary: "Resend Verification Email"
- Secondary: "Log Out"

**Success Status Message:**
"A new verification link has been sent to your email address."

**Rate Limit Message:**
"Too many requests. Please wait a moment before trying again."

---

## 8. User Interactions

### Main User Flow

**Step 1: Arrival**
- User redirected here after registration
- Or user attempts to access protected feature without verified email
- Page loads with verification message
- User's email displayed

**Step 2: Check Email**
- User navigates to email inbox
- Looks for verification email from Just In Case
- Checks spam/junk folder if not in inbox

**Step 3a: Email Received - Verification**
- User clicks verification link in email
- Link opens in browser (new tab or same window)
- System validates signed URL
- System marks email as verified
- User redirected to dashboard
- Success message displayed

**Step 3b: Email Not Received - Resend**
- User returns to verification page
- User clicks "Resend Verification Email" button
- Button shows loading state
- New email sent to user's address
- Success message displayed: "A new verification link has been sent"
- Button disabled for 60 seconds to prevent spam
- User checks email again

**Step 4: Alternative - Logout**
- User decides to logout instead
- Clicks "Log Out" button
- Session terminated
- User redirected to login page

### Interactive Elements

**Resend Verification Email Button:**
- Default state: Enabled, primary button styling
- Hover: Slight color change
- Click: Transitions to loading state
- Loading: Shows spinner, text changes to "Sending...", disabled
- After success: Disabled for 60 seconds with countdown timer (optional)
- Rate limited: Shows error if too many requests

**Log Out Button:**
- Secondary/ghost button styling
- Always enabled
- Posts to logout route via Inertia

**Status Message:**
- Appears at top of form after resend
- Green/success color scheme
- Auto-dismisses after 5 seconds (optional)
- Dismissible by user (close button)

---

## 9. Validation and Error Handling

### Server-Side Validation

**User Authentication:**
- Must be authenticated (enforced by middleware)
- Must not already have verified email (redundant check)

**Rate Limiting:**
- Maximum 6 resend requests per minute per user
- Prevents email spam and abuse
- Returns 429 Too Many Requests if exceeded

### Error Scenarios

**User Not Authenticated:**
- Middleware redirects to login page
- Should not occur in normal flow

**Email Already Verified:**
- If user manually navigates here after verification
- Automatically redirect to dashboard
- No error message needed

**Resend Rate Limit Exceeded:**
- Error message: "Too many requests. Please wait before trying again."
- Display as validation error above button
- Button remains disabled until cooldown expires

**Email Send Failure:**
- Error message: "Failed to send verification email. Please try again."
- Log error on server for debugging
- Button returns to enabled state
- User can retry

**Network Error:**
- Timeout after 30 seconds
- Display: "Unable to send email. Please check your connection."
- Button returns to enabled state
- User can retry

**Session Expired:**
- User redirected to login page
- Flash message: "Your session has expired. Please log in again."

### Error Display Strategy
- Errors displayed above action buttons
- Red/error color scheme for visibility
- Clear, actionable error messages
- Errors cleared before new request
- Rate limit errors include wait time if possible

---

## 10. Implementation Steps

### Step 1: Create Routes and Controller (1 hour)
- Define GET route for verification notice page
- Define POST route for resending verification email
- Create EmailVerificationNotificationController
- Implement store method to resend verification email
- Apply auth and throttle middleware
- Configure redirect to dashboard if already verified
- Set up flash message for success status

### Step 2: Create VerifyEmail Component (1 hour)
- Create `resources/js/Pages/Auth/VerifyEmail.vue`
- Define component props (status)
- Set up AppLayout wrapper (authenticated layout)
- Add Head component for page metadata
- Create basic page structure and content
- Set up Tailwind styling

### Step 3: Implement Content Display (0.5 hours)
- Add main heading and description text
- Display user's email from auth prop
- Add instructions and help text
- Style content for readability
- Ensure responsive design

### Step 4: Implement Resend Functionality (1 hour)
- Create resend button with Inertia form
- Configure POST to verification.send route
- Add loading state to button
- Implement button disable during request
- Add optional cooldown timer (60 seconds)

### Step 5: Add Status Message Display (0.5 hours)
- Display success message when status prop present
- Style status message (success color)
- Add optional auto-dismiss after 5 seconds
- Add dismissible close button
- Animate entry and exit

### Step 6: Implement Logout Functionality (0.5 hours)
- Add logout button with secondary styling
- Configure POST to logout route via Inertia
- Ensure proper session termination
- Verify redirect to login page

### Step 7: Add Error Handling (0.5 hours)
- Display validation errors above buttons
- Handle rate limit errors specifically
- Add network error handling
- Implement error clearing before new requests
- Style error messages consistently

### Step 8: Testing and Refinement (1-1.5 hours)
- Test resend functionality with real email
- Verify email verification link works correctly
- Test rate limiting (7+ requests)
- Test logout functionality
- Verify redirect when already verified
- Test with expired session
- Test network failure scenarios
- Verify email display for various lengths
- Test on mobile devices (responsive)
- Verify accessibility (keyboard navigation, screen readers)
- Check spam folder for verification emails
- Final UX and styling polish

---

## Notes and Considerations

### Security Considerations
- Verification links must be signed URLs
- Links should expire after 24 hours
- Rate limiting prevents email spam abuse
- User must be authenticated to access page
- Prevent enumeration by not revealing if email exists

### UX Considerations
- Clear explanation of why verification is required
- Prominent display of email address for user confirmation
- Helpful instructions about checking spam folder
- Resend button easily accessible
- Success feedback after resending email
- Option to logout if wrong email address
- No dead ends - user always has action to take

### Email Delivery Considerations
- Verification emails may take 1-2 minutes to arrive
- Some email providers may flag as spam
- Consider email deliverability best practices
- Queue emails for better performance
- Log email send attempts for debugging

### Accessibility Requirements
- Proper heading hierarchy (h1 for main heading)
- ARIA labels for buttons
- Keyboard navigation support
- Screen reader friendly messages
- Sufficient color contrast
- Focus indicators on interactive elements

### Future Enhancements
- Email change functionality from this page
- Display last sent timestamp
- Progress indicator for cooldown timer
- Alternative verification methods (SMS, authenticator)
- More detailed troubleshooting help
- Link to support/contact form
- Resend count display (e.g., "Email sent 2 times")

### Integration Points
- Laravel's built-in email verification system
- Queue system for sending emails
- Email notification system
- User authentication system
- Session management for flash messages
- Signed URL generation and validation
- Database for email_verified_at timestamp

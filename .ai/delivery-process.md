# Custodianship Expiration Notification Plan

## Objective
- Automate a command that detects custodianships whose `next_trigger_at` timestamp has passed (timer expired), then notifies the appropriate recipients via Laravel's mail channel (Mailpit locally, Mailgun in production), including a human-readable message body and any required attachments.

## Prerequisites
- Ensure mail driver configuration is environment-specific (`MAIL_MAILER=mailgun` in production, `MAIL_MAILER=mailpit` locally) with supporting credentials (`MAILGUN_DOMAIN`, `MAILGUN_SECRET`, `MAIL_FROM_ADDRESS`).
- Ensure queue configuration is set up (`QUEUE_CONNECTION=database` or `redis`) and queue workers are running to process notification jobs.
- Configure Mailgun webhook endpoint: `/webhooks/mailgun` to receive delivery events (delivered, failed, bounced).
  - Set up webhook signature verification for security.
  - Configure webhook to send events: delivered, failed, permanent_fail, complained.
- Confirm notification recipients exist in the `recipients` table with valid email addresses.
- Verify the storage disk for attachments is accessible to both command runtime and queue workers (S3 or local).
- Ensure `custodianships.uuid` is generated on creation (used for attachment download URLs).

## Implementation Steps
- **Define Command**: Add an Artisan console command `notifications:expired-custodianships` that queries custodianship records with `next_trigger_at <= now() AND status = 'active'` and eager-loads related data (`recipients`, `message`, `media`).
- **Compose Notification**: Implement a notification class (e.g., `ExpiredCustodianshipNotification`) that implements `ShouldQueue` to send notifications asynchronously, accepts the custodianship and recipient, composes the email content, generates UUID links for attachments, and handles delivery tracking.
- **Mail Channel**: Leverage Laravel's `mail` notification channel so the same command dispatches through Mailpit locally and Mailgun in production (driver selected via environment configuration).
- **Command Flow**:
  - Validate configuration and bail early with explicit logging if Mailgun credentials are missing.
  - Retrieve custodianships with `next_trigger_at <= now() AND status = 'active'`.
  - For each custodianship, iterate through all recipients:
    - Create a `Delivery` record with status 'pending' for the recipient.
    - Dispatch the notification using `$recipient->notify(new ExpiredCustodianshipNotification($custodianship))` which will be queued.
  - After all notifications are dispatched for a custodianship, update custodianship `status` to 'completed'.
  - Log command execution metrics (custodianships processed, notifications queued).
  - Note: `delivery_status` is computed automatically based on delivery records, no manual update needed.
- **Notification Job Flow** (executed asynchronously by queue worker):
  - Generate UUID link for attachments (if not already generated).
  - Compose email with user-defined content, attachment links, and footer (per PRD REQ-028).
  - Send via mail channel (Mailgun/Mailpit).
  - Capture `mailgun_message_id` from response.
  - Update `Delivery` record with `mailgun_message_id` and status 'sent'.
  - Handle failures:
    - Retry logic: Max 3 attempts with exponential backoff (1min, 5min, 15min) as per PRD REQ-030.
    - After 3 failures: Update `Delivery.status` to 'failed' and log error.
- **Webhook Handling** (for delivery confirmation and bounces):
  - Create webhook endpoint to receive Mailgun events (delivered, failed, bounced).
  - Match event to `Delivery` record via `mailgun_message_id`.
  - Update `Delivery.status` ('delivered', 'failed') and `delivered_at` timestamp.
  - The custodianship's `delivery_status` will automatically update (computed attribute).
  - Send alert email to custodianship owner on bounce/failure (per PRD REQ-031, REQ-038).
  - Log all webhook events for audit.
- **Automated Tests**:
  - Add a feature test (e.g., `tests/Feature/Notifications/ExpiredCustodianshipTest.php`) that fakes notifications, seeds qualifying custodianships, runs the command, and asserts dispatch.
  - Create a command unit test (e.g., `tests/Feature/Console/NotificationsExpiredCustodianshipsCommandTest.php` or `tests/Unit/Console/...`) verifying query filtering, notification usage, and scheduler registration where feasible.
- **Attachments Handling**: Ensure attachments are streamed as binary data; confirm names, MIME types, and sizes respect provider limits (Mailgun production, Mailpit development).
- **Notification Class Implementation**:
  - Implement `ShouldQueue` interface for asynchronous processing.
  - Set `$tries = 3` for automatic retry attempts.
  - Set `$backoff = [60, 300, 900]` for exponential backoff (1min, 5min, 15min) per PRD REQ-030.
  - Implement `failed()` method to update Delivery status to 'failed' after max retries.
  - Use `viaQueues()` to specify queue name (e.g., 'notifications').
  - Track delivery in `via()` method by updating Delivery record before and after send.

## Database Schema & Status Transitions

### Custodianships Table Fields
- `status`: ENUM('draft', 'active', 'completed') - Overall custodianship lifecycle status
  - `draft`: Not yet activated, timer not running
  - `active`: Timer running, can expire
  - `completed`: Timer expired, notifications dispatched (final state)

### Computed Delivery Status (not stored in database)
The `delivery_status` is a computed attribute based on the related `deliveries` records:
- `null`: No deliveries created yet (status = 'draft' or 'active')
- `dispatched`: Notifications queued, all deliveries pending (status = 'completed', all deliveries 'pending')
- `partially_delivered`: Some delivered, some pending (status = 'completed', mixed pending/delivered)
- `delivered`: All delivered successfully (status = 'completed', all deliveries 'delivered')
- `partially_failed`: Some failed, some delivered (status = 'completed', has 'failed' and 'delivered')
- `failed`: All failed (status = 'completed', all deliveries 'failed')

### Computed Delivery Statistics
The model provides real-time delivery statistics via the `delivery_stats` attribute:
- `total`: Total number of recipients/deliveries
- `delivered`: Number of successfully delivered emails
- `failed`: Number of failed deliveries
- `pending`: Number of pending deliveries
- `success_percentage`: Percentage of successful deliveries (0-100)

### Deliveries Table Fields (per recipient)
- `custodianship_id`: FK to custodianships
- `recipient_id`: FK to recipients
- `recipient_email`: Snapshot of email at send time
- `mailgun_message_id`: Message ID from Mailgun API response
- `status`: ENUM('pending', 'delivered', 'failed') - Individual delivery status
- `attempt_number`: Current attempt number (default: 1)
- `max_attempts`: Maximum retry attempts (default: 3)
- `last_retry_at`: Timestamp of last retry attempt
- `next_retry_at`: Timestamp of next scheduled retry
- `error_message`: Error message from last failed attempt
- `delivered_at`: Timestamp when Mailgun confirmed delivery

### Status Transition Flow
1. **Command Execution**: `status = 'active'` AND `next_trigger_at <= now()` found (computed `delivery_status` = `null`)
2. **Before Queuing**: Create Delivery records with status 'pending' (computed `delivery_status` = `null`)
3. **After Dispatching All Notifications**: custodianship `status` → 'completed' (computed `delivery_status` = 'dispatched')
4. **After Send** (in queued job): Individual Delivery status → 'delivered' or 'failed', capture `mailgun_message_id`
5. **Webhook Received**:
   - Success: Delivery status → 'delivered', set `delivered_at` (computed `delivery_status` updates automatically)
   - Bounce/Failure: Delivery status → 'failed' (computed `delivery_status` updates automatically)
6. **Computed Status Examples**:
   - Draft/Active: `delivery_status` = `null`
   - All pending: `delivery_status` = 'dispatched'
   - All delivered: `delivery_status` = 'delivered'
   - Some delivered, some pending: `delivery_status` = 'partially_delivered'
   - Some delivered, some failed: `delivery_status` = 'partially_failed'
   - All failed: `delivery_status` = 'failed'

### Email Content Structure (per PRD REQ-028)
```
Subject: [user_name] sent you an important message - Just In Case

Body:
---
You are receiving this message because [user_name] stopped resetting their timer in the Just In Case application.

[User-defined message content from custodianship_messages.content]

[If attachments exist:]
Download attachments: [URL to /custodianships/{uuid}/download]

---
LEGAL DISCLAIMER: This message was automatically sent by Just In Case.
This is NOT a legal testament or official document.

Learn more: https://justincase.com
```

## Webhook Implementation

### Mailgun Webhook Endpoint
- Route: `POST /webhooks/mailgun`
- Verify webhook signature using Mailgun signing key for security.
- Handle webhook events:
  - `delivered`: Update Delivery status to 'delivered', set `delivered_at`, check if all deliveries complete.
  - `failed` / `permanent_fail`: Update Delivery status to 'failed', send alert email to owner.
  - `complained` / `bounced`: Update Delivery status to 'failed', custodianship `delivery_status` to 'bounced', send alert.
- Extract `message-id` from webhook payload to match against `deliveries.mailgun_message_id`.
- Log all webhook events for audit and debugging.
- Return HTTP 200 response to acknowledge receipt (prevents webhook retries).

### Webhook Security
- Verify Mailgun signature on every request (reject invalid signatures).
- Rate limit webhook endpoint to prevent abuse.
- Log suspicious webhook activity (invalid signatures, unknown message IDs).

## Execution & Automation
- Ensure queue workers are running: `php artisan queue:work` (or use `composer dev` which includes queue listener).
- Run manually for validation: `php artisan notifications:expired-custodianships`.
- Schedule recurring execution via `bootstrap/app.php` (every minute) once validated:
  ```php
  $schedule->command('notifications:expired-custodianships')->everyMinute();
  ```
- Monitor via application logs or a designated channel (e.g., Slack notification on failure).
- Monitor queue status and failed jobs via `php artisan queue:failed` and retry with `php artisan queue:retry`.
- Set up Mailgun webhook endpoint at `/webhooks/mailgun` to receive delivery events.

## Validation Checklist
- [ ] Command queries using correct fields: `next_trigger_at <= now() AND status = 'active'`.
- [ ] Command returns a zero exit code and logs metrics when no records match.
- [ ] Delivery records created with status 'pending' for each recipient before queuing.
- [ ] Computed `delivery_status` returns `null` for draft/active custodianships.
- [ ] Notifications are properly queued for asynchronous processing (one per recipient).
- [ ] Custodianship `status` changes to 'completed' after all notifications are dispatched.
- [ ] Computed `delivery_status` returns 'dispatched' after notifications are queued (all deliveries pending).
- [ ] Queue workers process notification jobs successfully.
- [ ] UUID links for attachments are generated (if attachments exist).
- [ ] Notifications deliver correct subject, body (user content + footer), and attachment links (per PRD REQ-028).
- [ ] Mailgun `message_id` is captured and stored in Delivery record.
- [ ] Delivery record status updates to 'delivered' or 'failed' after send attempt.
- [ ] Retry logic executes: max 3 attempts with exponential backoff (1min, 5min, 15min).
- [ ] After 3 failed retries: Delivery status updates to 'failed' with error logged.
- [ ] Webhook endpoint processes Mailgun events (delivered, failed, bounced).
- [ ] Webhook updates Delivery status and `delivered_at` timestamp correctly.
- [ ] Computed `delivery_status` automatically returns 'delivered' when all deliveries confirmed 'delivered'.
- [ ] Computed `delivery_status` automatically returns 'failed' when all deliveries fail.
- [ ] Computed `delivery_status` automatically returns 'partially_failed' when some succeed and some fail.
- [ ] Computed `delivery_stats` provides accurate counts (total, delivered, failed, pending, success_percentage).
- [ ] Alert emails sent to custodianship owner on bounce/failure (per PRD REQ-038).
- [ ] Attachment integrity verified by recipients (correct file size/content).
- [ ] Errors (invalid addresses, Mailgun failures) surface in logs and alerting system.
- [ ] Failed queue jobs are properly logged and can be retried.
- [ ] Scheduler job executes on expected cadence (every minute) without manual intervention.
- [ ] Feature test covers the end-to-end flow (queue/mail fakes, verification of notification dispatch).
- [ ] Command test asserts filtering logic, notification trigger, and exit codes under success and failure scenarios.

## Planned Tests

### Command Tests
- `tests/Feature/Console/NotificationsExpiredCustodianshipsCommandTest`
  Confirms command queries using `next_trigger_at <= now() AND status = 'active'`, creates Delivery records, queues notifications, and verifies exit codes.
- `tests/Feature/Console/NotificationsExpiredCustodianshipsNoMatchesTest`
  Asserts the command exits successfully when no custodianships have `next_trigger_at <= now()` and that no notifications are sent.
- `tests/Feature/Console/NotificationsExpiredCustodianshipsSchedulerTest`
  Uses `Schedule::fake()` to confirm the command is registered on the scheduler with `everyMinute`.
- `tests/Feature/Console/NotificationsExpiredCustodianshipsLoggingTest`
  Verifies structured logs are emitted for success/failure paths and include custodianship identifiers for observability.

### Notification Tests
- `tests/Feature/Notifications/ExpiredCustodianshipTest`
  Seeds expired custodianships (`next_trigger_at <= now()`), fakes notifications and queue, runs the command, and asserts only expired records dispatch with correct subject/body/attachments.
- `tests/Feature/Notifications/ExpiredCustodianshipQueueTest`
  Verifies that notifications are properly queued for asynchronous processing and that queue jobs are created for each recipient.
- `tests/Feature/Notifications/ExpiredCustodianshipMultipleRecipientsTest`
  Verifies that multiple recipients all receive separate notifications (one per recipient) with the same content and attachments.
- `tests/Unit/Notifications/ExpiredCustodianshipNotificationTest`
  Ensures the notification builds the expected payload, includes UUID attachment links, and uses the correct mail channel configuration.
- `tests/Unit/Notifications/ExpiredCustodianshipMessageBuilderTest` (if helper extracted)
  Checks subject/body rendering logic per PRD REQ-028 (intro, user content, attachment link, footer).

### Delivery Tracking Tests
- `tests/Feature/Notifications/ExpiredCustodianshipDeliveryTrackingTest`
  Confirms that Delivery records are created with status 'pending' before queuing, then updated to 'delivered' or 'failed' after send, with correct `mailgun_message_id`.
- `tests/Feature/Notifications/ExpiredCustodianshipStatusUpdateTest`
  Validates that custodianship status changes to 'completed' immediately after all notifications are dispatched, and computed `delivery_status` returns correct values based on delivery records.
- `tests/Feature/Notifications/ExpiredCustodianshipPartialFailureTest`
  Tests scenario where some recipients succeed and some fail, verifying computed `delivery_status` returns 'partially_failed' while status remains 'completed'.
- `tests/Unit/Models/CustodianshipDeliveryStatusTest`
  Unit tests for computed `delivery_status` attribute covering all status scenarios (null, dispatched, delivered, partially_delivered, partially_failed, failed).
- `tests/Unit/Models/CustodianshipDeliveryStatsTest`
  Unit tests for computed `delivery_stats` attribute verifying accurate counts and percentage calculations.

### Retry Logic Tests
- `tests/Feature/Notifications/ExpiredCustodianshipRetryTest`
  Simulates transient failures and confirms retry logic executes with exponential backoff (1min, 5min, 15min) up to 3 attempts.
- `tests/Feature/Notifications/ExpiredCustodianshipMaxRetriesTest`
  Verifies that after 3 failed attempts, Delivery status changes to 'failed' and no further retries occur.

### Webhook Tests
- `tests/Feature/Webhooks/MailgunWebhookDeliveredTest`
  Simulates Mailgun 'delivered' webhook, verifies Delivery status updates to 'delivered' with `delivered_at` timestamp, and computed `delivery_status` reflects the change.
- `tests/Feature/Webhooks/MailgunWebhookBouncedTest`
  Simulates Mailgun 'bounced' webhook, verifies Delivery status updates to 'failed', computed `delivery_status` updates automatically, and alert email sent to owner.
- `tests/Feature/Webhooks/MailgunWebhookFailedTest`
  Simulates Mailgun 'failed' webhook, verifies Delivery status updates to 'failed', computed `delivery_status` updates automatically, and alert email sent to owner.
- `tests/Feature/Webhooks/MailgunWebhookInvalidMessageIdTest`
  Tests webhook with invalid/unknown message_id, verifies graceful handling and logging.

### Attachment & UUID Tests
- `tests/Feature/Notifications/ExpiredCustodianshipUuidLinkGenerationTest`
  Verifies UUID link is generated for custodianships with attachments and included in email body.
- `tests/Feature/Notifications/ExpiredCustodianshipNoAttachmentsTest`
  Verifies email is sent without attachment links when custodianship has no attachments.
- `tests/Feature/Notifications/ExpiredCustodianshipAttachmentFailureTest`
  Simulates missing or unreadable attachments and ensures the notification handles gracefully with error logging.

### Alert Tests
- `tests/Feature/Notifications/ExpiredCustodianshipBounceAlertTest`
  Verifies alert email is sent to custodianship owner and administrator when bounce occurs (per PRD REQ-038).
- `tests/Feature/Notifications/ExpiredCustodianshipFailureAlertTest`
  Verifies alert email is sent to custodianship owner and administrator when delivery fails (per PRD REQ-038).

## References

### PRD Requirements Alignment
- **REQ-021**: Timer mechanism using `last_reset_at`, `next_trigger_at`, `interval_days`
- **REQ-022**: Cron job checking `next_trigger_at <= now() AND status = 'active'` every minute
- **REQ-027**: Automatic email dispatch on timer expiration, UUID link generation, separate email per recipient
- **REQ-028**: Email content structure (intro, user content, attachment link, footer, disclaimer)
- **REQ-029**: Mailgun integration with webhook tracking for delivery status
- **REQ-030**: Retry logic - max 3 attempts with exponential backoff (1min, 5min, 15min)
- **REQ-031**: Bounce handling via webhooks, status update to delivery_failed
- **REQ-032**: Delivery verification via webhooks, status update to completed
- **REQ-038**: Alert emails to owner and admin on delivery failure/bounce

### Database Schema Alignment (db-plan.md)
- **custodianships table**: `status` (draft/active/completed), `next_trigger_at`, `uuid`
  - Note: `delivery_status` is a computed attribute, not stored in database
- **recipients table**: Email addresses for each custodianship
- **deliveries table**: Per-recipient tracking with `mailgun_message_id`, `status` (pending/delivered/failed), `attempt_number`, `max_attempts`, `last_retry_at`, `next_retry_at`, `error_message`, `delivered_at`
- **custodianship_messages table**: 1:1 relationship storing encrypted message content
- **media table**: Spatie media library for attachment storage (polymorphic relationship)
- **notifications table**: Standard Laravel notifications table (database channel for UI notifications)

### Computed Delivery Status Design
Instead of storing `delivery_status` as a database column, it is computed on-the-fly based on the custodianship's `status` and related `deliveries` records. This approach:
- Eliminates data synchronization issues between custodianships and deliveries tables
- Provides single source of truth (delivery records)
- Enables flexible status logic without database migrations
- Supports rich statistics (counts, percentages) computed in real-time
- Simplifies webhook handling (only update delivery records, status computes automatically)

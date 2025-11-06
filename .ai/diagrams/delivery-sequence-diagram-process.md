# Diagramy Sekwencji - Proces Dostarczania Wiadomości

Ten dokument przedstawia szczegółowe diagramy sekwencji pokazujące chronologiczny przepływ komunikacji między komponentami systemu delivery w aplikacji Just In Case.

## Scenariusz 1: Pomyślne Dostarczenie (Happy Path)

Ten diagram pokazuje standardowy przepływ od wykrycia wygasłego timera do pomyślnego dostarczenia emaila.

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Scheduler
    participant Cmd as NotificationsExpiredCustodianshipsCommand
    participant DB as Database
    participant Queue as Queue (Redis/Database)
    participant Job as SendCustodianshipNotificationJob
    participant Notif as ExpiredCustodianshipNotification
    participant Mailgun as Mailgun API
    participant Webhook as MailgunWebhookController
    participant Recipient as Odbiorca

    Note over Scheduler,Recipient: Faza 1: Wykrywanie i Dispatch

    Scheduler->>Cmd: Execute (co minutę)
    Cmd->>DB: Query: next_trigger_at <= now() AND status = 'active'
    DB-->>Cmd: Return: Custodianship (z Recipients)

    loop Dla każdego Recipient
        Cmd->>Queue: Dispatch SendCustodianshipNotificationJob
    end

    Cmd->>DB: Update Custodianship: status = 'completed'
    DB-->>Cmd: OK
    Cmd-->>Scheduler: Exit 0 (success)

    Note over Scheduler,Recipient: Faza 2: Queue Processing

    Queue->>Job: Execute Job
    Job->>DB: Check if Delivery exists for Recipient
    DB-->>Job: Not found
    Job->>DB: Create Delivery (status: pending, attempt_number: 1)
    DB-->>Job: Delivery created
    Job->>Job: Generate Mailgun Message-ID (UUID)
    Job->>DB: Update Delivery: mailgun_message_id
    DB-->>Job: OK

    Job->>Notif: Send notification to Recipient

    Note over Scheduler,Recipient: Faza 3: Email Composition & Send

    Notif->>DB: Load CustodianshipMessage (encrypted)
    DB-->>Notif: Message content
    Notif->>DB: Load Media (attachments)
    DB-->>Notif: Attachments list
    Notif->>Notif: Build email body:<br/>- User message<br/>- UUID download link<br/>- Disclaimer
    Notif->>Mailgun: POST /messages (with Mailgun Message-ID)
    Mailgun-->>Notif: 200 OK (message queued)
    Notif-->>Job: Email sent
    Job-->>Queue: Job completed

    Note over Scheduler,Recipient: Faza 4: Email Delivery

    Mailgun->>Recipient: Deliver email
    Recipient-->>Mailgun: Accept email

    Note over Scheduler,Recipient: Faza 5: Webhook Confirmation

    Mailgun->>Webhook: POST /webhooks/mailgun<br/>event: delivered<br/>message-id: UUID
    Webhook->>Webhook: Verify HMAC signature
    Webhook->>DB: Find Delivery by mailgun_message_id
    DB-->>Webhook: Delivery found
    Webhook->>DB: Update Delivery:<br/>status = 'delivered'<br/>delivered_at = now()
    DB-->>Webhook: OK
    Webhook->>DB: Check all Deliveries for Custodianship
    DB-->>Webhook: Delivery stats
    Webhook->>DB: Custodianship.updateDeliveryStatus()
    Note over Webhook: delivery_status computed:<br/>'delivered' (all delivered)
    Webhook-->>Mailgun: 200 OK
```

## Scenariusz 2: Temporary Failure z Retry Logic

Ten diagram pokazuje przepływ gdy email nie zostaje dostarczony z powodu błędu przejściowego (5xx) i następują retry z exponential backoff.

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Scheduler
    participant Cmd1 as NotificationsExpiredCustodianshipsCommand
    participant Queue as Queue
    participant Job as SendCustodianshipNotificationJob
    participant Notif as ExpiredCustodianshipNotification
    participant Mailgun as Mailgun API
    participant Webhook as MailgunWebhookController
    participant DB as Database
    participant Cmd2 as ProcessPendingRetriesCommand

    Note over Scheduler,Cmd2: Attempt 1: Initial Send

    Scheduler->>Cmd1: Execute
    Cmd1->>Queue: Dispatch Job
    Queue->>Job: Execute
    Job->>DB: Create Delivery (attempt_number: 1, status: pending)
    Job->>Notif: Send notification
    Notif->>Mailgun: POST /messages
    Mailgun-->>Notif: 200 OK

    Note over Scheduler,Cmd2: Mailgun Processing Failure

    Mailgun->>Webhook: POST /webhooks/mailgun<br/>event: failed<br/>error: 550 (temporary)
    Webhook->>Webhook: Verify signature
    Webhook->>DB: Find Delivery by message_id
    Webhook->>Webhook: handleFailed()<br/>Check status code 500-599
    Note over Webhook: Temporary failure detected
    Webhook->>DB: delivery.fail(error, retry: true)<br/>Calculate next_retry_at = now() + 3600s (1h)
    DB-->>Webhook: Delivery updated:<br/>status: pending<br/>next_retry_at: +1h
    Webhook-->>Mailgun: 200 OK

    Note over Scheduler,Cmd2: Wait 1 hour...

    Scheduler->>Cmd2: Execute ProcessPendingRetriesCommand
    Cmd2->>DB: Query: next_retry_at <= now()<br/>AND status = pending<br/>AND attempt_number < max_attempts
    DB-->>Cmd2: Return: Delivery (attempt_number: 1)
    Cmd2->>Queue: Re-dispatch SendCustodianshipNotificationJob
    Cmd2-->>Scheduler: Exit 0

    Note over Scheduler,Cmd2: Attempt 2: Retry

    Queue->>Job: Execute Job (retry)
    Job->>DB: Find existing Delivery
    DB-->>Job: Delivery found (attempt_number: 1)
    Job->>DB: Update Delivery:<br/>increment attempt_number to 2<br/>last_retry_at = now()
    Job->>Notif: Send notification
    Notif->>Mailgun: POST /messages
    Mailgun-->>Notif: 200 OK

    Note over Scheduler,Cmd2: Mailgun Success This Time

    Mailgun->>Webhook: POST /webhooks/mailgun<br/>event: delivered
    Webhook->>DB: Update Delivery:<br/>status = 'delivered'<br/>delivered_at = now()
    Webhook->>DB: updateDeliveryStatus()
    Webhook-->>Mailgun: 200 OK
```

## Scenariusz 3: Hard Bounce (Permanent Failure)

Ten diagram pokazuje przepływ gdy email nie może być dostarczony z powodu nieprawidłowego adresu (hard bounce).

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Scheduler
    participant Cmd as NotificationsExpiredCustodianshipsCommand
    participant Queue as Queue
    participant Job as SendCustodianshipNotificationJob
    participant Mailgun as Mailgun API
    participant Webhook as MailgunWebhookController
    participant DB as Database
    participant AlertNotif as CustodianshipOwnerAlert
    participant Owner as Właściciel Custodianship

    Scheduler->>Cmd: Execute
    Cmd->>Queue: Dispatch Job
    Queue->>Job: Execute
    Job->>DB: Create Delivery (status: pending, attempt_number: 1)
    Job->>Mailgun: POST /messages
    Mailgun-->>Job: 200 OK (queued)

    Note over Mailgun: Mailgun próbuje dostarczyć email

    Mailgun->>Mailgun: Email bounced:<br/>Invalid recipient address

    Note over Webhook: Webhook Event: Bounced

    Mailgun->>Webhook: POST /webhooks/mailgun<br/>event: bounced<br/>reason: invalid_recipient
    Webhook->>Webhook: Verify signature
    Webhook->>DB: Find Delivery by message_id
    DB-->>Webhook: Delivery found
    Webhook->>Webhook: handleBounced()
    Note over Webhook: Permanent failure:<br/>No retries for bounces
    Webhook->>DB: delivery.fail(error, retry: false)<br/>status = 'failed'<br/>error_message = reason
    DB-->>Webhook: Delivery updated

    Note over Webhook: Send Owner Alert

    Webhook->>DB: delivery.sendOwnerAlert('bounced')
    DB->>Queue: Dispatch CustodianshipOwnerAlert
    Queue->>AlertNotif: Execute notification
    AlertNotif->>Owner: Email: "Delivery Failed - Bounced"<br/>- Recipient email<br/>- Error reason<br/>- Custodianship details
    Owner-->>AlertNotif: Receive alert

    Webhook->>DB: updateDeliveryStatus()
    Note over Webhook: delivery_status computed:<br/>depends on other deliveries
    Webhook-->>Mailgun: 200 OK
```

## Scenariusz 4: Stale Delivery Check

Ten diagram pokazuje przepływ wykrywania i oznaczania "zawisłych" delivery rekordów jako failed.

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Scheduler
    participant Cmd as CheckStaleDeliveriesCommand
    participant DB as Database
    participant Queue as Queue
    participant AlertNotif as CustodianshipOwnerAlert
    participant Owner as Właściciel Custodianship

    Note over Scheduler,Owner: Delivery została utworzona 3h temu<br/>i utknęła w status: pending<br/>bez next_retry_at

    Scheduler->>Cmd: Execute (co minutę)
    Cmd->>DB: Query:<br/>status = 'pending'<br/>AND next_retry_at IS NULL<br/>AND created_at < now() - 7200s (2h)
    DB-->>Cmd: Return: Stale Delivery (pending 3h)

    Note over Cmd: Wykryto zawieszoną delivery

    loop Dla każdej stale delivery
        Cmd->>DB: delivery.fail('Stale delivery timeout', retry: false)
        DB->>DB: Update:<br/>status = 'failed'<br/>error_message = 'Stale delivery timeout'

        Cmd->>DB: delivery.sendOwnerAlert('stale')
        DB->>Queue: Dispatch CustodianshipOwnerAlert

        Cmd->>DB: custodianship.updateDeliveryStatus()
        Note over DB: Przelicz delivery_status<br/>na podstawie wszystkich deliveries
    end

    Cmd-->>Scheduler: Exit 0 (success)

    Note over Queue,Owner: Asynchroniczne wysłanie alertu

    Queue->>AlertNotif: Execute notification
    AlertNotif->>Owner: Email: "Delivery Failed - Stale"<br/>- Recipient email<br/>- Time pending<br/>- Custodianship details
    Owner-->>AlertNotif: Receive alert
```

## Scenariusz 5: Multiple Recipients - Partial Failure

Ten diagram pokazuje custodianship z 2 odbiorcami, gdzie jeden jest dostarczony pomyślnie, a drugi fails.

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Scheduler
    participant Cmd as NotificationsExpiredCustodianshipsCommand
    participant DB as Database
    participant Queue as Queue
    participant Job as SendCustodianshipNotificationJob
    participant Mailgun as Mailgun API
    participant Webhook as MailgunWebhookController
    participant Rec1 as Odbiorca 1 (valid)
    participant Rec2 as Odbiorca 2 (invalid)

    Note over Scheduler,Rec2: Custodianship ma 2 Recipients

    Scheduler->>Cmd: Execute
    Cmd->>DB: Query wygasłych custodianships
    DB-->>Cmd: Custodianship z 2 Recipients

    Note over Cmd: Loop przez Recipients

    Cmd->>Queue: Dispatch Job dla Recipient 1
    Cmd->>Queue: Dispatch Job dla Recipient 2
    Cmd->>DB: Update Custodianship: status = 'completed'
    Cmd-->>Scheduler: Exit 0

    Note over Queue,Rec2: Parallel Job Execution

    par Job dla Recipient 1
        Queue->>Job: Execute Job
        Job->>DB: Create Delivery 1 (pending)
        Job->>Mailgun: POST /messages (Recipient 1)
        Mailgun-->>Job: 200 OK
        Mailgun->>Rec1: Deliver email
        Rec1-->>Mailgun: Accept
        Mailgun->>Webhook: event: delivered
        Webhook->>DB: Update Delivery 1: status = 'delivered'
    and Job dla Recipient 2
        Queue->>Job: Execute Job
        Job->>DB: Create Delivery 2 (pending)
        Job->>Mailgun: POST /messages (Recipient 2)
        Mailgun-->>Job: 200 OK
        Mailgun->>Rec2: Attempt delivery
        Rec2-->>Mailgun: Bounce (invalid)
        Mailgun->>Webhook: event: bounced
        Webhook->>DB: Update Delivery 2: status = 'failed'
        Webhook->>Queue: Dispatch CustodianshipOwnerAlert
    end

    Note over DB: Calculate delivery_status

    Webhook->>DB: custodianship.updateDeliveryStatus()
    DB->>DB: Count deliveries:<br/>- Delivery 1: delivered<br/>- Delivery 2: failed
    Note over DB: delivery_status computed:<br/>'partially_failed'<br/>(1 delivered, 1 failed)

    Webhook-->>Mailgun: 200 OK
```

## Scenariusz 6: Max Retries Exhausted

Ten diagram pokazuje pełny cykl retry aż do wyczerpania wszystkich 3 prób.

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Scheduler
    participant Cmd1 as NotificationsExpiredCustodianshipsCommand
    participant Cmd2 as ProcessPendingRetriesCommand
    participant Queue as Queue
    participant Job as SendCustodianshipNotificationJob
    participant Mailgun as Mailgun API
    participant Webhook as MailgunWebhookController
    participant DB as Database
    participant AlertQueue as Alert Queue
    participant Owner as Właściciel

    Note over Scheduler,Owner: Attempt 1 (Initial)

    Scheduler->>Cmd1: Execute
    Cmd1->>Queue: Dispatch Job
    Queue->>Job: Execute
    Job->>DB: Create Delivery (attempt: 1, max: 3)
    Job->>Mailgun: POST /messages
    Mailgun-->>Job: 200 OK
    Mailgun->>Webhook: event: failed (5xx)
    Webhook->>DB: fail(retry: true)<br/>next_retry_at = now() + 1h<br/>status: pending

    Note over Scheduler,Owner: Wait 1 hour...

    Note over Scheduler,Owner: Attempt 2 (1st Retry)

    Scheduler->>Cmd2: ProcessPendingRetriesCommand
    Cmd2->>DB: Query ready for retry
    DB-->>Cmd2: Delivery (attempt: 1)
    Cmd2->>Queue: Re-dispatch Job
    Queue->>Job: Execute
    Job->>DB: Update: attempt_number = 2
    Job->>Mailgun: POST /messages
    Mailgun-->>Job: 200 OK
    Mailgun->>Webhook: event: failed (5xx)
    Webhook->>DB: fail(retry: true)<br/>next_retry_at = now() + 1d<br/>status: pending

    Note over Scheduler,Owner: Wait 1 day...

    Note over Scheduler,Owner: Attempt 3 (2nd Retry - Last)

    Scheduler->>Cmd2: ProcessPendingRetriesCommand
    Cmd2->>DB: Query ready for retry
    DB-->>Cmd2: Delivery (attempt: 2)
    Cmd2->>Queue: Re-dispatch Job
    Queue->>Job: Execute
    Job->>DB: Update: attempt_number = 3
    Job->>Mailgun: POST /messages
    Mailgun-->>Job: 200 OK
    Mailgun->>Webhook: event: failed (5xx)
    Webhook->>DB: fail(retry: true)<br/>Check: attempt_number (3) >= max_attempts (3)

    Note over Webhook: Max retries exhausted

    Webhook->>DB: Update Delivery:<br/>status = 'failed'<br/>error_message<br/>NO next_retry_at
    Webhook->>DB: sendOwnerAlert('failed')
    DB->>AlertQueue: Dispatch CustodianshipOwnerAlert
    AlertQueue->>Owner: Email: "All Retries Exhausted"<br/>- 3 attempts made<br/>- Last error<br/>- Recipient email
    Webhook->>DB: updateDeliveryStatus()
    Note over DB: delivery_status computed<br/>based on all deliveries
```

## Legenda dla Diagramów Sekwencji

### Uczestnicy (Participants)
- **Scheduler** - Laravel Task Scheduler (bootstrap/app.php)
- **Commands** - Artisan commands (NotificationsExpiredCustodianshipsCommand, ProcessPendingRetriesCommand, CheckStaleDeliveriesCommand)
- **Queue** - System kolejkowy (Redis/Database)
- **Job** - SendCustodianshipNotificationJob
- **Notif** - ExpiredCustodianshipNotification
- **AlertNotif** - CustodianshipOwnerAlert
- **Mailgun** - Mailgun API (mail provider)
- **Webhook** - MailgunWebhookController
- **DB** - Database (SQLite/MySQL)
- **Recipient** - Odbiorca wiadomości
- **Owner** - Właściciel custodianship

### Kluczowe Interakcje
1. **Synchroniczne** - Strzałki ciągłe (→)
2. **Asynchroniczne** - Strzałki przerywane (-->)
3. **Loop** - Pętla przez elementy (Recipients, Deliveries)
4. **Par** - Równoległe wykonanie (multiple recipients)
5. **Note** - Komentarze i opisy faz

### Retry Intervals
- **1st retry**: 3600s = 1 godzina
- **2nd retry**: 86400s = 1 dzień
- **3rd retry**: 604800s = 1 tydzień
- **Max attempts**: 3

### Stale Delivery Threshold
- **Timeout**: 7200s = 2 godziny
- **Condition**: status = pending AND next_retry_at IS NULL AND created_at < now() - 2h

## Pliki Źródłowe

### Commands
- `app/Console/Commands/NotificationsExpiredCustodianshipsCommand.php`
- `app/Console/Commands/ProcessPendingRetriesCommand.php`
- `app/Console/Commands/CheckStaleDeliveriesCommand.php`
- `app/Console/Commands/CleanupTemporaryAttachments.php`

### Jobs & Notifications
- `app/Jobs/SendCustodianshipNotificationJob.php`
- `app/Notifications/ExpiredCustodianshipNotification.php`
- `app/Notifications/CustodianshipOwnerAlert.php`

### Controllers
- `app/Http/Controllers/MailgunWebhookController.php`

### Models
- `app/Models/Custodianship.php`
- `app/Models/Delivery.php`
- `app/Models/Recipient.php`
- `app/Models/CustodianshipMessage.php`

### Configuration
- `config/custodianship.php` - Retry intervals, thresholds, attachment limits
- `bootstrap/app.php` - Scheduler configuration

### Routes
- `routes/web.php` - Webhook endpoint: `POST /webhooks/mailgun`

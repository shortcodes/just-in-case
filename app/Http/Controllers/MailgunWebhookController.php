<?php

namespace App\Http\Controllers;

use App\Exceptions\MailgunWebhookException;
use App\Models\Delivery;
use App\Notifications\CustodianshipOwnerAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailgunWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (! $this->verifyWebhookSignature($request)) {
            throw new MailgunWebhookException(
                'Invalid Mailgun webhook signature',
                [
                    'ip' => $request->ip(),
                    'timestamp' => $request->input('signature.timestamp'),
                ],
                'Invalid signature',
                403
            );
        }

        $eventData = $request->input('event-data', []);
        $event = $eventData['event'] ?? null;
        $messageId = $eventData['message']['headers']['message-id'] ?? null;

        if (! $messageId) {
            throw new MailgunWebhookException(
                'Mailgun webhook received without message ID',
                [
                    'event' => $event,
                    'payload' => $eventData,
                ],
                'No message ID',
                400
            );
        }

        Log::info('Mailgun webhook received', [
            'event' => $event,
            'message_id' => $messageId,
        ]);

        $delivery = Delivery::where('mailgun_message_id', $messageId)->first();

        if (! $delivery) {
            throw new MailgunWebhookException(
                'Mailgun webhook received for unknown delivery',
                [
                    'event' => $event,
                    'message_id' => $messageId,
                ],
                'Delivery not found',
                200
            );
        }

        $this->processWebhookEvent($delivery, $event, $eventData);

        return response()->json(['message' => 'Webhook processed'], 200);
    }

    protected function verifyWebhookSignature(Request $request): bool
    {
        $signingKey = config('services.mailgun.webhook_signing_key');

        if (! $signingKey) {
            return true;
        }

        $timestamp = $request->input('signature.timestamp');
        $token = $request->input('signature.token');
        $signature = $request->input('signature.signature');

        if (! $timestamp || ! $token || ! $signature) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $timestamp.$token, $signingKey);

        return hash_equals($computedSignature, $signature);
    }

    protected function processWebhookEvent(Delivery $delivery, string $event, array $eventData): void
    {
        match ($event) {
            'delivered' => $this->handleDelivered($delivery, $eventData),
            'failed', 'permanent_fail' => $this->handleFailed($delivery, $eventData),
            'complained', 'bounced' => $this->handleBounced($delivery, $eventData),
            default => Log::info('Unhandled Mailgun webhook event', [
                'event' => $event,
                'delivery_id' => $delivery->id,
            ]),
        };
    }

    protected function handleDelivered(Delivery $delivery, array $eventData): void
    {
        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    protected function handleFailed(Delivery $delivery, array $eventData): void
    {
        $delivery->update([
            'status' => 'failed',
        ]);

        $errorMessage = $eventData['delivery-status']['message'] ?? 'Unknown error';

        $this->sendAlertToOwner($delivery, 'failed', $errorMessage);
    }

    protected function handleBounced(Delivery $delivery, array $eventData): void
    {
        $delivery->update([
            'status' => 'failed',
        ]);

        $errorMessage = $eventData['delivery-status']['message'] ?? 'Bounced';

        $this->sendAlertToOwner($delivery, 'bounced', $errorMessage);
    }

    protected function sendAlertToOwner(Delivery $delivery, string $type, string $errorMessage): void
    {
        $custodianship = $delivery->custodianship;

        if ($custodianship && $custodianship->user) {
            $custodianship->user->notify(new CustodianshipOwnerAlert(
                $custodianship,
                $delivery,
                $type,
                $errorMessage
            ));
        }
    }
}

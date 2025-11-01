<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class MailgunWebhookException extends Exception
{
    public function __construct(
        protected string $logMessage,
        protected array $context = [],
        string $responseMessage = 'Webhook processing failed',
        int $code = 400
    ) {
        parent::__construct($responseMessage, $code);
    }

    public function report(): void
    {
        Log::warning($this->logMessage, $this->context);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['error' => $this->getMessage()], $this->code);
    }
}

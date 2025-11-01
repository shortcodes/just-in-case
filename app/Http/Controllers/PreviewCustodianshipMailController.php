<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewCustodianshipMailRequest;
use App\Models\Custodianship;
use App\Models\Recipient;
use App\Notifications\ExpiredCustodianshipNotification;
use Illuminate\Http\Response;

class PreviewCustodianshipMailController extends Controller
{
    public function __invoke(PreviewCustodianshipMailRequest $request, Custodianship $custodianship): Response
    {
        $custodianship->load(['user', 'message']);

        $recipient = $custodianship->recipients()->first() ?? new Recipient([
            'email' => 'recipient@example.com',
            'name' => 'Recipient Name',
        ]);

        $notification = new ExpiredCustodianshipNotification($custodianship, $recipient);

        $mailMessage = $notification->toMail($recipient);

        return response($mailMessage->render());
    }
}

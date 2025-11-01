<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetCustodianshipRequest;
use App\Models\Custodianship;
use DateInterval;
use Illuminate\Http\RedirectResponse;

class ResetCustodianshipController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ResetCustodianshipRequest $request, Custodianship $custodianship): RedirectResponse
    {
        $now = now();
        $interval = new DateInterval($custodianship->interval);

        $custodianship->update([
            'last_reset_at' => $now,
            'next_trigger_at' => $now->copy()->add($interval),
        ]);

        $custodianship->resets()->create([
            'user_id' => $request->user()->id,
            'reset_method' => 'manual_button',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => $now,
        ]);

        return back();
    }
}

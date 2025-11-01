<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivateCustodianshipRequest;
use App\Models\Custodianship;
use DateInterval;
use Illuminate\Http\RedirectResponse;

class ActivateCustodianshipController extends Controller
{
    public function __invoke(ActivateCustodianshipRequest $request, Custodianship $custodianship): RedirectResponse
    {
        $now = now();
        $interval = new DateInterval($custodianship->interval);

        $custodianship->update([
            'status' => 'active',
            'last_reset_at' => $now,
            'next_trigger_at' => $now->copy()->add($interval),
            'activated_at' => $now,
        ]);

        return back()->with('success', 'Custodianship activated successfully.');
    }
}

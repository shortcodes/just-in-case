<?php

namespace App\Http\Controllers;

use App\Enums\IntervalUnit;
use App\Http\Requests\DeleteCustodianshipRequest;
use App\Http\Requests\EditCustodianshipRequest;
use App\Http\Requests\ShowCustodianshipRequest;
use App\Http\Requests\StoreCustodianshipRequest;
use App\Http\Requests\UpdateCustodianshipRequest;
use App\Http\Resources\CustodianshipCollectionResource;
use App\Http\Resources\CustodianshipResource;
use App\Http\Resources\UserResource;
use App\Models\Custodianship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CustodianshipController extends Controller
{
    public function index(Request $request): Response
    {
        $custodianships = $request->user()
            ->custodianships()
            ->with('message')
            ->withCount('recipients')
            ->orderByDefault()
            ->get();

        return Inertia::render('Custodianships/Index', [
            'user' => UserResource::make($request->user())->resolve(),
            'custodianships' => CustodianshipCollectionResource::collection($custodianships)->resolve(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Custodianships/Form', [
            'user' => UserResource::make($request->user())->resolve(),
            'intervalUnits' => IntervalUnit::toArray(),
        ]);
    }

    public function store(StoreCustodianshipRequest $request): RedirectResponse
    {
        $custodianship = DB::transaction(function () use ($request) {
            $intervalUnit = IntervalUnit::from($request->validated('intervalUnit'));
            $interval = $intervalUnit->toIso8601($request->validated('intervalValue'));

            $custodianship = $request->user()->custodianships()->create([
                'name' => $request->validated('name'),
                'interval' => $interval,
            ]);

            if ($messageContent = $request->validated('messageContent')) {
                $custodianship->message()->create([
                    'content' => $messageContent,
                ]);
            }

            foreach ($request->validated('recipients') as $email) {
                $custodianship->recipients()->create([
                    'email' => $email,
                ]);
            }

            return $custodianship;
        });

        return redirect()
            ->route('custodianships.show', $custodianship)
            ->with('success', 'Custodianship created successfully.');
    }

    public function show(ShowCustodianshipRequest $request, Custodianship $custodianship): Response
    {
        $custodianship->load([
            'recipients',
            'message',
            'resets',
            'user',
        ]);

        return Inertia::render('Custodianships/Show', [
            'user' => UserResource::make($request->user())->resolve(),
            'custodianship' => CustodianshipResource::make($custodianship)->resolve(),
            'resetHistory' => $custodianship->resets()
                ->latest()
                ->take(20)
                ->get()
                ->map(fn ($reset) => [
                    'id' => $reset->id,
                    'resetMethod' => $reset->reset_method,
                    'ipAddress' => $reset->ip_address,
                    'userAgent' => $reset->user_agent,
                    'createdAt' => $reset->created_at->toISOString(),
                ]),
        ]);
    }

    public function edit(EditCustodianshipRequest $request, Custodianship $custodianship): Response
    {
        $custodianship->load([
            'recipients',
            'message',
        ]);

        return Inertia::render('Custodianships/Form', [
            'user' => UserResource::make($request->user())->resolve(),
            'custodianship' => CustodianshipResource::make($custodianship)->resolve(),
            'intervalUnits' => IntervalUnit::toArray(),
        ]);
    }

    public function update(UpdateCustodianshipRequest $request, Custodianship $custodianship): RedirectResponse
    {
        $custodianship = DB::transaction(function () use ($request, $custodianship) {
            $intervalUnit = IntervalUnit::from($request->validated('intervalUnit'));
            $interval = $intervalUnit->toIso8601($request->validated('intervalValue'));

            $custodianship->update([
                'name' => $request->validated('name'),
                'interval' => $interval,
            ]);

            if ($request->has('messageContent')) {
                $messageContent = $request->validated('messageContent');

                if ($custodianship->message) {
                    $custodianship->message->update(['content' => $messageContent]);
                } else {
                    $custodianship->message()->create(['content' => $messageContent]);
                }
            }

            $existingEmails = $custodianship->recipients->pluck('email')->toArray();
            $newEmails = array_filter($request->validated('recipients', []));

            $emailsToRemove = array_diff($existingEmails, $newEmails);
            if (! empty($emailsToRemove)) {
                $custodianship->recipients()->whereIn('email', $emailsToRemove)->delete();
            }

            $emailsToAdd = array_diff($newEmails, $existingEmails);
            foreach ($emailsToAdd as $email) {
                $custodianship->recipients()->create(['email' => $email]);
            }

            return $custodianship;
        });

        return redirect()
            ->route('custodianships.show', $custodianship)
            ->with('success', 'Custodianship updated successfully.');
    }

    public function destroy(DeleteCustodianshipRequest $request, Custodianship $custodianship): RedirectResponse
    {
        $custodianship->delete();

        return redirect()
            ->route('custodianships.index')
            ->with('success', 'Custodianship deleted successfully.');
    }
}

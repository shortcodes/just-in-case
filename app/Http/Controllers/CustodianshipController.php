<?php

namespace App\Http\Controllers;

use App\Enums\IntervalUnit;
use App\Http\Requests\ShowCustodianshipRequest;
use App\Http\Requests\StoreCustodianshipRequest;
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
        return Inertia::render('Custodianships/Create', [
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

    public function edit($id): Response
    {
        return Inertia::render('Custodianships/Edit');
    }
}

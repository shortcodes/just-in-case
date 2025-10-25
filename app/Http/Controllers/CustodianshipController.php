<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustodianshipController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Custodianships/Index', [
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'emailVerified' => auth()->user()->email_verified_at !== null,
                'emailVerifiedAt' => auth()->user()->email_verified_at?->toISOString(),
                'createdAt' => auth()->user()->created_at->toISOString(),
            ],
            'custodianships' => [],
        ]);
    }

    public function show($id): Response
    {
        return Inertia::render('Custodianships/Show');
    }

    public function edit($id): Response
    {
        return Inertia::render('Custodianships/Edit');
    }

    public function reset(Request $request, $id)
    {
        // TODO: Implement reset logic
        return back();
    }
}

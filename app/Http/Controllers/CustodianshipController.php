<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustodianshipCollectionResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
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
        $intervals = [
            ['value' => 'P30D', 'label' => '30 days', 'days' => 30],
            ['value' => 'P60D', 'label' => '60 days', 'days' => 60],
            ['value' => 'P90D', 'label' => '90 days', 'days' => 90],
            ['value' => 'P180D', 'label' => '180 days', 'days' => 180],
            ['value' => 'P365D', 'label' => '1 year', 'days' => 365],
        ];

        return Inertia::render('Custodianships/Create', [
            'user' => UserResource::make($request->user())->resolve(),
            'intervals' => $intervals,
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

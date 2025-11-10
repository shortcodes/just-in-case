<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms_accepted' => ['required', function ($attribute, $value, $fail) {
                if (! in_array($value, [true, 'true', 1, '1', 'on', 'yes'], true)) {
                    $fail(__('validation.accepted', ['attribute' => __('validation.attributes.terms_accepted')]));
                }
            }],
            'not_testament_acknowledged' => ['required', function ($attribute, $value, $fail) {
                if (! in_array($value, [true, 'true', 1, '1', 'on', 'yes'], true)) {
                    $fail(__('validation.accepted', ['attribute' => __('validation.attributes.not_testament_acknowledged')]));
                }
            }],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('custodianships.index', absolute: false));
    }
}

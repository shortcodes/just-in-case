<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms_accepted' => true,
            'not_testament_acknowledged' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('custodianships.index', absolute: false));
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms_accepted' => false,
            'not_testament_acknowledged' => true,
        ]);

        $response->assertSessionHasErrors(['terms_accepted']);
        $this->assertGuest();
    }

    public function test_registration_requires_legal_disclaimer_acknowledgement(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms_accepted' => true,
            'not_testament_acknowledged' => false,
        ]);

        $response->assertSessionHasErrors(['not_testament_acknowledged']);
        $this->assertGuest();
    }

    public function test_registration_requires_both_checkboxes(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms_accepted' => false,
            'not_testament_acknowledged' => false,
        ]);

        $response->assertSessionHasErrors(['terms_accepted', 'not_testament_acknowledged']);
        $this->assertGuest();
    }

    public function test_registration_fails_without_checkboxes_in_request(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['terms_accepted', 'not_testament_acknowledged']);
        $this->assertGuest();
    }
}

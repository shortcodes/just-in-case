<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewCustodianshipMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_custodianship_mail_preview(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $response = $this->get(route('custodianships.preview', $custodianship));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_preview_their_own_custodianship_mail(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $custodianship = Custodianship::factory()->for($user)->create();

        $custodianship->message()->create([
            'content' => 'This is a test message',
        ]);

        $custodianship->recipients()->create([
            'email' => 'recipient@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.preview', $custodianship));

        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertSee(__('Here is the message dedicated for you:'));
        $response->assertSee('This is a test message');
        $response->assertSee(__('Legal Disclaimer'), false);
        $response->assertSee(__('This is NOT a legal testament or official document.'), false);
    }

    public function test_user_cannot_preview_another_users_custodianship_mail(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user1)->create();

        $response = $this->actingAs($user2)->get(route('custodianships.preview', $custodianship));

        $response->assertForbidden();
    }

    public function test_preview_works_without_recipients(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $custodianship->message()->create([
            'content' => 'Test message',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.preview', $custodianship));

        $response->assertOk();
        $response->assertSee('Test message');
    }

    public function test_preview_includes_styled_disclaimer(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $custodianship->message()->create([
            'content' => 'Test message',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.preview', $custodianship));

        $response->assertOk();
        $response->assertSee('class="disclaimer-footer"', false);
        $response->assertSee(__('Resources'), false);
        $response->assertSee(__('Legal Disclaimer'), false);
        $response->assertSee(__('Visit Website'), false);
    }

    public function test_preview_uses_polish_translations(): void
    {
        app()->setLocale('pl');

        $user = User::factory()->create(['name' => 'Jan Kowalski']);
        $custodianship = Custodianship::factory()->for($user)->create();

        $custodianship->message()->create([
            'content' => 'Testowa wiadomość',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.preview', $custodianship));

        $response->assertOk();
        $response->assertSee('Jan Kowalski');
        $response->assertSee('Oto wiadomość dedykowana dla Ciebie:');
        $response->assertSee('Testowa wiadomość');
        $response->assertSee('Zasoby', false);
        $response->assertSee('Informacja prawna', false);
        $response->assertSee('Odwiedź stronę', false);
    }
}

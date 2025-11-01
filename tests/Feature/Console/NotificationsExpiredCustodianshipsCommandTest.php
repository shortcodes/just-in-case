<?php

namespace Tests\Feature\Console;

use App\Models\Custodianship;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\ExpiredCustodianshipNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationsExpiredCustodianshipsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_processes_expired_custodianships(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()
            ->for($user)
            ->active()
            ->expired()
            ->create();

        $recipient1 = Recipient::factory()->for($custodianship)->create();
        $recipient2 = Recipient::factory()->for($custodianship)->create();

        $this->artisan('notifications:expired-custodianships')
            ->assertExitCode(0);

        $this->assertDatabaseHas('custodianships', [
            'id' => $custodianship->id,
            'status' => 'completed',
        ]);

        Notification::assertSentTo($recipient1, ExpiredCustodianshipNotification::class);
        Notification::assertSentTo($recipient2, ExpiredCustodianshipNotification::class);
    }

    public function test_command_ignores_non_expired_custodianships(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()
            ->for($user)
            ->active()
            ->create(['next_trigger_at' => now()->addDays(7)]);

        Recipient::factory()->for($custodianship)->create();

        $this->artisan('notifications:expired-custodianships')
            ->assertExitCode(0);

        $this->assertDatabaseHas('custodianships', [
            'id' => $custodianship->id,
            'status' => 'active',
        ]);

        Notification::assertNothingSent();
    }

    public function test_command_ignores_draft_custodianships(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()
            ->for($user)
            ->create([
                'status' => 'draft',
                'next_trigger_at' => now()->subDays(1),
            ]);

        Recipient::factory()->for($custodianship)->create();

        $this->artisan('notifications:expired-custodianships')
            ->assertExitCode(0);

        $this->assertDatabaseHas('custodianships', [
            'id' => $custodianship->id,
            'status' => 'draft',
        ]);

        Notification::assertNothingSent();
    }

    public function test_command_handles_no_expired_custodianships(): void
    {
        $this->artisan('notifications:expired-custodianships')
            ->expectsOutput('No expired custodianships found.')
            ->assertExitCode(0);
    }
}

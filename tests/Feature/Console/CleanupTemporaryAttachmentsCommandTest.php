<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupTemporaryAttachmentsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['media-library.disk_name' => 'public']);
    }

    public function test_command_deletes_old_temporary_attachments(): void
    {
        config(['custodianship.attachments.temporary_cleanup_hours' => 24]);

        $user = User::factory()->create();

        $oldMedia = $user->addMediaFromString('old file content')
            ->usingFileName('old.txt')
            ->toMediaCollection('temporary-attachments');
        $oldMedia->created_at = now()->subHours(25);
        $oldMedia->save();

        $this->artisan('custodianship:cleanup-temporary-attachments')
            ->assertSuccessful();

        $this->assertDatabaseMissing('media', ['id' => $oldMedia->id]);
    }

    public function test_command_keeps_recent_temporary_attachments(): void
    {
        config(['custodianship.attachments.temporary_cleanup_hours' => 24]);

        $user = User::factory()->create();

        $recentMedia = $user->addMediaFromString('recent file content')
            ->usingFileName('recent.txt')
            ->toMediaCollection('temporary-attachments');

        $this->artisan('custodianship:cleanup-temporary-attachments')
            ->assertSuccessful();

        $this->assertDatabaseHas('media', ['id' => $recentMedia->id]);
    }

    public function test_command_ignores_non_temporary_attachments(): void
    {
        config(['custodianship.attachments.temporary_cleanup_hours' => 24]);

        $user = User::factory()->create();

        $permanentMedia = $user->addMediaFromString('permanent file content')
            ->usingFileName('permanent.txt')
            ->toMediaCollection('attachments');
        $permanentMedia->created_at = now()->subHours(25);
        $permanentMedia->save();

        $this->artisan('custodianship:cleanup-temporary-attachments')
            ->assertSuccessful();

        $this->assertDatabaseHas('media', ['id' => $permanentMedia->id]);
    }
}

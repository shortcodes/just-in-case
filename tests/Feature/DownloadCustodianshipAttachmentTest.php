<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadCustodianshipAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_individual_attachment(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $media = $custodianship->addMedia($file)->toMediaCollection('attachments');

        $response = $this->actingAs($user)->get(
            route('custodianships.attachments.download', [
                'custodianship' => $custodianship,
                'attachment' => $media->id,
            ])
        );

        $response->assertOk();
    }

    public function test_download_is_logged_to_database(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $custodianship->addMedia($file)->toMediaCollection('attachments');

        $this->assertDatabaseCount('downloads', 0);

        $this->actingAs($user)->get(
            route('custodianships.attachments.download', [
                'custodianship' => $custodianship,
                'attachment' => $media->id,
            ])
        );

        $this->assertDatabaseCount('downloads', 1);
        $this->assertDatabaseHas('downloads', [
            'custodianship_id' => $custodianship->id,
            'success' => true,
            'filename' => 'document.pdf',
        ]);
    }

    public function test_download_logs_ip_address_and_user_agent(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $custodianship->addMedia($file)->toMediaCollection('attachments');

        $response = $this->actingAs($user)
            ->withHeader('User-Agent', 'TestBrowser/1.0')
            ->from('127.0.0.1')
            ->get(
                route('custodianships.attachments.download', [
                    'custodianship' => $custodianship,
                    'attachment' => $media->id,
                ])
            );

        $response->assertOk();

        $this->assertDatabaseHas('downloads', [
            'custodianship_id' => $custodianship->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestBrowser/1.0',
        ]);
    }

    public function test_download_fails_for_non_existent_attachment(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(
            route('custodianships.attachments.download', [
                'custodianship' => $custodianship,
                'attachment' => 999,
            ])
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('downloads', [
            'custodianship_id' => $custodianship->id,
            'success' => false,
            'filename' => null,
        ]);
    }

    public function test_user_cannot_download_attachment_from_another_users_custodianship(): void
    {
        Storage::fake('s3');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->for($owner)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $custodianship->addMedia($file)->toMediaCollection('attachments');

        $response = $this->actingAs($otherUser)->get(
            route('custodianships.attachments.download', [
                'custodianship' => $custodianship,
                'attachment' => $media->id,
            ])
        );

        $response->assertForbidden();
    }

    public function test_guest_cannot_download_attachment(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $custodianship->addMedia($file)->toMediaCollection('attachments');

        $response = $this->get(
            route('custodianships.attachments.download', [
                'custodianship' => $custodianship,
                'attachment' => $media->id,
            ])
        );

        $response->assertRedirect(route('login'));
    }

    public function test_download_returns_correct_file_content_type(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->image('photo.jpg');
        $media = $custodianship->addMedia($file)->toMediaCollection('attachments');

        $response = $this->actingAs($user)->get(
            route('custodianships.attachments.download', [
                'custodianship' => $custodianship,
                'attachment' => $media->id,
            ])
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }
}

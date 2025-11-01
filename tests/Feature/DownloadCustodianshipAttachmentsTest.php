<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\Download;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadCustodianshipAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_download_is_logged_to_database(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $custodianship->addMedia($file)->toMediaCollection('attachments');

        $this->assertDatabaseCount('downloads', 0);

        $response = $this->get(route('custodianships.download', $custodianship));

        $response->assertOk();

        $this->assertDatabaseCount('downloads', 1);
        $this->assertDatabaseHas('downloads', [
            'custodianship_id' => $custodianship->id,
            'success' => true,
            'filename' => 'document.pdf',
        ]);

        $download = Download::first();
        $this->assertNotNull($download->ip_address);
        $this->assertNotNull($download->user_agent);
    }

    public function test_download_logs_zip_filename_for_multiple_attachments(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create(['name' => 'Test Custodianship']);

        $file1 = UploadedFile::fake()->create('document1.pdf', 100);
        $file2 = UploadedFile::fake()->create('document2.pdf', 100);
        $custodianship->addMedia($file1)->toMediaCollection('attachments');
        $custodianship->addMedia($file2)->toMediaCollection('attachments');

        $response = $this->get(route('custodianships.download', $custodianship));

        $response->assertOk();

        $this->assertDatabaseHas('downloads', [
            'custodianship_id' => $custodianship->id,
            'success' => true,
            'filename' => 'Test Custodianship-attachments.zip',
        ]);
    }

    public function test_failed_download_is_logged_when_no_attachments_exist(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $response = $this->get(route('custodianships.download', $custodianship));

        $response->assertNotFound();

        $this->assertDatabaseHas('downloads', [
            'custodianship_id' => $custodianship->id,
            'success' => false,
            'filename' => null,
        ]);
    }

    public function test_download_logs_ip_address_and_user_agent(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $custodianship->addMedia($file)->toMediaCollection('attachments');

        $response = $this->withHeader('User-Agent', 'TestBrowser/1.0')
            ->from('127.0.0.1')
            ->get(route('custodianships.download', $custodianship));

        $response->assertOk();

        $this->assertDatabaseHas('downloads', [
            'custodianship_id' => $custodianship->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestBrowser/1.0',
        ]);
    }

    public function test_multiple_downloads_create_separate_log_entries(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $custodianship->addMedia($file)->toMediaCollection('attachments');

        $this->get(route('custodianships.download', $custodianship));
        $this->get(route('custodianships.download', $custodianship));
        $this->get(route('custodianships.download', $custodianship));

        $this->assertDatabaseCount('downloads', 3);
        $this->assertEquals(3, $custodianship->downloads()->count());
    }

    public function test_download_relationship_exists_on_custodianship(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->for($user)->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $custodianship->addMedia($file)->toMediaCollection('attachments');

        $this->get(route('custodianships.download', $custodianship));

        $custodianship->refresh();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $custodianship->downloads);
        $this->assertCount(1, $custodianship->downloads);
        $this->assertEquals($custodianship->id, $custodianship->downloads->first()->custodianship_id);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadCustodianshipAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_upload_attachment(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson(route('custodianships.attachments.upload'), [
            'file' => $file,
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_upload_valid_file(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('document.jpg');

        $response = $this->actingAs($user)->postJson(route('custodianships.attachments.upload'), [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'name',
            'fileName',
            'size',
            'mimeType',
        ]);

        $this->assertDatabaseHas('media', [
            'model_type' => User::class,
            'model_id' => $user->id,
            'collection_name' => 'temporary-attachments',
        ]);
    }

    public function test_upload_requires_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('custodianships.attachments.upload'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_rejects_executable_files(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($user)->postJson(route('custodianships.attachments.upload'), [
            'file' => $file,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_rejects_javascript_files(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('script.js', 100, 'application/javascript');

        $response = $this->actingAs($user)->postJson(route('custodianships.attachments.upload'), [
            'file' => $file,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_rejects_files_exceeding_size_limit(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf');

        $response = $this->actingAs($user)->postJson(route('custodianships.attachments.upload'), [
            'file' => $file,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_accepts_image_files(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($user)->postJson(route('custodianships.attachments.upload'), [
            'file' => $file,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('media', [
            'model_type' => User::class,
            'model_id' => $user->id,
            'collection_name' => 'temporary-attachments',
        ]);
    }

    public function test_upload_accepts_document_files(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $response = $this->actingAs($user)->postJson(route('custodianships.attachments.upload'), ['file' => $file]);
        $response->assertOk();

        $file = UploadedFile::fake()->create('document.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response = $this->actingAs($user)->postJson(route('custodianships.attachments.upload'), ['file' => $file]);
        $response->assertOk();
    }
}

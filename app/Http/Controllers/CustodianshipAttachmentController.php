<?php

namespace App\Http\Controllers;

use App\Http\Requests\DownloadCustodianshipAttachmentRequest;
use App\Http\Requests\UploadCustodianshipAttachmentRequest;
use App\Models\Custodianship;
use App\Models\Download;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class CustodianshipAttachmentController extends Controller
{
    public function upload(UploadCustodianshipAttachmentRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $user = $request->user();

        $media = $user->addMedia($file)
            ->toMediaCollection('temporary-attachments');

        return response()->json([
            'id' => $media->id,
            'name' => $media->name,
            'fileName' => $media->file_name,
            'size' => $media->size,
            'mimeType' => $media->mime_type,
        ]);
    }

    public function download(DownloadCustodianshipAttachmentRequest $request, Custodianship $custodianship, int $attachment): StreamedResponse
    {
        $mediaItem = $custodianship->getMedia('attachments')->firstWhere('id', $attachment);

        if (! $mediaItem) {
            $this->logDownload($custodianship, null, false);
            abort(404, 'Attachment not found');
        }

        $this->logDownload($custodianship, $mediaItem->file_name, true);

        return response()->streamDownload(function () use ($mediaItem) {
            $stream = $mediaItem->stream();
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $mediaItem->file_name, [
            'Content-Type' => $mediaItem->mime_type,
        ]);
    }

    public function downloadAll(Custodianship $custodianship): StreamedResponse
    {
        $media = $custodianship->getMedia('attachments');

        if ($media->isEmpty()) {
            $this->logDownload($custodianship, null, false);
            abort(404, 'No attachments found');
        }

        if ($media->count() === 1) {
            $mediaItem = $media->first();
            $this->logDownload($custodianship, $mediaItem->file_name, true);

            return response()->streamDownload(function () use ($mediaItem) {
                $stream = $mediaItem->stream();
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, $mediaItem->file_name, [
                'Content-Type' => $mediaItem->mime_type,
            ]);
        }

        $zipFilename = $custodianship->name.'-attachments.zip';
        $this->logDownload($custodianship, $zipFilename, true);

        return response()->streamDownload(function () use ($media) {
            $zip = new ZipArchive;
            $tempFile = tempnam(sys_get_temp_dir(), 'custodianship_');

            if ($zip->open($tempFile, ZipArchive::CREATE) === true) {
                foreach ($media as $mediaItem) {
                    $stream = $mediaItem->stream();
                    $content = stream_get_contents($stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $zip->addFromString($mediaItem->file_name, $content);
                }
                $zip->close();
            }

            readfile($tempFile);
            unlink($tempFile);
        }, $zipFilename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    private function logDownload(Custodianship $custodianship, ?string $filename, bool $success): void
    {
        Download::create([
            'custodianship_id' => $custodianship->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'filename' => $filename,
            'success' => $success,
        ]);
    }
}

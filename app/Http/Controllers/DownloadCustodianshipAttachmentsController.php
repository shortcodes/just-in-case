<?php

namespace App\Http\Controllers;

use App\Models\Custodianship;
use App\Models\Download;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class DownloadCustodianshipAttachmentsController extends Controller
{
    public function __invoke(Custodianship $custodianship): StreamedResponse
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
                echo file_get_contents($mediaItem->getPath());
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
                    $zip->addFile($mediaItem->getPath(), $mediaItem->file_name);
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

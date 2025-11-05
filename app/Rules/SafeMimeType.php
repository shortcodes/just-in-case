<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeMimeType implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('The :attribute must be a valid file.');

            return;
        }

        $allowedMimeTypes = config('custodianship.attachments.allowed_mime_types', []);
        $fileMimeType = $value->getMimeType();

        if (! in_array($fileMimeType, $allowedMimeTypes, true)) {
            $fail('The :attribute file type is not allowed. Only documents, images, audio, and video files are permitted.');
        }
    }
}

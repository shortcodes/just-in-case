<?php

namespace App\Http\Requests;

use App\Rules\SafeMimeType;
use Illuminate\Foundation\Http\FormRequest;

class UploadCustodianshipAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', new SafeMimeType],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded item must be a valid file.',
            'file.max' => 'The file size cannot exceed 10MB.',
        ];
    }
}

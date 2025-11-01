<?php

namespace App\Http\Requests;

use App\Models\Custodianship;
use Illuminate\Foundation\Http\FormRequest;

class PreviewCustodianshipMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        $custodianship = $this->route('custodianship');

        if (! $custodianship instanceof Custodianship) {
            return false;
        }

        return $this->user()->can('view', $custodianship);
    }

    public function rules(): array
    {
        return [];
    }
}

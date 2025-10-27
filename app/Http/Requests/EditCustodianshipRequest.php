<?php

namespace App\Http\Requests;

use App\Models\Custodianship;
use Illuminate\Foundation\Http\FormRequest;

class EditCustodianshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        $custodianship = $this->route('custodianship');

        if (! $custodianship instanceof Custodianship) {
            return false;
        }

        return $this->user()->can('update', $custodianship);
    }

    public function rules(): array
    {
        return [];
    }
}

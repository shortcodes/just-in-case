<?php

namespace App\Http\Requests;

use App\Models\Custodianship;
use Illuminate\Foundation\Http\FormRequest;

class DeleteCustodianshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        $custodianship = $this->route('custodianship');

        if (! $custodianship instanceof Custodianship) {
            return false;
        }

        return $this->user()->can('delete', $custodianship);
    }

    public function rules(): array
    {
        return [];
    }
}

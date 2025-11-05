<?php

namespace App\Http\Requests;

use App\Enums\IntervalUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCustodianshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'messageContent' => ['nullable', 'string', 'max:10000'],
            'intervalValue' => ['required', 'integer', 'min:1', 'max:999'],
            'intervalUnit' => ['required', new Enum(IntervalUnit::class)],
            'recipients' => ['nullable', 'array', 'max:2'],
            'recipients.*' => ['nullable', 'email', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['integer', 'exists:media,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a name for this custodianship.',
            'name.max' => 'The name cannot exceed 255 characters.',
            'messageContent.max' => 'The message content cannot exceed 10,000 characters.',
            'intervalValue.required' => 'Please enter a check-in interval value.',
            'intervalValue.integer' => 'The interval value must be a number.',
            'intervalValue.min' => 'The interval value must be at least 1.',
            'intervalValue.max' => 'The interval value cannot exceed 999.',
            'intervalUnit.required' => 'Please select an interval unit.',
            'intervalUnit.in' => 'Please select a valid interval unit (minutes, hours, or days).',
            'recipients.required' => 'Please add at least one recipient.',
            'recipients.min' => 'Please add at least one recipient.',
            'recipients.max' => 'You can add a maximum of 2 recipients in the free plan.',
            'recipients.*.required' => 'Please enter an email address for all recipients.',
            'attachments.max' => 'You can attach a maximum of 10 files.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ActivateCustodianshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('custodianship')->user_id;
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $custodianship = $this->route('custodianship');

            if (! $this->user()->hasVerifiedEmail()) {
                $validator->errors()->add(
                    'email_verification',
                    'You must verify your email address before activating a custodianship.'
                );
            }

            if ($custodianship->status !== 'draft') {
                $validator->errors()->add(
                    'status',
                    'Only draft custodianships can be activated.'
                );
            }

            if ($custodianship->recipients()->count() === 0) {
                $validator->errors()->add(
                    'recipients',
                    'The custodianship must have at least one recipient before activation.'
                );
            }

            if (! $custodianship->message || empty($custodianship->message->content)) {
                $validator->errors()->add(
                    'message',
                    'The custodianship must have a message before activation.'
                );
            }
        });
    }
}

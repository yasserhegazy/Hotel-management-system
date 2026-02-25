<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Merge the route parameter into the validated data
        // so the 'token' rule can be applied to it.
        $this->merge([
            'token' => $this->route('token'),
        ]);
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'regex:/^[a-f0-9]{64}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.regex' => 'Invalid or expired token.',
        ];
    }
}

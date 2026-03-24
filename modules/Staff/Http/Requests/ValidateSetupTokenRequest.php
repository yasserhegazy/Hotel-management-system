<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateSetupTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validate query parameters for GET request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:128'],
            'token' => ['required', 'string'],
        ];
    }

    /**
     * Use query string as the validation data source for GET requests.
     */
    public function validationData(): array
    {
        return $this->query();
    }
}

<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'email' => [
                'required',
                'string',
                'email',
                'max:128',
                Rule::unique('tenants', 'email'),
            ],
            'phone' => [
                'required',
                'string',
                'max:32',
                'regex:/^\+[1-9]\d{7,14}$/',
                Rule::unique('tenants', 'phone'),
            ],
            'location' => ['required', 'array'],
            'location.country_code' => ['required', 'string', 'max:3'],
            'location.region_name' => ['nullable', 'string', 'max:64'],
            'location.city' => ['required', 'string', 'max:64'],
            'location.address_line' => ['nullable', 'string', 'max:256'],
            'location.postal_code' => ['nullable', 'string', 'max:16'],
            'location.timezone' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Hotel name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Phone number must be in valid international format (e.g. +1234567890).',
            'phone.unique' => 'This phone number is already registered.',
            'location.required' => 'Location information is required.',
            'location.country_code.required' => 'Country code is required.',
            'location.city.required' => 'City is required.',
        ];
    }
}

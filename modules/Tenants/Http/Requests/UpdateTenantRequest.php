<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tenants\Domain\Models\Tenant;

class UpdateTenantRequest extends FormRequest
{
    private ?Tenant $tenant = null;

    protected function prepareForValidation(): void
    {
        $this->tenant = Tenant::with('location')->find($this->route('hotel_id'));

        if (! $this->tenant) {
            abort(404, 'Tenant not found.');
        }
    }

    public function authorize(): bool
    {
        return $this->tenant->owner_id === $this->user()->id;
    }

    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    public function rules(): array
    {
        $tenantId = $this->route('hotel_id');

        return [
            'name' => ['sometimes', 'string', 'max:128'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:128',
                Rule::unique('tenants', 'email')->ignore($tenantId),
            ],
            'phone' => [
                'sometimes',
                'string',
                'max:32',
                'regex:/^\+[1-9]\d{7,14}$/',
                Rule::unique('tenants', 'phone')->ignore($tenantId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['sometimes', 'array'],
            'location.country_code' => ['required_with:location', 'string', 'max:3'],
            'location.city' => ['required_with:location', 'string', 'max:64'],
            'location.region_name' => ['nullable', 'string', 'max:64'],
            'location.address_line' => ['nullable', 'string', 'max:256'],
            'location.postal_code' => ['nullable', 'string', 'max:16'],
            'location.timezone' => ['nullable', 'string', 'max:64'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'remove_profile_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered by another hotel.',
            'phone.regex' => 'Phone number must be in valid international format (e.g. +1234567890).',
            'phone.unique' => 'This phone number is already registered by another hotel.',
            'location.country_code.required_with' => 'Country code is required when updating location.',
            'location.city.required_with' => 'City is required when updating location.',
            'profile_image.max' => 'Profile image must not exceed 2MB.',
            'profile_image.mimes' => 'Profile image must be a JPEG, PNG, or WebP file.',
        ];
    }
}

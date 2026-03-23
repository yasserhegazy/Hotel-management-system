<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Staff\Domain\Enums\StaffRole;

class CreateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:64'],
            'last_name' => ['required', 'string', 'max:64'],
            'email' => ['required', 'email', 'max:128', 'unique:tenant_users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'preferred_language' => ['nullable', 'string', 'max:5'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in(array_column(StaffRole::cases(), 'value'))],
        ];
    }
}

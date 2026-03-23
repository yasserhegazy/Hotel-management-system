<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Staff\Domain\Enums\StaffRole;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:128'],
            'last_name' => ['sometimes', 'string', 'max:128'],
            'email' => ['sometimes', 'email', 'max:128', Rule::unique('tenant_users', 'email')->ignore($this->route('staff_id'))],
            'phone' => ['nullable', 'string'],
            'preferred_language' => ['sometimes', 'string', 'max:5'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in(array_column(StaffRole::cases(), 'value'))],
        ];
    }
}

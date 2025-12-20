<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    // System roles that cannot be deleted
    protected $systemRoles = ['super-admin', 'admin', 'staff', 'teacher', 'parent', 'student'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('super-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roleId = $this->route('role')?->id;
        $isSystemRole = $this->route('role') ? 
            in_array($this->route('role')->name, $this->systemRoles) : false;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles', 'name')->ignore($roleId),
                function ($attribute, $value, $fail) use ($isSystemRole) {
                    // If it's a system role being edited, don't allow changing the name
                    if ($isSystemRole && $this->route('role') && $value !== $this->route('role')->name) {
                        $fail('System role names cannot be changed.');
                    }
                    // Don't allow using system role names for new roles
                    if (!$isSystemRole && in_array($value, $this->systemRoles)) {
                        $fail('This role name is reserved for system use.');
                    }
                },
            ],
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Role name is required.',
            'name.unique' => 'This role name already exists.',
            'name.regex' => 'Role name must contain only lowercase letters, numbers, and hyphens.',
            'name.max' => 'Role name cannot exceed 255 characters.',
            'display_name.max' => 'Display name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'role name',
            'display_name' => 'display name',
            'description' => 'description',
        ];
    }
}

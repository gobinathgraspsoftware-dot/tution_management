<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['super-admin', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $staffId = $this->route('staff')?->id;
        $userId = $this->route('staff')?->user_id;

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'ic_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('staff', 'ic_number')->ignore($staffId),
            ],
            'address' => 'nullable|string|max:500',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'join_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'epf_number' => 'nullable|string|max:50',
            'socso_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,on_leave',
        ];

        // Email validation with unique check
        $rules['email'] = [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($userId),
        ];

        // Password rules - required on create, optional on update
        if ($this->isMethod('POST')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Staff name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'ic_number.required' => 'IC number is required.',
            'ic_number.unique' => 'This IC number is already registered.',
            'department.required' => 'Department is required.',
            'position.required' => 'Position is required.',
            'join_date.required' => 'Join date is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'ic_number' => 'IC number',
            'epf_number' => 'EPF number',
            'socso_number' => 'SOCSO number',
        ];
    }
}

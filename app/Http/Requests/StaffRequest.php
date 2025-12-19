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
     * Prepare the data for validation.
     * Convert name to UPPERCASE before validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => strtoupper($this->name),
            ]);
        }
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
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'ic_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Clean IC number - remove all non-numeric characters
                    $cleaned = preg_replace('/[^0-9]/', '', $value);
                    
                    // Check if exactly 12 digits
                    if (strlen($cleaned) !== 12) {
                        $fail('The IC number must be exactly 12 digits.');
                    }
                    
                    // Check if contains only numeric digits
                    if (!preg_match('/^[0-9]+$/', $cleaned)) {
                        $fail('The IC number must contain only numeric digits.');
                    }
                },
                Rule::unique('staff', 'ic_number')->ignore($staffId)->where(function ($query) {
                    // Clean the IC number for unique check
                    $cleaned = preg_replace('/[^0-9]/', '', $this->ic_number);
                    return $query->where('ic_number', $cleaned);
                }),
            ],
            'address' => 'nullable|string|max:500',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'join_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_country_code' => 'nullable|string|max:5',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
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
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'country_code.required' => 'Country code is required.',
            'phone.required' => 'Phone number is required.',
            'ic_number.required' => 'IC number is required.',
            'ic_number.unique' => 'This IC number is already registered.',
            'department.required' => 'Department is required.',
            'position.required' => 'Position is required.',
            'join_date.required' => 'Join date is required.',
            'join_date.date' => 'Join date must be a valid date.',
            'salary.numeric' => 'Salary must be a number.',
            'salary.min' => 'Salary cannot be negative.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected. Status must be active or inactive.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'ic_number' => 'IC number',
            'country_code' => 'country code',
            'emergency_country_code' => 'emergency contact country code',
            'emergency_phone' => 'emergency contact phone',
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation()
    {
        // Clean IC number - store only 12 digits without hyphens
        if ($this->has('ic_number')) {
            $this->merge([
                'ic_number' => preg_replace('/[^0-9]/', '', $this->ic_number),
            ]);
        }
    }
}

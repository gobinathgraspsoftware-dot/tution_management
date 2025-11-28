<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
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
        $teacherId = $this->route('teacher')?->id;
        $userId = $this->route('teacher')?->user_id;

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'ic_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('teachers', 'ic_number')->ignore($teacherId),
            ],
            'address' => 'nullable|string|max:500',
            'qualification' => 'nullable|string|max:500',
            'experience_years' => 'required|integer|min:0|max:50',
            'specialization' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'join_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'pay_type' => 'required|in:hourly,monthly,per_class',
            'hourly_rate' => 'nullable|numeric|min:0|required_if:pay_type,hourly',
            'monthly_salary' => 'nullable|numeric|min:0|required_if:pay_type,monthly',
            'per_class_rate' => 'nullable|numeric|min:0|required_if:pay_type,per_class',
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
            'name.required' => 'Teacher name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'ic_number.required' => 'IC number is required.',
            'ic_number.unique' => 'This IC number is already registered.',
            'experience_years.required' => 'Years of experience is required.',
            'experience_years.integer' => 'Experience must be a valid number.',
            'specialization.required' => 'Specialization is required.',
            'join_date.required' => 'Join date is required.',
            'employment_type.required' => 'Employment type is required.',
            'employment_type.in' => 'Invalid employment type selected.',
            'pay_type.required' => 'Pay type is required.',
            'pay_type.in' => 'Invalid pay type selected.',
            'hourly_rate.required_if' => 'Hourly rate is required when pay type is hourly.',
            'monthly_salary.required_if' => 'Monthly salary is required when pay type is monthly.',
            'per_class_rate.required_if' => 'Per class rate is required when pay type is per class.',
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
            'experience_years' => 'years of experience',
        ];
    }
}

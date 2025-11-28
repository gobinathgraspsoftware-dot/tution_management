<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['super-admin', 'admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $studentId = $this->route('student')?->id;
        $userId = $this->route('student')?->user_id;

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'parent_id' => 'required|exists:parents,id',
            'ic_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'ic_number')->ignore($studentId),
            ],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'school_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:50',
            'address' => 'nullable|string|max:500',
            'medical_conditions' => 'nullable|string|max:500',
            'registration_type' => 'required|in:online,offline',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
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
            'name.required' => 'Student name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already registered.',
            'parent_id.required' => 'Parent/Guardian must be selected.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'ic_number.required' => 'IC number is required.',
            'ic_number.unique' => 'This IC number is already registered.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Invalid gender selected.',
            'school_name.required' => 'School name is required.',
            'grade_level.required' => 'Grade level is required.',
            'registration_type.required' => 'Registration type is required.',
            'registration_type.in' => 'Invalid registration type.',
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
            'date_of_birth' => 'date of birth',
            'school_name' => 'school name',
            'grade_level' => 'grade level',
            'medical_conditions' => 'medical conditions',
            'registration_type' => 'registration type',
        ];
    }
}

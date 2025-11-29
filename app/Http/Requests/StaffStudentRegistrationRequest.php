<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffStudentRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Student Account Information
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',

            // Parent Selection
            'parent_id' => 'required|exists:parents,id',

            // Student Details
            'ic_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'ic_number'),
            ],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'school_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:50',
            'address' => 'nullable|string|max:500',

            // Additional Information
            'medical_conditions' => 'nullable|string|max:500',
            'referral_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',

            // Staff Options
            'auto_approve' => 'nullable|boolean',
        ];
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
            'parent_id.required' => 'Please select a parent/guardian.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'ic_number.required' => 'IC number is required.',
            'ic_number.unique' => 'This IC number is already registered.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Invalid gender selected.',
            'school_name.required' => 'School name is required.',
            'grade_level.required' => 'Grade level is required.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'parent_id' => 'parent/guardian',
            'ic_number' => 'IC number',
            'date_of_birth' => 'date of birth',
            'school_name' => 'school name',
            'grade_level' => 'grade level',
            'medical_conditions' => 'medical conditions',
            'referral_code' => 'referral code',
            'auto_approve' => 'auto approve',
        ];
    }
}

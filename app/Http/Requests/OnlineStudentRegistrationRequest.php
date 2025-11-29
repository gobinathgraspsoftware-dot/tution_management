<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnlineStudentRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public registration
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Parent Information
            'parent_name' => 'required|string|max:255',
            'parent_email' => 'required|email|max:255',
            'parent_phone' => 'required|string|max:20',
            'parent_ic' => 'nullable|string|max:20',
            'parent_whatsapp' => 'nullable|string|max:20',
            'relationship' => 'required|in:father,mother,guardian',

            // Student Information
            'student_name' => 'required|string|max:255',
            'student_email' => 'nullable|email|max:255|unique:users,email',
            'student_phone' => 'nullable|string|max:20',
            'student_ic' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'ic_number'),
            ],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'school_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:50',

            // Address Information
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',

            // Additional Information
            'medical_conditions' => 'nullable|string|max:500',
            'referral_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',

            // Terms
            'terms_accepted' => 'required|accepted',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Parent messages
            'parent_name.required' => 'Parent/Guardian name is required.',
            'parent_email.required' => 'Parent/Guardian email is required.',
            'parent_email.email' => 'Please enter a valid email address.',
            'parent_phone.required' => 'Parent/Guardian phone number is required.',
            'relationship.required' => 'Please select your relationship with the student.',
            'relationship.in' => 'Invalid relationship selected.',

            // Student messages
            'student_name.required' => 'Student name is required.',
            'student_email.unique' => 'This student email is already registered.',
            'student_ic.required' => 'Student IC number is required.',
            'student_ic.unique' => 'This IC number is already registered.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Invalid gender selected.',
            'school_name.required' => 'School name is required.',
            'grade_level.required' => 'Grade level is required.',

            // Address messages
            'address.required' => 'Address is required.',

            // Terms
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'parent_name' => 'parent/guardian name',
            'parent_email' => 'parent/guardian email',
            'parent_phone' => 'parent/guardian phone',
            'parent_ic' => 'parent/guardian IC',
            'parent_whatsapp' => 'WhatsApp number',
            'student_name' => 'student name',
            'student_email' => 'student email',
            'student_ic' => 'student IC',
            'date_of_birth' => 'date of birth',
            'school_name' => 'school name',
            'grade_level' => 'grade level',
            'medical_conditions' => 'medical conditions',
            'referral_code' => 'referral code',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeminarRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $seminarId = $this->route('seminar') ? $this->route('seminar')->id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('seminar_participants', 'email')->where(function ($query) use ($seminarId) {
                    return $query->where('seminar_id', $seminarId);
                }),
            ],
            'phone' => 'required|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/',
            'school' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:50',
            'payment_method' => 'nullable|in:cash,online,bank_transfer',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Your name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered for this seminar.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid phone number.',
            'school.max' => 'School name cannot exceed 255 characters.',
            'grade.max' => 'Grade cannot exceed 50 characters.',
            'payment_method.in' => 'Invalid payment method selected.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'phone' => 'phone number',
            'school' => 'school name',
            'grade' => 'grade/class',
            'payment_method' => 'payment method',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Format phone number
        if ($this->has('phone')) {
            $phone = $this->phone;
            // Remove any extra spaces
            $phone = preg_replace('/\s+/', ' ', trim($phone));
            
            $this->merge([
                'phone' => $phone,
            ]);
        }
    }
}

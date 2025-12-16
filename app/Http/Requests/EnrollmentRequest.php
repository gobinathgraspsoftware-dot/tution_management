<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentRequest extends FormRequest
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
        $rules = [
            'student_id' => 'required|exists:students,id',
            'start_date' => 'required|date|after_or_equal:today',
            'payment_cycle_day' => 'required|integer|min:1|max:28',
            'status' => 'nullable|in:active,suspended,cancelled,expired,trial',
        ];

        // Package or Class is required (one of them)
        if (!$this->has('package_id') && !$this->has('class_id')) {
            $rules['package_id'] = 'required';
            $rules['class_id'] = 'required';
        } else {
            $rules['package_id'] = 'nullable|exists:packages,id';
            $rules['class_id'] = 'nullable|exists:classes,id';
        }

        // Additional rules based on enrollment type
        if ($this->has('package_id') && $this->package_id) {
            $rules['monthly_fee'] = 'nullable|numeric|min:0';
        } else {
            $rules['class_id'] = 'required|exists:classes,id';
            $rules['monthly_fee'] = 'required|numeric|min:0';
        }

        // Update specific rules
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['end_date'] = 'nullable|date|after:start_date';
            $rules['cancellation_reason'] = 'nullable|string|max:500';
            $rules['fee_change_reason'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'student',
            'package_id' => 'package',
            'class_id' => 'class',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'payment_cycle_day' => 'payment cycle day',
            'monthly_fee' => 'monthly fee',
            'cancellation_reason' => 'cancellation reason',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'The selected student is invalid.',
            'package_id.exists' => 'The selected package is invalid.',
            'class_id.exists' => 'The selected class is invalid.',
            'start_date.required' => 'Please select a start date.',
            'start_date.after_or_equal' => 'Start date must be today or a future date.',
            'end_date.after' => 'End date must be after start date.',
            'payment_cycle_day.required' => 'Please select a payment cycle day.',
            'payment_cycle_day.min' => 'Payment cycle day must be between 1 and 28.',
            'payment_cycle_day.max' => 'Payment cycle day must be between 1 and 28.',
            'monthly_fee.required' => 'Please enter the monthly fee.',
            'monthly_fee.numeric' => 'Monthly fee must be a valid number.',
            'monthly_fee.min' => 'Monthly fee cannot be negative.',
        ];
    }
}

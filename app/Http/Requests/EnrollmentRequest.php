<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['super-admin', 'admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'student_id' => 'required|exists:students,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'payment_cycle_day' => 'required|integer|min:1|max:15',
            'status' => 'required|in:active,trial,suspended,expired,cancelled',
        ];

        // Determine enrollment type
        $enrollmentType = $this->input('enrollment_type');
        
        // For updates, check the existing enrollment if type not provided
        if (($this->isMethod('PUT') || $this->isMethod('PATCH')) && !$enrollmentType) {
            $enrollment = $this->route('enrollment');
            if ($enrollment) {
                $enrollmentType = $enrollment->package_id ? 'package' : 'single';
            }
        }
        
        // Default to 'package' for new enrollments if not specified
        $enrollmentType = $enrollmentType ?? 'package';

        if ($enrollmentType === 'package') {
            $rules['package_id'] = 'required|exists:packages,id';
            $rules['class_id'] = 'nullable|exists:classes,id';
            $rules['monthly_fee'] = 'nullable|numeric|min:0';
            $rules['subject_classes'] = 'nullable|array';
            $rules['subject_classes.*'] = 'nullable|exists:classes,id';
        } else {
            // Single class enrollment
            $rules['package_id'] = 'nullable';
            $rules['class_id'] = 'required|exists:classes,id';
            $rules['monthly_fee'] = 'required|numeric|min:0';
        }

        // Fee change reason required when updating PACKAGE enrollment and fee has changed
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $enrollment = $this->route('enrollment');
            
            // Only allow fee changes for package enrollments
            if ($enrollment && $enrollment->package_id) {
                $newFee = $this->input('monthly_fee');
                
                if ($newFee !== null && floatval($newFee) != floatval($enrollment->monthly_fee)) {
                    $rules['fee_change_reason'] = 'required|string|max:500';
                } else {
                    $rules['fee_change_reason'] = 'nullable|string|max:500';
                }
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'Selected student does not exist.',
            'package_id.required' => 'Please select a package.',
            'package_id.exists' => 'Selected package does not exist.',
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Please enter a valid start date.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'payment_cycle_day.required' => 'Payment cycle day is required.',
            'payment_cycle_day.integer' => 'Payment cycle day must be a number.',
            'payment_cycle_day.min' => 'Payment cycle day must be at least 1.',
            'payment_cycle_day.max' => 'Payment cycle day cannot exceed 15.',
            'monthly_fee.required' => 'Monthly fee is required.',
            'monthly_fee.numeric' => 'Monthly fee must be a valid number.',
            'monthly_fee.min' => 'Monthly fee cannot be negative.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'fee_change_reason.required' => 'Please provide a reason for changing the monthly fee.',
            'fee_change_reason.max' => 'Fee change reason cannot exceed 500 characters.',
            'subject_classes.*.exists' => 'One or more selected classes do not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
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
            'fee_change_reason' => 'fee change reason',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge(['status' => 'active']);
        }

        // Clean up the monthly fee
        if ($this->has('monthly_fee') && $this->monthly_fee !== null) {
            $this->merge([
                'monthly_fee' => floatval($this->monthly_fee)
            ]);
        }
    }
}

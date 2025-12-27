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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $enrollmentId = $this->route('enrollment') ? $this->route('enrollment')->id : null;
        
        $rules = [
            'student_id' => ['required', 'exists:students,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'payment_cycle_day' => ['required', 'integer', 'min:1', 'max:28'],
            'status' => ['nullable', 'in:active,suspended,expired,cancelled,trial'],
            'fee_change_reason' => ['nullable', 'string', 'max:500'],
        ];

        // For new enrollment
        if (!$enrollmentId) {
            // Validate based on enrollment type
            if ($this->input('enrollment_type') === 'package') {
                $rules['package_id'] = ['required', 'exists:packages,id'];
                $rules['subject_classes'] = ['required', 'array', 'min:1'];
                $rules['subject_classes.*'] = ['required', 'exists:classes,id'];
            } else {
                $rules['class_id'] = ['required', 'exists:classes,id'];
                $rules['monthly_fee'] = ['required', 'numeric', 'min:0'];
            }
        } else {
            // For update
            $rules['monthly_fee'] = ['required', 'numeric', 'min:0'];
            
            // Require fee change reason if fee is changed
            if ($this->input('monthly_fee') != $this->route('enrollment')->monthly_fee) {
                $rules['fee_change_reason'] = ['required', 'string', 'max:500'];
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
            'student_id.exists' => 'The selected student is invalid.',
            'package_id.required' => 'Please select a package for package enrollment.',
            'package_id.exists' => 'The selected package is invalid.',
            'class_id.required' => 'Please select a class for single class enrollment.',
            'class_id.exists' => 'The selected class is invalid.',
            'subject_classes.required' => 'Please select classes for each subject in the package.',
            'subject_classes.min' => 'Please select at least one class.',
            'subject_classes.*.required' => 'Please select a class for each subject.',
            'subject_classes.*.exists' => 'One or more selected classes are invalid.',
            'start_date.required' => 'Please select a start date.',
            'start_date.date' => 'Please provide a valid start date.',
            'end_date.after' => 'End date must be after the start date.',
            'payment_cycle_day.required' => 'Please select a payment cycle day.',
            'payment_cycle_day.integer' => 'Payment cycle day must be a number.',
            'payment_cycle_day.min' => 'Payment cycle day must be between 1 and 28.',
            'payment_cycle_day.max' => 'Payment cycle day must be between 1 and 28.',
            'monthly_fee.required' => 'Please enter the monthly fee.',
            'monthly_fee.numeric' => 'Monthly fee must be a number.',
            'monthly_fee.min' => 'Monthly fee cannot be negative.',
            'fee_change_reason.required' => 'Please provide a reason for the fee change.',
            'fee_change_reason.max' => 'Fee change reason cannot exceed 500 characters.',
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
            'subject_classes' => 'class selections',
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
        if (!$this->has('status') || empty($this->status)) {
            $this->merge(['status' => 'active']);
        }

        // Clean subject_classes array - remove empty values
        if ($this->has('subject_classes') && is_array($this->subject_classes)) {
            $cleaned = array_filter($this->subject_classes, fn($value) => !empty($value));
            $this->merge(['subject_classes' => $cleaned]);
        }
    }
}

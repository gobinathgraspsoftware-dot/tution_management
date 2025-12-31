<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherPayslipRequest extends FormRequest
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
        return [
            'teacher_id' => 'required|exists:teachers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,approved,paid',
            'notes' => 'nullable|string|max:1000',
            // Statutory contribution flags
            'epf_enabled' => 'nullable|boolean',
            'socso_enabled' => 'nullable|boolean',
            'socso_type' => 'nullable|in:regular,insurance_only',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'teacher_id.required' => 'Please select a teacher.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'period_start.required' => 'Period start date is required.',
            'period_end.required' => 'Period end date is required.',
            'period_end.after_or_equal' => 'Period end must be on or after period start.',
            'allowances.numeric' => 'Allowances must be a valid number.',
            'allowances.min' => 'Allowances cannot be negative.',
            'deductions.numeric' => 'Deductions must be a valid number.',
            'deductions.min' => 'Deductions cannot be negative.',
            'status.in' => 'Invalid status selected.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'socso_type.in' => 'Invalid SOCSO type selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'teacher_id' => 'teacher',
            'period_start' => 'period start date',
            'period_end' => 'period end date',
            'epf_enabled' => 'EPF deduction',
            'socso_enabled' => 'SOCSO deduction',
            'socso_type' => 'SOCSO type',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        $this->merge([
            'epf_enabled' => $this->has('epf_enabled') ? (bool) $this->epf_enabled : null,
            'socso_enabled' => $this->has('socso_enabled') ? (bool) $this->socso_enabled : null,
        ]);
    }
}

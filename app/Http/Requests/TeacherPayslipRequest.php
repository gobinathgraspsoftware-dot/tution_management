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
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'teacher_id.required' => 'Please select a teacher.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'period_start.required' => 'Period start date is required.',
            'period_start.date' => 'Invalid period start date.',
            'period_end.required' => 'Period end date is required.',
            'period_end.date' => 'Invalid period end date.',
            'period_end.after_or_equal' => 'Period end date must be after or equal to start date.',
            'allowances.numeric' => 'Allowances must be a valid number.',
            'allowances.min' => 'Allowances cannot be negative.',
            'deductions.numeric' => 'Deductions must be a valid number.',
            'deductions.min' => 'Deductions cannot be negative.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'teacher_id' => 'teacher',
            'period_start' => 'start date',
            'period_end' => 'end date',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('view-attendance-reports');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => 'nullable|exists:students,id',
            'class_id' => 'nullable|exists:classes,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'nullable|in:csv,xlsx,pdf',
            'threshold' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:present,absent,late,excused',
            'period' => 'nullable|in:today,week,month,quarter,year',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date_from.required' => 'Start date is required.',
            'date_to.required' => 'End date is required.',
            'date_to.after_or_equal' => 'End date must be after or equal to start date.',
            'student_id.exists' => 'Selected student does not exist.',
            'class_id.exists' => 'Selected class does not exist.',
            'threshold.min' => 'Threshold must be at least 0.',
            'threshold.max' => 'Threshold cannot exceed 100.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'date_from' => 'start date',
            'date_to' => 'end date',
            'student_id' => 'student',
            'class_id' => 'class',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults if not provided
        if (!$this->has('date_from')) {
            $this->merge(['date_from' => now()->subMonth()->format('Y-m-d')]);
        }

        if (!$this->has('date_to')) {
            $this->merge(['date_to' => now()->format('Y-m-d')]);
        }

        if (!$this->has('threshold')) {
            $this->merge(['threshold' => 75]);
        }
    }
}

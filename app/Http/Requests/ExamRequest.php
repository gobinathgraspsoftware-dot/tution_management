<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamRequest extends FormRequest
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
            'name'             => 'required|string|max:255',
            'class_id'         => 'required|exists:classes,id',
            'subject_id'       => 'required|exists:subjects,id',
            'exam_date'        => 'required|date',
            'start_time'       => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'max_marks'        => 'required|numeric|min:1|max:9999.99',
            'passing_marks'    => 'required|numeric|min:0|lte:max_marks',
            'description'      => 'nullable|string|max:2000',
            'status'           => 'required|in:scheduled,ongoing,completed,cancelled',
        ];

        // For create: exam date should be today or future
        if ($this->isMethod('POST')) {
            $rules['exam_date'] = 'required|date|after_or_equal:today';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required'             => 'Exam name is required.',
            'name.max'                  => 'Exam name cannot exceed 255 characters.',
            'class_id.required'         => 'Please select a class.',
            'class_id.exists'           => 'Selected class does not exist.',
            'subject_id.required'       => 'Please select a subject.',
            'subject_id.exists'         => 'Selected subject does not exist.',
            'exam_date.required'        => 'Exam date is required.',
            'exam_date.date'            => 'Please enter a valid date.',
            'exam_date.after_or_equal'  => 'Exam date must be today or a future date.',
            'start_time.required'       => 'Start time is required.',
            'start_time.date_format'    => 'Please enter a valid time (HH:MM).',
            'duration_minutes.required' => 'Duration is required.',
            'duration_minutes.integer'  => 'Duration must be a whole number.',
            'duration_minutes.min'      => 'Duration must be at least 1 minute.',
            'duration_minutes.max'      => 'Duration cannot exceed 480 minutes (8 hours).',
            'max_marks.required'        => 'Maximum marks is required.',
            'max_marks.numeric'         => 'Maximum marks must be a valid number.',
            'max_marks.min'             => 'Maximum marks must be at least 1.',
            'max_marks.max'             => 'Maximum marks cannot exceed 9,999.99.',
            'passing_marks.required'    => 'Passing marks is required.',
            'passing_marks.numeric'     => 'Passing marks must be a valid number.',
            'passing_marks.min'         => 'Passing marks cannot be negative.',
            'passing_marks.lte'         => 'Passing marks cannot exceed maximum marks.',
            'description.max'           => 'Description cannot exceed 2000 characters.',
            'status.required'           => 'Status is required.',
            'status.in'                 => 'Invalid status selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name'             => 'exam name',
            'class_id'         => 'class',
            'subject_id'       => 'subject',
            'exam_date'        => 'exam date',
            'start_time'       => 'start time',
            'duration_minutes' => 'duration',
            'max_marks'        => 'maximum marks',
            'passing_marks'    => 'passing marks',
            'description'      => 'description',
            'status'           => 'status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('duration_minutes')) {
            $this->merge(['duration_minutes' => (int) $this->input('duration_minutes')]);
        }

        if ($this->has('max_marks') && $this->input('max_marks') !== '') {
            $this->merge(['max_marks' => (float) $this->input('max_marks')]);
        }

        if ($this->has('passing_marks') && $this->input('passing_marks') !== '') {
            $this->merge(['passing_marks' => (float) $this->input('passing_marks')]);
        }
    }
}

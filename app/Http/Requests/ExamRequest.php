<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'max_marks' => 'required|numeric|min:1|max:1000',
            'passing_marks' => 'nullable|numeric|min:0|max:' . ($this->max_marks ?? 100),
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Exam name is required.',
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'Selected class does not exist.',
            'subject_id.required' => 'Please select a subject.',
            'subject_id.exists' => 'Selected subject does not exist.',
            'exam_date.required' => 'Exam date is required.',
            'exam_date.date' => 'Please provide a valid exam date.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'duration_minutes.integer' => 'Duration must be a number.',
            'duration_minutes.min' => 'Duration must be at least 1 minute.',
            'duration_minutes.max' => 'Duration cannot exceed 480 minutes (8 hours).',
            'max_marks.required' => 'Maximum marks is required.',
            'max_marks.min' => 'Maximum marks must be at least 1.',
            'max_marks.max' => 'Maximum marks cannot exceed 1000.',
            'passing_marks.max' => 'Passing marks cannot exceed maximum marks.',
            'status.required' => 'Exam status is required.',
            'status.in' => 'Invalid exam status selected.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'class',
            'subject_id' => 'subject',
            'exam_date' => 'exam date',
            'start_time' => 'start time',
            'duration_minutes' => 'duration',
            'max_marks' => 'maximum marks',
            'passing_marks' => 'passing marks',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate passing marks doesn't exceed max marks
            if ($this->passing_marks && $this->max_marks) {
                if ($this->passing_marks > $this->max_marks) {
                    $validator->errors()->add('passing_marks', 'Passing marks cannot exceed maximum marks.');
                }
            }
        });
    }
}

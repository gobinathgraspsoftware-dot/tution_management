<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamResultRequest extends FormRequest
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
        $exam = $this->route('result') ? $this->route('result')->exam : null;
        $maxMarks = $exam ? $exam->max_marks : 100;

        return [
            'marks_obtained' => 'required|numeric|min:0|max:' . $maxMarks,
            'remarks' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'marks_obtained.required' => 'Marks obtained is required.',
            'marks_obtained.numeric' => 'Marks must be a number.',
            'marks_obtained.min' => 'Marks cannot be negative.',
            'marks_obtained.max' => 'Marks cannot exceed maximum marks for this exam.',
            'remarks.max' => 'Remarks cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'marks_obtained' => 'marks obtained',
        ];
    }
}

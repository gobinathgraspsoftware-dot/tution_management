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
        return auth()->user()->hasAnyRole(['super-admin', 'admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get max marks from the exam (via result relationship or route param)
        $maxMarks = 9999.99;

        if ($this->route('result') && $this->route('result')->exam) {
            $maxMarks = $this->route('result')->exam->max_marks;
        }

        return [
            'marks_obtained' => "required|numeric|min:0|max:{$maxMarks}",
            'remarks'        => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'marks_obtained.required' => 'Marks obtained is required.',
            'marks_obtained.numeric'  => 'Marks must be a valid number.',
            'marks_obtained.min'      => 'Marks cannot be negative.',
            'marks_obtained.max'      => 'Marks cannot exceed the maximum marks for this exam.',
            'remarks.max'             => 'Remarks cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'marks_obtained' => 'marks obtained',
            'remarks'        => 'remarks',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('marks_obtained') && $this->input('marks_obtained') !== '') {
            $this->merge(['marks_obtained' => (float) $this->input('marks_obtained')]);
        }
    }
}

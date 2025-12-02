<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentReviewRequest extends FormRequest
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
            'student_id' => 'required|exists:students,id',
            'class_id' => 'nullable|exists:classes,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'The selected student does not exist.',
            'class_id.exists' => 'The selected class does not exist.',
            'teacher_id.exists' => 'The selected teacher does not exist.',
            'rating.required' => 'Please provide a rating.',
            'rating.integer' => 'Rating must be a whole number.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',
            'review.max' => 'Review cannot exceed 2000 characters.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'student',
            'class_id' => 'class',
            'teacher_id' => 'teacher',
            'rating' => 'rating',
            'review' => 'review text',
        ];
    }
}

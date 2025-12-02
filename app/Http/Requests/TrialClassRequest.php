<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrialClassRequest extends FormRequest
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
            'student_id' => 'nullable|exists:students,id',
            'parent_name' => 'required_without:student_id|nullable|string|max:255',
            'parent_phone' => 'required_without:student_id|nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
            'student_name' => 'required_without:student_id|nullable|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'student_id.exists' => 'The selected student does not exist.',
            'parent_name.required_without' => 'Parent name is required when no existing student is selected.',
            'parent_phone.required_without' => 'Parent phone is required when no existing student is selected.',
            'student_name.required_without' => 'Student name is required when no existing student is selected.',
            'class_id.required' => 'Please select a class for the trial.',
            'class_id.exists' => 'The selected class does not exist.',
            'scheduled_date.required' => 'Please select a date for the trial.',
            'scheduled_date.after_or_equal' => 'The trial date must be today or a future date.',
            'scheduled_time.required' => 'Please select a time for the trial.',
            'scheduled_time.date_format' => 'Please enter a valid time in HH:MM format.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'student',
            'parent_name' => 'parent name',
            'parent_phone' => 'parent phone',
            'parent_email' => 'parent email',
            'student_name' => 'student name',
            'class_id' => 'class',
            'scheduled_date' => 'trial date',
            'scheduled_time' => 'trial time',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('mark-student-attendance');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'send_notifications' => 'nullable|boolean',
            'attendance' => 'required|array|min:1',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.check_in_time' => 'nullable|date_format:H:i',
            'attendance.*.remarks' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Please select a class.',
            'class_id.exists' => 'The selected class does not exist.',
            'date.required' => 'Please select a date.',
            'date.date' => 'Please enter a valid date.',
            'attendance.required' => 'Please mark attendance for at least one student.',
            'attendance.min' => 'Please mark attendance for at least one student.',
            'attendance.*.status.required' => 'Please select attendance status for all students.',
            'attendance.*.status.in' => 'Invalid attendance status selected.',
            'attendance.*.check_in_time.date_format' => 'Check-in time must be in HH:MM format.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'class',
            'date' => 'attendance date',
            'send_notifications' => 'notification setting',
        ];
    }
}

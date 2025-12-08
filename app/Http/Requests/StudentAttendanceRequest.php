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
            'session_id' => 'required|exists:class_sessions,id',
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
            'session_id.required' => 'Please select a session.',
            'attendance.required' => 'Please mark attendance for at least one student.',
            'attendance.*.status.required' => 'Please select attendance status for all students.',
            'attendance.*.status.in' => 'Invalid attendance status selected.',
        ];
    }
}

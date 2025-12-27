<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('mark-teacher-attendance');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'attendance' => 'required|array|min:1',
            'attendance.*.status' => 'required|in:present,absent,half_day,leave',
            'attendance.*.time_in' => 'nullable|date_format:H:i',
            'attendance.*.time_out' => 'nullable|date_format:H:i',
            'attendance.*.remarks' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Please select a date.',
            'date.date' => 'Please enter a valid date.',
            'attendance.required' => 'Please mark attendance for at least one teacher.',
            'attendance.min' => 'Please mark attendance for at least one teacher.',
            'attendance.*.status.required' => 'Please select attendance status for all teachers.',
            'attendance.*.status.in' => 'Invalid attendance status selected.',
            'attendance.*.time_in.date_format' => 'Time in must be in HH:MM format.',
            'attendance.*.time_out.date_format' => 'Time out must be in HH:MM format.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'date' => 'attendance date',
            'attendance.*.status' => 'status',
            'attendance.*.time_in' => 'time in',
            'attendance.*.time_out' => 'time out',
            'attendance.*.remarks' => 'remarks',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('manage-class-schedule');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'day_of_week.required' => 'Please select a day of the week.',
            'day_of_week.in' => 'The selected day is invalid.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            'effective_from.date' => 'Effective from must be a valid date.',
            'effective_until.date' => 'Effective until must be a valid date.',
            'effective_until.after_or_equal' => 'Effective until must be on or after the effective from date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null for nullable fields
        $this->merge([
            'effective_from' => $this->effective_from ?: null,
            'effective_until' => $this->effective_until ?: null,
        ]);

        // Set is_active to true by default if not provided
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'day_of_week' => 'day of week',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'effective_from' => 'effective from',
            'effective_until' => 'effective until',
            'is_active' => 'active status',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation: ensure times are in business hours (8 AM - 10 PM)
            if ($this->start_time) {
                $startHour = (int) substr($this->start_time, 0, 2);
                if ($startHour < 8 || $startHour >= 22) {
                    $validator->errors()->add('start_time', 'Classes must start between 8:00 AM and 10:00 PM.');
                }
            }

            if ($this->end_time) {
                $endHour = (int) substr($this->end_time, 0, 2);
                if ($endHour > 22 || ($endHour == 22 && substr($this->end_time, 3, 2) > 0)) {
                    $validator->errors()->add('end_time', 'Classes must end by 10:00 PM.');
                }
            }

            // Check duration is reasonable (at least 30 minutes, max 4 hours)
            if ($this->start_time && $this->end_time) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);
                $duration = $end->diffInMinutes($start);

                if ($duration < 30) {
                    $validator->errors()->add('end_time', 'Class duration must be at least 30 minutes.');
                }

                if ($duration > 240) {
                    $validator->errors()->add('end_time', 'Class duration cannot exceed 4 hours.');
                }
            }
        });
    }
}

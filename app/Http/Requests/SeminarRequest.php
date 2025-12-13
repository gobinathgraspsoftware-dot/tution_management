<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeminarRequest extends FormRequest
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
        $seminarId = $this->route('seminar') ? $this->route('seminar')->id : null;

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:spm,workshop,bootcamp,other',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'venue' => 'required_if:is_online,0|nullable|string|max:255',
            'is_online' => 'required|boolean',
            'meeting_link' => 'required_if:is_online,1|nullable|url|max:500',
            'capacity' => 'nullable|integer|min:1|max:1000',
            'regular_fee' => 'required|numeric|min:0|max:9999.99',
            'early_bird_fee' => 'nullable|numeric|min:0|max:9999.99|lt:regular_fee',
            'early_bird_deadline' => 'required_with:early_bird_fee|nullable|date|before:date',
            'registration_deadline' => 'nullable|date|before_or_equal:date',
            'facilitator' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:draft,open,closed,completed,cancelled',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Seminar name is required.',
            'type.required' => 'Seminar type is required.',
            'type.in' => 'Invalid seminar type selected.',
            'date.required' => 'Seminar date is required.',
            'date.after_or_equal' => 'Seminar date cannot be in the past.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            'venue.required_if' => 'Venue is required for offline seminars.',
            'is_online.required' => 'Please specify if seminar is online or offline.',
            'meeting_link.required_if' => 'Meeting link is required for online seminars.',
            'meeting_link.url' => 'Meeting link must be a valid URL.',
            'capacity.integer' => 'Capacity must be a number.',
            'capacity.min' => 'Capacity must be at least 1.',
            'capacity.max' => 'Capacity cannot exceed 1000.',
            'regular_fee.required' => 'Regular fee is required.',
            'regular_fee.numeric' => 'Regular fee must be a valid amount.',
            'regular_fee.min' => 'Regular fee cannot be negative.',
            'early_bird_fee.lt' => 'Early bird fee must be less than regular fee.',
            'early_bird_deadline.required_with' => 'Early bird deadline is required when early bird fee is set.',
            'early_bird_deadline.before' => 'Early bird deadline must be before seminar date.',
            'registration_deadline.before_or_equal' => 'Registration deadline must be on or before seminar date.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be jpeg, png, jpg, or webp format.',
            'image.max' => 'Image size cannot exceed 2MB.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'name' => 'seminar name',
            'type' => 'seminar type',
            'date' => 'seminar date',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'is_online' => 'seminar mode',
            'meeting_link' => 'meeting link',
            'regular_fee' => 'regular fee',
            'early_bird_fee' => 'early bird fee',
            'early_bird_deadline' => 'early bird deadline',
            'registration_deadline' => 'registration deadline',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox to boolean
        if ($this->has('is_online')) {
            $this->merge([
                'is_online' => $this->is_online == '1' || $this->is_online === 'on' || $this->is_online === true,
            ]);
        }
    }
}

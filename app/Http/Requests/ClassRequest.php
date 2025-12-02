<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('create-classes') || auth()->user()->can('edit-classes');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $classId = $this->route('class') ? $this->route('class')->id : null;

        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:classes,code,' . $classId,
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'type' => 'required|in:online,offline',
            'grade_level' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255|required_if:type,offline',
            'meeting_link' => 'nullable|url|max:500|required_if:type,online',
            'status' => 'sometimes|in:active,inactive,full',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Class name is required.',
            'name.max' => 'Class name cannot exceed 255 characters.',
            'code.unique' => 'This class code is already in use.',
            'subject_id.required' => 'Please select a subject.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'teacher_id.exists' => 'The selected teacher is invalid.',
            'type.required' => 'Please select class type (Online/Offline).',
            'type.in' => 'Class type must be either Online or Offline.',
            'capacity.required' => 'Class capacity is required.',
            'capacity.integer' => 'Capacity must be a number.',
            'capacity.min' => 'Capacity must be at least 1 student.',
            'capacity.max' => 'Capacity cannot exceed 100 students.',
            'location.required_if' => 'Location is required for offline classes.',
            'meeting_link.required_if' => 'Meeting link is required for online classes.',
            'meeting_link.url' => 'Please provide a valid meeting link URL.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null for nullable fields
        $this->merge([
            'teacher_id' => $this->teacher_id ?: null,
            'grade_level' => $this->grade_level ?: null,
            'description' => $this->description ?: null,
            'location' => $this->location ?: null,
            'meeting_link' => $this->meeting_link ?: null,
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'class name',
            'code' => 'class code',
            'subject_id' => 'subject',
            'teacher_id' => 'teacher',
            'type' => 'class type',
            'grade_level' => 'grade level',
            'capacity' => 'class capacity',
            'description' => 'description',
            'location' => 'location',
            'meeting_link' => 'meeting link',
            'status' => 'status',
        ];
    }
}

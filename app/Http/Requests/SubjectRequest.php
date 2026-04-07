<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['super-admin', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'name' => 'required|string|max:100',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('subjects', 'code')->ignore($subjectId),
            ],
            'description' => 'nullable|string|max:500',
            'grade_levels' => 'nullable|array',
            // CHANGED: validate as integer IDs referencing grade_levels table
            'grade_levels.*' => 'integer|exists:grade_levels,id',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Subject name is required.',
            'name.max' => 'Subject name cannot exceed 100 characters.',
            'code.required' => 'Subject code is required.',
            'code.unique' => 'This subject code is already in use.',
            'code.max' => 'Subject code cannot exceed 20 characters.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'grade_levels.array' => 'Grade levels must be an array.',
            'grade_levels.*.integer' => 'Each grade level must be a valid selection.',
            'grade_levels.*.exists' => 'Selected grade level does not exist.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'subject name',
            'code' => 'subject code',
            'grade_levels' => 'grade levels',
        ];
    }
}

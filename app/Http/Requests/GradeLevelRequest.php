<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeLevelRequest extends FormRequest
{
    /**
     * Only admin / super-admin may manage grade levels.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super-admin', 'admin']);
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        // On update, ignore the current record's own name in unique check
        $gradeLevel = $this->route('gradeLevel');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('grade_levels', 'name')->ignore($gradeLevel?->id),
            ],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Grade level name is required.',
            'name.unique'   => 'This grade level name already exists.',
            'name.max'      => 'Grade level name must not exceed 100 characters.',
        ];
    }
}

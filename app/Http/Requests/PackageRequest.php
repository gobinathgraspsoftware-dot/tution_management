<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
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
        $packageId = $this->route('package')?->id;

        $rules = [
            'name' => 'required|string|max:100',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('packages', 'code')->ignore($packageId),
            ],
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:online,offline,hybrid',
            'duration_months' => 'required|integer|min:1|max:24',
            'price' => 'required|numeric|min:0|max:99999.99',
            'online_fee' => 'nullable|numeric|min:0|max:999.99',
            'includes_materials' => 'boolean',
            'max_students' => 'nullable|integer|min:1|max:100',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'status' => 'required|in:active,inactive',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];

        // Add dynamic validation for sessions per subject
        if ($this->has('subjects')) {
            foreach ($this->subjects as $subjectId) {
                $rules["sessions_{$subjectId}"] = 'nullable|integer|min:1|max:30';
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Package name is required.',
            'name.max' => 'Package name cannot exceed 100 characters.',
            'code.required' => 'Package code is required.',
            'code.unique' => 'This package code is already in use.',
            'code.max' => 'Package code cannot exceed 20 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'type.required' => 'Package type is required.',
            'type.in' => 'Invalid package type selected.',
            'duration_months.required' => 'Duration is required.',
            'duration_months.min' => 'Duration must be at least 1 month.',
            'duration_months.max' => 'Duration cannot exceed 24 months.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price cannot exceed RM99,999.99.',
            'online_fee.numeric' => 'Online fee must be a valid number.',
            'online_fee.min' => 'Online fee cannot be negative.',
            'online_fee.max' => 'Online fee cannot exceed RM999.99.',
            'max_students.min' => 'Maximum students must be at least 1.',
            'max_students.max' => 'Maximum students cannot exceed 100.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'subjects.*.exists' => 'Selected subject does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'package name',
            'code' => 'package code',
            'duration_months' => 'duration',
            'online_fee' => 'online payment fee',
            'max_students' => 'maximum students',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set online_fee based on type
        if ($this->type === 'offline') {
            $this->merge(['online_fee' => null]);
        }
    }
}

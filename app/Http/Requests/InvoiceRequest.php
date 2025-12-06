<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
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
        $rules = [
            'student_id' => ['required', 'exists:students,id'],
            'enrollment_id' => ['nullable', 'exists:enrollments,id'],
            'type' => ['required', Rule::in(['registration', 'monthly', 'renewal', 'additional', 'custom'])],
            'billing_period_start' => ['required', 'date'],
            'billing_period_end' => ['required', 'date', 'after_or_equal:billing_period_start'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'online_fee' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // For updates, make some fields nullable
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['student_id'] = ['sometimes', 'exists:students,id'];
            $rules['type'] = ['sometimes', Rule::in(['registration', 'monthly', 'renewal', 'additional', 'custom'])];
            $rules['billing_period_start'] = ['sometimes', 'date'];
            $rules['billing_period_end'] = ['sometimes', 'date', 'after_or_equal:billing_period_start'];
            $rules['subtotal'] = ['sometimes', 'numeric', 'min:0'];
            $rules['due_date'] = ['sometimes', 'date'];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'student',
            'enrollment_id' => 'enrollment',
            'billing_period_start' => 'billing start date',
            'billing_period_end' => 'billing end date',
            'subtotal' => 'subtotal amount',
            'online_fee' => 'online fee',
            'due_date' => 'due date',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'The selected student does not exist.',
            'type.required' => 'Please select an invoice type.',
            'type.in' => 'Invalid invoice type selected.',
            'billing_period_start.required' => 'Please enter the billing start date.',
            'billing_period_end.required' => 'Please enter the billing end date.',
            'billing_period_end.after_or_equal' => 'The billing end date must be after or equal to the start date.',
            'subtotal.required' => 'Please enter the subtotal amount.',
            'subtotal.min' => 'The subtotal cannot be negative.',
            'due_date.required' => 'Please enter the due date.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults
        if (!$this->has('online_fee')) {
            $this->merge(['online_fee' => 0]);
        }

        if (!$this->has('discount')) {
            $this->merge(['discount' => 0]);
        }

        if (!$this->has('tax')) {
            $this->merge(['tax' => 0]);
        }
    }
}

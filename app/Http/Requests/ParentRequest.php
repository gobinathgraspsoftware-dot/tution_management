<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['super-admin', 'admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $parentId = $this->route('parent')?->id;
        $userId = $this->route('parent')?->user_id;

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'ic_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('parents', 'ic_number')->ignore($parentId),
            ],
            'occupation' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postcode' => 'required|string|max:10',
            'relationship' => 'required|in:father,mother,guardian',
            'whatsapp_number' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'notification_preference' => 'nullable|array',
            'notification_preference.whatsapp' => 'nullable|boolean',
            'notification_preference.email' => 'nullable|boolean',
            'notification_preference.sms' => 'nullable|boolean',
            'link_students' => 'nullable|array',
            'link_students.*' => 'exists:students,id',
            'status' => 'sometimes|in:active,inactive',
        ];

        // Email validation with unique check
        $rules['email'] = [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($userId),
        ];

        // Password rules - required on create, optional on update
        if ($this->isMethod('POST')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Parent name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'ic_number.required' => 'IC number is required.',
            'ic_number.unique' => 'This IC number is already registered.',
            'address.required' => 'Address is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'postcode.required' => 'Postcode is required.',
            'relationship.required' => 'Relationship to student is required.',
            'relationship.in' => 'Invalid relationship selected.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'link_students.*.exists' => 'One or more selected students do not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'ic_number' => 'IC number',
            'whatsapp_number' => 'WhatsApp number',
            'emergency_contact' => 'emergency contact name',
            'emergency_phone' => 'emergency contact phone',
            'notification_preference' => 'notification preferences',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        if ($this->has('notification_preference')) {
            $prefs = $this->notification_preference;
            $this->merge([
                'notification_preference' => [
                    'whatsapp' => isset($prefs['whatsapp']),
                    'email' => isset($prefs['email']),
                    'sms' => isset($prefs['sms']),
                ],
            ]);
        }
    }
}

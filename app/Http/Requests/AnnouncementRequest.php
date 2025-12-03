<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,class,urgent,event',
            'target_audience' => 'required|in:all,students,parents,teachers,staff,specific_class',
            'target_class_id' => 'nullable|required_if:target_audience,specific_class|exists:classes,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'status' => 'required|in:draft,published,archived',
            'publish_at' => 'nullable|date|after_or_equal:today',
            'expires_at' => 'nullable|date|after:publish_at',
            'is_pinned' => 'nullable|boolean',
            'attachment_files.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Announcement title is required.',
            'content.required' => 'Announcement content is required.',
            'type.required' => 'Announcement type is required.',
            'target_audience.required' => 'Target audience is required.',
            'target_class_id.required_if' => 'Please select a class when targeting specific class.',
            'priority.required' => 'Priority level is required.',
            'status.required' => 'Status is required.',
            'publish_at.after_or_equal' => 'Publish date must be today or a future date.',
            'expires_at.after' => 'Expiry date must be after publish date.',
            'attachment_files.*.max' => 'Each file must not exceed 10MB.',
            'attachment_files.*.mimes' => 'Only PDF, DOC, DOCX, JPG, JPEG, PNG files are allowed.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'target_class_id' => 'target class',
            'publish_at' => 'publish date',
            'expires_at' => 'expiry date',
            'is_pinned' => 'pin status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_pinned' => $this->has('is_pinned') ? true : false,
        ]);
    }
}

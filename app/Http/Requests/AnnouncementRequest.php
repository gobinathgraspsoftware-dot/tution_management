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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:general,class,urgent,event'],
            'target_audience' => ['required', 'in:all,students,parents,teachers,staff,specific_class'],
            'target_class_id' => ['nullable', 'required_if:target_audience,specific_class', 'exists:classes,id'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'status' => ['required', 'in:draft,published,archived'],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:publish_at'],
            'is_pinned' => ['nullable', 'boolean'],
            'attachment_files' => ['nullable', 'array'],
            'attachment_files.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please enter an announcement title.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'content.required' => 'Please enter announcement content.',
            'type.required' => 'Please select an announcement type.',
            'type.in' => 'Invalid announcement type selected.',
            'target_audience.required' => 'Please select the target audience.',
            'target_audience.in' => 'Invalid target audience selected.',
            'target_class_id.required_if' => 'Please select a class when targeting a specific class.',
            'target_class_id.exists' => 'The selected class does not exist.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'Invalid priority level selected.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
            'publish_at.date' => 'Please enter a valid publish date.',
            'expires_at.date' => 'Please enter a valid expiry date.',
            'expires_at.after' => 'Expiry date must be after the publish date.',
            'attachment_files.*.max' => 'Each attachment must not exceed 10MB.',
            'attachment_files.*.mimes' => 'Invalid file type. Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'announcement title',
            'content' => 'announcement content',
            'type' => 'announcement type',
            'target_audience' => 'target audience',
            'target_class_id' => 'target class',
            'priority' => 'priority level',
            'status' => 'status',
            'publish_at' => 'publish date',
            'expires_at' => 'expiry date',
            'attachment_files' => 'attachments',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert is_pinned checkbox to boolean
        $this->merge([
            'is_pinned' => $this->has('is_pinned') ? true : false,
        ]);

        // Clear target_class_id if not targeting specific class
        if ($this->target_audience !== 'specific_class') {
            $this->merge([
                'target_class_id' => null,
            ]);
        }
    }
}

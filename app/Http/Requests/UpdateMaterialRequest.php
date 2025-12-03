<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
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
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'type' => 'required|in:notes,presentation,video,document,other',
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,mp4,avi,mov', // 50MB max
            'description' => 'nullable|string',
            'access_type' => 'required|in:view_only,downloadable',
            'publish_date' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'class',
            'subject_id' => 'subject',
            'teacher_id' => 'teacher',
            'publish_date' => 'publish date',
            'access_type' => 'access type',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'file.max' => 'File size must not exceed 50MB.',
            'file.mimes' => 'File must be a PDF, Word, PowerPoint, or Video file.',
        ];
    }
}

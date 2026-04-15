<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'grade_level_id' => 'required|exists:grade_levels,id',   // ADDED
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'type' => 'required|in:notes,presentation,video,document,worksheet,assignment,reference,other',
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp4,avi,mov',
            'description' => 'nullable|string',
            'access_type' => 'required|in:view_only,downloadable',
            'publish_date' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ];
    }

    public function attributes(): array
    {
        return [
            'grade_level_id' => 'grade level',
            'class_id' => 'class',
            'subject_id' => 'subject',
            'teacher_id' => 'teacher',
            'publish_date' => 'publish date',
            'access_type' => 'access type',
        ];
    }

    public function messages(): array
    {
        return [
            'grade_level_id.required' => 'Please select a grade level.',
            'file.max' => 'File size must not exceed 50MB.',
            'file.mimes' => 'File must be a PDF, Word, PowerPoint, Excel, or Video file.',
        ];
    }
}

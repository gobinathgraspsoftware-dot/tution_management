<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;;

class MessageTemplateController extends Controller
{
    /**
     * Display listing of templates
     */
    public function index(Request $request)
    {
        $query = MessageTemplate::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('message_body', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('category')->orderBy('name')->paginate(15);

        $categories = [
            'payment_reminder' => 'Payment Reminder',
            'welcome' => 'Welcome',
            'attendance' => 'Attendance',
            'exam_result' => 'Exam Result',
            'announcement' => 'Announcement',
            'trial_class' => 'Trial Class',
            'enrollment' => 'Enrollment',
            'other' => 'Other',
        ];

        $channels = [
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'sms' => 'SMS',
            'all' => 'All Channels',
        ];

        return view('admin.templates.index', compact('templates', 'categories', 'channels'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = [
            'payment_reminder' => 'Payment Reminder',
            'welcome' => 'Welcome',
            'attendance' => 'Attendance',
            'exam_result' => 'Exam Result',
            'announcement' => 'Announcement',
            'trial_class' => 'Trial Class',
            'enrollment' => 'Enrollment',
            'other' => 'Other',
        ];

        $channels = [
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'sms' => 'SMS',
            'all' => 'All Channels',
        ];

        $variables = config('notification.variables', []);

        return view('admin.templates.create', compact('categories', 'channels', 'variables'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:message_templates,name',
            'category' => 'required|string|in:payment_reminder,welcome,attendance,exam_result,announcement,trial_class,enrollment,other',
            'channel' => 'required|string|in:whatsapp,email,sms,all',
            'subject' => 'nullable|required_if:channel,email|string|max:255',
            'message_body' => 'required|string|max:2000',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['variables'] = $request->variables ?? [];

        MessageTemplate::create($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Show template details
     */
    public function show(MessageTemplate $template)
    {
        return view('admin.templates.show', compact('template'));
    }

    /**
     * Show edit form
     */
    public function edit(MessageTemplate $template)
    {
        $categories = [
            'payment_reminder' => 'Payment Reminder',
            'welcome' => 'Welcome',
            'attendance' => 'Attendance',
            'exam_result' => 'Exam Result',
            'announcement' => 'Announcement',
            'trial_class' => 'Trial Class',
            'enrollment' => 'Enrollment',
            'other' => 'Other',
        ];

        $channels = [
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'sms' => 'SMS',
            'all' => 'All Channels',
        ];

        $variables = config('notification.variables', []);

        return view('admin.templates.edit', compact('template', 'categories', 'channels', 'variables'));
    }

    /**
     * Update template
     */
    public function update(Request $request, MessageTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:message_templates,name,' . $template->id,
            'category' => 'required|string|in:payment_reminder,welcome,attendance,exam_result,announcement,trial_class,enrollment,other',
            'channel' => 'required|string|in:whatsapp,email,sms,all',
            'subject' => 'nullable|required_if:channel,email|string|max:255',
            'message_body' => 'required|string|max:2000',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['variables'] = $request->variables ?? [];

        $template->update($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Delete template
     */
    public function destroy(MessageTemplate $template)
    {
        // Check if template is in use
        $inUseWhatsapp = $template->whatsappQueue()->exists();
        $inUseEmail = $template->emailQueue()->exists();

        if ($inUseWhatsapp || $inUseEmail) {
            return back()->with('error', 'Cannot delete template that is in use.');
        }

        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Toggle template status
     */
    public function toggleStatus(MessageTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);

        $status = $template->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Template {$status} successfully.");
    }

    /**
     * Duplicate template
     */
    public function duplicate(MessageTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->is_active = false;
        $newTemplate->save();

        return redirect()->route('admin.templates.edit', $newTemplate)
            ->with('success', 'Template duplicated. Please update the name.');
    }

    /**
     * Preview template with sample data
     */
    public function preview(Request $request, MessageTemplate $template)
    {
        $sampleData = [
            'student_name' => 'Ahmad bin Ali',
            'parent_name' => 'Ali bin Abu',
            'teacher_name' => 'Cikgu Siti',
            'class_name' => 'SPM Mathematics',
            'subject_name' => 'Additional Mathematics',
            'amount' => '250.00',
            'due_date' => now()->addDays(7)->format('d M Y'),
            'invoice_number' => 'INV-2024-001234',
            'attendance_date' => now()->format('d M Y'),
            'attendance_status' => 'Present',
            'exam_name' => 'Mid-Year Examination',
            'exam_date' => now()->addDays(14)->format('d M Y'),
            'score' => '85',
            'grade' => 'A',
            'trial_date' => now()->addDays(3)->format('d M Y'),
            'trial_time' => '10:00 AM',
            'center_name' => 'Arena Matriks Edu Group',
            'center_phone' => '03-1234 5678',
            'reset_link' => url('/reset-password/sample-token'),
            'login_link' => url('/login'),
        ];

        $message = $template->message_body;
        $subject = $template->subject ?? '';

        foreach ($sampleData as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
            $subject = str_replace("{{$key}}", $value, $subject);
        }

        if ($request->ajax()) {
            return response()->json([
                'subject' => $subject,
                'message' => $message,
            ]);
        }

        return view('admin.templates.preview', compact('template', 'message', 'subject'));
    }
}

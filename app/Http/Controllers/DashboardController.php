<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;

class DashboardController extends Controller
{
    /**
     * Admin/Super Admin Dashboard
     */
    public function adminDashboard()
    {
        $data = [
            'total_students' => Student::approved()->count(),
            'pending_approvals' => Student::pending()->count(),
            'total_teachers' => Teacher::active()->count(),
            'active_classes' => ClassModel::active()->count(),
            'total_revenue_month' => Payment::completed()->thisMonth()->sum('amount'),
            'pending_payments' => Invoice::pending()->count(),
            'recent_enrollments' => Enrollment::with(['student.user', 'package'])->latest()->take(5)->get(),
            'recent_payments' => Payment::with(['student.user', 'invoice'])->completed()->latest()->take(5)->get(),
        ];

        return view('dashboards.admin', $data);
    }

    /**
     * Staff Dashboard
     */
    public function staffDashboard()
    {
        $data = [
            'total_students' => Student::approved()->count(),
            'active_classes' => ClassModel::active()->count(),
            'today_sessions' => \App\Models\ClassSession::today()->count(),
            'pending_payments' => Invoice::pending()->count(),
        ];

        return view('dashboards.staff', $data);
    }

    /**
     * Teacher Dashboard
     */
    public function teacherDashboard()
    {
        $teacher = auth()->user()->teacher;

        $data = [
            'my_classes' => $teacher->classes()->with('subject')->get(),
            'today_classes' => $teacher->classes()
                ->whereHas('sessions', function($q) {
                    $q->today();
                })
                ->count(),
            'total_students' => $teacher->classes()
                ->withCount('enrollments')
                ->get()
                ->sum('enrollments_count'),
            'recent_materials' => $teacher->materials()->latest()->take(5)->get(),
        ];

        return view('dashboards.teacher', $data);
    }

    /**
     * Parent Dashboard
     */
    public function parentDashboard()
    {
        $parent = auth()->user()->parent;

        $data = [
            'children' => $parent->students()->with(['enrollments.package', 'enrollments.class'])->get(),
            'pending_invoices' => Invoice::whereIn('student_id', $parent->students->pluck('id'))
                ->pending()
                ->with('student.user')
                ->get(),
            'recent_payments' => Payment::whereIn('student_id', $parent->students->pluck('id'))
                ->completed()
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('dashboards.parent', $data);
    }

    /**
     * Student Dashboard
     */
    public function studentDashboard()
    {
        $student = auth()->user()->student;

        $data = [
            'enrollments' => $student->enrollments()
                ->active()
                ->with(['package', 'class.teacher.user', 'class.subject'])
                ->get(),
            'recent_attendance' => $student->attendance()
                ->with('classSession.class')
                ->latest()
                ->take(10)
                ->get(),
            'recent_materials' => \App\Models\Material::whereHas('class.enrollments', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                ->published()
                ->latest()
                ->take(5)
                ->get(),
            'upcoming_exams' => \App\Models\Exam::whereHas('class.enrollments', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                ->upcoming()
                ->with('class', 'subject')
                ->get(),
        ];

        return view('dashboards.student', $data);
    }
}

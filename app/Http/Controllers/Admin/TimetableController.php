<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\ClassSchedule;
use App\Models\Teacher;
use App\Models\Student;
use App\Services\TimetableService;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    protected $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
        // $this->middleware('permission:view-timetable');
    }

    /**
     * Display timetable dashboard.
     * FIX: Added class_id / teacher_id server-side filtering support.
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'weekly');
        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');

        $user = auth()->user();
        $timetableData = [];

        // ── Admin / Staff view ──────────────────────────────────────
        if ($user->hasRole(['super-admin', 'admin', 'staff'])) {

            $classes  = ClassModel::active()->with('subject', 'teacher.user')->get();
            $teachers = Teacher::active()->with('user')->get();

            // FIX: Combined class + teacher filter (AND logic, both optional)
            $timetableData = $this->timetableService->getFilteredTimetable(
                $request->class_id,    // null when "All Classes"
                $request->teacher_id,  // null when "All Teachers"
                $view,
                $date
            );

            return view('admin.timetable.index', compact(
                'timetableData', 'view', 'date', 'classes', 'teachers'
            ));
        }

        // ── Teacher view ────────────────────────────────────────────
        elseif ($user->hasRole('teacher')) {
            $teacher = $user->teacher;
            $timetableData = $this->timetableService->getTeacherTimetable($teacher->id, $view, $date);

            return view('teacher.timetable.index', compact('timetableData', 'view', 'date'));
        }

        // ── Student view ────────────────────────────────────────────
        elseif ($user->hasRole('student')) {
            $student = $user->student;
            $timetableData = $this->timetableService->getStudentTimetable($student->id, $view, $date);

            return view('student.timetable.index', compact('timetableData', 'view', 'date'));
        }

        // ── Parent view ─────────────────────────────────────────────
        elseif ($user->hasRole('parent')) {
            $parent   = $user->parent;
            $children = $parent->students;

            $selectedStudent = $request->filled('student_id')
                ? $children->find($request->student_id)
                : $children->first();

            if ($selectedStudent) {
                $timetableData = $this->timetableService->getStudentTimetable(
                    $selectedStudent->id, $view, $date
                );
            }

            return view('parent.timetable.index', compact(
                'timetableData', 'view', 'date', 'children', 'selectedStudent'
            ));
        }

        return redirect()->route('dashboard');
    }

    /**
     * Filter timetable by class (AJAX fallback).
     */
    public function filterByClass(Request $request)
    {
        $classId = $request->class_id;
        $view    = $request->get('view', 'weekly');
        $date    = $request->filled('date') ? $request->date : now()->format('Y-m-d');

        $timetableData = $this->timetableService->getClassTimetable($classId, $view, $date);

        return response()->json($timetableData);
    }

    /**
     * Filter timetable by teacher (AJAX fallback).
     */
    public function filterByTeacher(Request $request)
    {
        $teacherId = $request->teacher_id;
        $view      = $request->get('view', 'weekly');
        $date      = $request->filled('date') ? $request->date : now()->format('Y-m-d');

        $timetableData = $this->timetableService->getTeacherTimetable($teacherId, $view, $date);

        return response()->json($timetableData);
    }

    /**
     * Export timetable.
     */
    public function export(Request $request)
    {
        $view   = $request->get('view', 'weekly');
        $date   = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $format = $request->get('format', 'pdf');

        $user = auth()->user();

        if ($user->hasRole('teacher')) {
            $timetableData = $this->timetableService->getTeacherTimetable($user->teacher->id, $view, $date);
            $filename = 'teacher_timetable_' . $date;
        } elseif ($user->hasRole('student')) {
            $timetableData = $this->timetableService->getStudentTimetable($user->student->id, $view, $date);
            $filename = 'student_timetable_' . $date;
        } else {
            // Admin: respect combined filters in export too
            $timetableData = $this->timetableService->getFilteredTimetable(
                $request->class_id,
                $request->teacher_id,
                $view,
                $date
            );
            $filename = 'timetable_' . $date;
        }

        if ($format === 'pdf') {
            return $this->timetableService->exportToPdf($timetableData, $view, $filename);
        } else {
            return $this->timetableService->exportToCsv($timetableData, $view, $filename);
        }
    }

    /**
     * Print timetable.
     */
    public function print(Request $request)
    {
        $view = $request->get('view', 'weekly');
        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');

        $user = auth()->user();

        if ($user->hasRole('teacher')) {
            $timetableData = $this->timetableService->getTeacherTimetable($user->teacher->id, $view, $date);
        } elseif ($user->hasRole('student')) {
            $timetableData = $this->timetableService->getStudentTimetable($user->student->id, $view, $date);
        } else {
            $timetableData = $this->timetableService->getFilteredTimetable(
                $request->class_id,
                $request->teacher_id,
                $view,
                $date
            );
        }

        return view('admin.timetable.print', compact('timetableData', 'view', 'date'));
    }
}

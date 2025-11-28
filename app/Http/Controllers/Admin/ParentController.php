<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parents;
use App\Models\User;
use App\Models\Student;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    /**
     * Display a listing of parents.
     */
    public function index(Request $request)
    {
        $query = Parents::with(['user', 'students.user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('parent_id', 'like', "%{$search}%")
              ->orWhere('ic_number', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $parents = $query->latest()->paginate(15)->withQueryString();

        // Get unique cities for filter
        $cities = Parents::distinct()->pluck('city')->filter()->values();

        return view('admin.parents.index', compact('parents', 'cities'));
    }

    /**
     * Show the form for creating a new parent.
     */
    public function create()
    {
        // Get students without parents for linking
        $unlinkedStudents = Student::whereNull('parent_id')
            ->with('user')
            ->get();
        // $query = Parents::with(['user', 'students.user']);
        // $parents = $query->latest()->paginate(15)->withQueryString();

        // $cities = Parents::distinct()->pluck('city')->filter()->values();

        return view('admin.parents.create', compact('unlinkedStudents'));
    }

    /**
     * Store a newly created parent.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'ic_number' => 'required|string|max:20|unique:parents,ic_number',
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
            'notification_preference.*' => 'in:whatsapp,email,sms',
            'status' => 'required|in:active,inactive',
            'link_students' => 'nullable|array',
            'link_students.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();
        try {
            // Create User account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
                'email_verified_at' => now(),
            ]);

            // Assign parent role
            $user->assignRole('parent');

            // Generate parent ID
            $parentId = 'PAR-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Build notification preferences
            $notificationPrefs = [];
            if (isset($validated['notification_preference'])) {
                foreach (['whatsapp', 'email', 'sms'] as $channel) {
                    $notificationPrefs[$channel] = in_array($channel, $validated['notification_preference']);
                }
            } else {
                $notificationPrefs = ['whatsapp' => true, 'email' => true, 'sms' => false];
            }

            // Create Parent profile
            $parent = Parents::create([
                'user_id' => $user->id,
                'parent_id' => $parentId,
                'ic_number' => $validated['ic_number'],
                'occupation' => $validated['occupation'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'relationship' => $validated['relationship'],
                'whatsapp_number' => $validated['whatsapp_number'] ?? $validated['phone'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $validated['emergency_phone'],
                'notification_preference' => $notificationPrefs,
            ]);

            // Link students to parent
            if (!empty($validated['link_students'])) {
                Student::whereIn('id', $validated['link_students'])
                    ->update(['parent_id' => $parent->id]);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Parent',
                'model_id' => $parent->id,
                'description' => 'Created parent: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.parents.index')
                ->with('success', 'Parent created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create parent. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified parent.
     */
    public function show(Parents $parent)
    {
        $parent->load(['user', 'students.user', 'students.enrollments.package']);

        // Get payment summary for parent's children
        $childrenIds = $parent->students->pluck('id');
        $paymentStats = [
            'total_paid' => \App\Models\Payment::whereIn('student_id', $childrenIds)
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_invoices' => \App\Models\Invoice::whereIn('student_id', $childrenIds)
                ->whereIn('status', ['pending', 'partial'])
                ->sum('total_amount'),
        ];

        return view('admin.parents.show', compact('parent', 'paymentStats'));
    }

    /**
     * Show the form for editing the specified parent.
     */
    public function edit(Parents $parent)
    {
        $parent->load(['user', 'students']);

        // Get students that can be linked (unlinked or already linked to this parent)
        $availableStudents = Student::where(function ($q) use ($parent) {
            $q->whereNull('parent_id')
              ->orWhere('parent_id', $parent->id);
        })->with('user')->get();

        return view('admin.parents.edit', compact('parent', 'availableStudents'));
    }

    /**
     * Update the specified parent.
     */
    public function update(Request $request, Parents $parent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($parent->user_id)],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'ic_number' => ['required', 'string', 'max:20', Rule::unique('parents', 'ic_number')->ignore($parent->id)],
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
            'notification_preference.*' => 'in:whatsapp,email,sms',
            'status' => 'required|in:active,inactive',
            'link_students' => 'nullable|array',
            'link_students.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();
        try {
            // Update User account
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $parent->user->update($userData);

            // Build notification preferences
            $notificationPrefs = [];
            if (isset($validated['notification_preference'])) {
                foreach (['whatsapp', 'email', 'sms'] as $channel) {
                    $notificationPrefs[$channel] = in_array($channel, $validated['notification_preference']);
                }
            } else {
                $notificationPrefs = $parent->notification_preference ?? ['whatsapp' => true, 'email' => true, 'sms' => false];
            }

            // Update Parent profile
            $parent->update([
                'ic_number' => $validated['ic_number'],
                'occupation' => $validated['occupation'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'relationship' => $validated['relationship'],
                'whatsapp_number' => $validated['whatsapp_number'] ?? $validated['phone'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $validated['emergency_phone'],
                'notification_preference' => $notificationPrefs,
            ]);

            // Update student links
            // First, unlink all students currently linked to this parent
            Student::where('parent_id', $parent->id)->update(['parent_id' => null]);

            // Then link the selected students
            if (!empty($validated['link_students'])) {
                Student::whereIn('id', $validated['link_students'])
                    ->update(['parent_id' => $parent->id]);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Parent',
                'model_id' => $parent->id,
                'description' => 'Updated parent: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.parents.index')
                ->with('success', 'Parent updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update parent. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified parent (soft delete).
     */
    public function destroy(Request $request, Parents $parent)
    {
        // Check if parent has linked students with active enrollments
        $hasActiveStudents = $parent->students()
            ->whereHas('enrollments', function ($q) {
                $q->where('status', 'active');
            })->exists();

        if ($hasActiveStudents) {
            return back()->with('error', 'Cannot delete parent with children having active enrollments.');
        }

        DB::beginTransaction();
        try {
            $parentName = $parent->user->name;

            // Unlink students
            $parent->students()->update(['parent_id' => null]);

            // Soft delete parent and user
            $parent->delete();
            $parent->user->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Parent',
                'model_id' => $parent->id,
                'description' => 'Deleted parent: ' . $parentName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.parents.index')
                ->with('success', 'Parent deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete parent. ' . $e->getMessage());
        }
    }

    /**
     * Export parents list to CSV.
     */
    public function export(Request $request)
    {
        $parents = Parents::with(['user', 'students'])->get();

        $filename = 'parents_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($parents) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Parent ID', 'Name', 'Email', 'Phone', 'IC Number',
                'Relationship', 'City', 'State', 'Children Count', 'Status'
            ]);

            // Data rows
            foreach ($parents as $p) {
                fputcsv($file, [
                    $p->parent_id,
                    $p->user->name,
                    $p->user->email,
                    $p->user->phone,
                    $p->ic_number,
                    $p->relationship,
                    $p->city,
                    $p->state,
                    $p->students->count(),
                    $p->user->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

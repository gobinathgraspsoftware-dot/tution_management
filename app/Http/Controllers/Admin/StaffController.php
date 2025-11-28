<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members.
     */
    public function index(Request $request)
    {
        $query = Staff::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('staff_id', 'like', "%{$search}%")
              ->orWhere('ic_number', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Filter by position
        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        $staff = $query->latest()->paginate(15)->withQueryString();

        // Get unique departments and positions for filters
        $departments = Staff::distinct()->pluck('department')->filter()->values();
        $positions = Staff::distinct()->pluck('position')->filter()->values();

        return view('admin.staff.index', compact('staff', 'departments', 'positions'));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'ic_number' => 'required|string|max:20|unique:staff,ic_number',
            'address' => 'nullable|string|max:500',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'join_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
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

            // Assign staff role
            $user->assignRole('staff');

            // Generate staff ID
            $staffId = 'STF-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Create Staff profile
            Staff::create([
                'user_id' => $user->id,
                'staff_id' => $staffId,
                'ic_number' => $validated['ic_number'],
                'address' => $validated['address'],
                'position' => $validated['position'],
                'department' => $validated['department'],
                'join_date' => $validated['join_date'],
                'salary' => $validated['salary'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $validated['emergency_phone'],
                'notes' => $validated['notes'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Staff',
                'model_id' => $user->staff->id,
                'description' => 'Created staff member: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create staff member. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show(Staff $staff)
    {
        $staff->load('user');
        return view('admin.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(Staff $staff)
    {
        $staff->load('user');
        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($staff->user_id)],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'ic_number' => ['required', 'string', 'max:20', Rule::unique('staff', 'ic_number')->ignore($staff->id)],
            'address' => 'nullable|string|max:500',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'join_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
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

            $staff->user->update($userData);

            // Update Staff profile
            $staff->update([
                'ic_number' => $validated['ic_number'],
                'address' => $validated['address'],
                'position' => $validated['position'],
                'department' => $validated['department'],
                'join_date' => $validated['join_date'],
                'salary' => $validated['salary'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $validated['emergency_phone'],
                'notes' => $validated['notes'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Staff',
                'model_id' => $staff->id,
                'description' => 'Updated staff member: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update staff member. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff member (soft delete).
     */
    public function destroy(Request $request, Staff $staff)
    {
        DB::beginTransaction();
        try {
            $staffName = $staff->user->name;

            // Soft delete staff and user
            $staff->delete();
            $staff->user->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Staff',
                'model_id' => $staff->id,
                'description' => 'Deleted staff member: ' . $staffName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete staff member. ' . $e->getMessage());
        }
    }

    /**
     * Export staff list to CSV.
     */
    public function export(Request $request)
    {
        $staff = Staff::with('user')->get();

        $filename = 'staff_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($staff) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Staff ID', 'Name', 'Email', 'Phone', 'IC Number',
                'Position', 'Department', 'Join Date', 'Status'
            ]);

            // Data rows
            foreach ($staff as $s) {
                fputcsv($file, [
                    $s->staff_id,
                    $s->user->name,
                    $s->user->email,
                    $s->user->phone,
                    $s->ic_number,
                    $s->position,
                    $s->department,
                    $s->join_date?->format('Y-m-d'),
                    $s->user->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

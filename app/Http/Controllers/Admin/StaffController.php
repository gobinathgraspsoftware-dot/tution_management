<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\CountryCodeHelper;
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
        // Get all countries for dropdown
        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        return view('admin.staff.create', compact('countries', 'defaultCountryCode'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'ic_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleaned = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleaned) !== 12) {
                        $fail('The IC number must be exactly 12 digits.');
                    }
                    if (!preg_match('/^[0-9]+$/', $cleaned)) {
                        $fail('The IC number must contain only numeric digits.');
                    }
                },
                Rule::unique('staff', 'ic_number')->where(function ($query) use ($request) {
                    $cleaned = preg_replace('/[^0-9]/', '', $request->ic_number);
                    return $query->where('ic_number', $cleaned);
                })
            ],
            'address' => 'nullable|string|max:500',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'join_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_country_code' => 'nullable|string|max:5',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            // Convert name to UPPERCASE
            $name = strtoupper($validated['name']);

            // Clean IC number (remove hyphens, store only 12 digits)
            $cleanedIcNumber = preg_replace('/[^0-9]/', '', $validated['ic_number']);

            // Format phone number with country code
            $phoneNumber = CountryCodeHelper::formatPhoneNumber(
                $validated['country_code'],
                $validated['phone']
            );

            // Emergency phone handling
            $emergencyPhone = null;
            if (!empty($validated['emergency_phone'])) {
                $emergencyCountryCode = $validated['emergency_country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $emergencyPhone = CountryCodeHelper::formatPhoneNumber(
                    $emergencyCountryCode,
                    $validated['emergency_phone']
                );
            }

            // Create User account
            $user = User::create([
                'name' => $name,
                'email' => $validated['email'],
                'phone' => $phoneNumber,
                'password' => Hash::make($validated['password']),
                'password_view' => $validated['password'], // Store plain password for viewing
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
                'ic_number' => $cleanedIcNumber,
                'address' => $validated['address'],
                'position' => $validated['position'],
                'department' => $validated['department'],
                'join_date' => $validated['join_date'],
                'salary' => $validated['salary'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $emergencyPhone,
                'notes' => $validated['notes'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Staff',
                'model_id' => $user->staff->id,
                'description' => 'Created staff member: ' . $name,
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

        // Get all countries for dropdown
        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        // Extract country code from phone number
        $phoneData = CountryCodeHelper::extractCountryCode($staff->user->phone);
        $selectedCountryCode = $phoneData['country_code'];
        $phoneNumber = $phoneData['number'];

        // Extract country code from emergency phone
        $emergencyCountryCode = $defaultCountryCode;
        $emergencyPhoneNumber = '';
        if ($staff->emergency_phone) {
            $emergencyPhoneData = CountryCodeHelper::extractCountryCode($staff->emergency_phone);
            $emergencyCountryCode = $emergencyPhoneData['country_code'];
            $emergencyPhoneNumber = $emergencyPhoneData['number'];
        }

        return view('admin.staff.edit', compact(
            'staff',
            'countries',
            'defaultCountryCode',
            'selectedCountryCode',
            'phoneNumber',
            'emergencyCountryCode',
            'emergencyPhoneNumber'
        ));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($staff->user_id)],
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'ic_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleaned = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleaned) !== 12) {
                        $fail('The IC number must be exactly 12 digits.');
                    }
                    if (!preg_match('/^[0-9]/', $cleaned)) {
                        $fail('The IC number must contain only numeric digits.');
                    }
                },
                Rule::unique('staff', 'ic_number')->ignore($staff->id)->where(function ($query) use ($request) {
                    $cleaned = preg_replace('/[^0-9]/', '', $request->ic_number);
                    return $query->where('ic_number', $cleaned);
                })
            ],
            'address' => 'nullable|string|max:500',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'join_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_country_code' => 'nullable|string|max:5',
            'emergency_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            // Convert name to UPPERCASE
            $name = strtoupper($validated['name']);

            // Clean IC number (remove hyphens, store only 12 digits)
            $cleanedIcNumber = preg_replace('/[^0-9]/', '', $validated['ic_number']);

            // Format phone number with country code
            $phoneNumber = CountryCodeHelper::formatPhoneNumber(
                $validated['country_code'],
                $validated['phone']
            );

            // Emergency phone handling
            $emergencyPhone = null;
            if (!empty($validated['emergency_phone'])) {
                $emergencyCountryCode = $validated['emergency_country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $emergencyPhone = CountryCodeHelper::formatPhoneNumber(
                    $emergencyCountryCode,
                    $validated['emergency_phone']
                );
            }

            // Update User account
            $userData = [
                'name' => $name,
                'email' => $validated['email'],
                'phone' => $phoneNumber,
                'status' => $validated['status'],
            ];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
                $userData['password_view'] = $validated['password'];
            }

            $staff->user->update($userData);

            // Update Staff profile
            $staff->update([
                'ic_number' => $cleanedIcNumber,
                'address' => $validated['address'],
                'position' => $validated['position'],
                'department' => $validated['department'],
                'join_date' => $validated['join_date'],
                'salary' => $validated['salary'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $emergencyPhone,
                'notes' => $validated['notes'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Staff',
                'model_id' => $staff->id,
                'description' => 'Updated staff member: ' . $name,
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
                    $this->formatIcNumber($s->ic_number),
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

    /**
     * Format IC number for display (add hyphens)
     */
    private function formatIcNumber($icNumber)
    {
        if (strlen($icNumber) === 12) {
            return substr($icNumber, 0, 6) . '-' . substr($icNumber, 6, 2) . '-' . substr($icNumber, 8);
        }
        return $icNumber;
    }
}

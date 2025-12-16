<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parents;
use App\Models\User;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Helpers\CountryCodeHelper;
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

        // Get all countries for dropdown
        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        return view('admin.parents.create', compact('unlinkedStudents', 'countries', 'defaultCountryCode'));
    }

    /**
     * Store a newly created parent.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'ic_number' => 'required|string|max:20|unique:parents,ic_number',
            'occupation' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postcode' => 'required|string|max:10',
            'relationship' => 'required|in:father,mother,guardian,other',
            'relationship_description' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_country_code' => 'nullable|string|max:5',
            'emergency_phone' => 'nullable|string|max:20',
            'email_notifications' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'link_students' => 'nullable|array',
            'link_students.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();
        try {
            // Convert name to uppercase
            $validated['name'] = strtoupper($validated['name']);

            // Convert occupation to uppercase if provided
            if (!empty($validated['occupation'])) {
                $validated['occupation'] = strtoupper($validated['occupation']);
            }

            // Convert address and city to uppercase
            $validated['address'] = strtoupper($validated['address']);
            $validated['city'] = strtoupper($validated['city']);

            // Format phone numbers using helper
            $phoneNumber = CountryCodeHelper::formatPhoneNumber(
                $validated['country_code'],
                $validated['phone']
            );

            // Emergency phone handling
            if (!empty($validated['emergency_phone'])) {
                $emergencyCountryCode = $validated['emergency_country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $emergencyPhone = CountryCodeHelper::formatPhoneNumber(
                    $emergencyCountryCode,
                    $validated['emergency_phone']
                );
            } else {
                $emergencyPhone = null;
            }

            // Create User account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $phoneNumber,
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
                'email_verified_at' => now(),
            ]);

            // Assign parent role
            $user->assignRole('parent');

            // Generate parent ID
            $parentId = 'PAR-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Build notification preferences (email only)
            $notificationPrefs = [
                'email' => $validated['email_notifications'] ?? true,
            ];

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
                'relationship_description' => $validated['relationship_description'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $emergencyPhone,
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

        // Extract country code from phone numbers for display using helper
        $phoneData = CountryCodeHelper::extractCountryCode($parent->user->phone);
        $emergencyData = CountryCodeHelper::extractCountryCode($parent->emergency_phone);

        // Get all countries for dropdown
        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        return view('admin.parents.edit', compact(
            'parent',
            'availableStudents',
            'phoneData',
            'emergencyData',
            'countries',
            'defaultCountryCode'
        ));
    }

    /**
     * Update the specified parent.
     */
    public function update(Request $request, Parents $parent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($parent->user_id)],
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'ic_number' => ['required', 'string', 'max:20', Rule::unique('parents', 'ic_number')->ignore($parent->id)],
            'occupation' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postcode' => 'required|string|max:10',
            'relationship' => 'required|in:father,mother,guardian,other',
            'relationship_description' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_country_code' => 'nullable|string|max:5',
            'emergency_phone' => 'nullable|string|max:20',
            'email_notifications' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'link_students' => 'nullable|array',
            'link_students.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();
        try {
            // Convert name to uppercase
            $validated['name'] = strtoupper($validated['name']);

            // Convert occupation to uppercase if provided
            if (!empty($validated['occupation'])) {
                $validated['occupation'] = strtoupper($validated['occupation']);
            }

            // Convert address and city to uppercase
            $validated['address'] = strtoupper($validated['address']);
            $validated['city'] = strtoupper($validated['city']);

            // Format phone numbers using helper
            $phoneNumber = CountryCodeHelper::formatPhoneNumber(
                $validated['country_code'],
                $validated['phone']
            );

            // Emergency phone handling
            if (!empty($validated['emergency_phone'])) {
                $emergencyCountryCode = $validated['emergency_country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $emergencyPhone = CountryCodeHelper::formatPhoneNumber(
                    $emergencyCountryCode,
                    $validated['emergency_phone']
                );
            } else {
                $emergencyPhone = null;
            }

            // Update User account
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $phoneNumber,
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $parent->user->update($userData);

            // Build notification preferences (email only)
            $notificationPrefs = [
                'email' => $validated['email_notifications'] ?? ($parent->notification_preference['email'] ?? true),
            ];

            // Update Parent profile
            $parent->update([
                'ic_number' => $validated['ic_number'],
                'occupation' => $validated['occupation'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'relationship' => $validated['relationship'],
                'relationship_description' => $validated['relationship_description'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $emergencyPhone,
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
     * IC numbers and phone numbers are exported as text to preserve + symbol
     */
    public function export(Request $request)
    {
        $parents = Parents::with(['user', 'students'])->get();

        $filename = 'parents_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($parents) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM to ensure proper encoding in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

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
                    // Prepend tab character to preserve phone format with + symbol
                    "\t" . $p->user->phone,
                    // Prepend tab character to preserve IC number format
                    "\t" . $p->ic_number,
                    ucfirst($p->relationship) . ($p->relationship_description ? ' (' . $p->relationship_description . ')' : ''),
                    $p->city,
                    $p->state,
                    $p->students->count(),
                    ucfirst($p->user->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get city and state based on postcode (AJAX endpoint)
     */
    public function getPostcodeData(Request $request)
    {
        $postcode = $request->input('postcode');

        if (empty($postcode)) {
            return response()->json(['error' => 'Postcode is required'], 400);
        }

        $postcodeData = config('postcodes.postcodes', []);

        foreach ($postcodeData as $range) {
            if ($postcode >= $range['min'] && $postcode <= $range['max']) {
                return response()->json([
                    'state' => $range['state'],
                    'cities' => $range['cities'],
                    'city' => $range['cities'][0] ?? '', // Default to first city
                ]);
            }
        }

        return response()->json(['error' => 'Postcode not found'], 404);
    }
}

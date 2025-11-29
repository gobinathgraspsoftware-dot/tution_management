<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use App\Models\Package;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    /**
     * Default online fee (RM130).
     */
    const DEFAULT_ONLINE_FEE = 130.00;

    /**
     * Display a listing of packages.
     */
    public function index(Request $request)
    {
        $query = Package::withCount(['subjects', 'enrollments']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $packages = $query->latest()->paginate(15)->withQueryString();

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $defaultOnlineFee = self::DEFAULT_ONLINE_FEE;

        return view('admin.packages.create', compact('subjects', 'defaultOnlineFee'));
    }

    /**
     * Store a newly created package.
     */
    public function store(PackageRequest $request)
    {
        $validated = $request->validated();

        // Set default online fee if not provided
        if (in_array($validated['type'], ['online', 'hybrid']) && empty($validated['online_fee'])) {
            $validated['online_fee'] = self::DEFAULT_ONLINE_FEE;
        }

        // Handle features array
        $validated['features'] = $request->features ?? [];
        $validated['includes_materials'] = $request->boolean('includes_materials');

        DB::beginTransaction();
        try {
            $package = Package::create($validated);

            // Sync subjects with sessions_per_month
            if ($request->has('subjects')) {
                $subjectsData = [];
                foreach ($request->subjects as $subjectId) {
                    $sessionsKey = "sessions_{$subjectId}";
                    $subjectsData[$subjectId] = [
                        'sessions_per_month' => $request->input($sessionsKey, 4)
                    ];
                }
                $package->subjects()->sync($subjectsData);
            }

            DB::commit();

            return redirect()->route('admin.packages.index')
                ->with('success', 'Package created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create package. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified package.
     */
    public function show(Package $package)
    {
        $package->load([
            'subjects',
            'enrollments' => function ($query) {
                $query->with('student.user')->latest()->limit(10);
            }
        ]);

        $enrollmentStats = [
            'active' => $package->enrollments()->where('status', 'active')->count(),
            'expired' => $package->enrollments()->where('status', 'expired')->count(),
            'cancelled' => $package->enrollments()->where('status', 'cancelled')->count(),
            'total' => $package->enrollments()->count(),
        ];

        return view('admin.packages.show', compact('package', 'enrollmentStats'));
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $package->load('subjects');
        $defaultOnlineFee = self::DEFAULT_ONLINE_FEE;

        // Get current subject sessions
        $selectedSubjects = $package->subjects->pluck('pivot.sessions_per_month', 'id')->toArray();

        return view('admin.packages.edit', compact('package', 'subjects', 'defaultOnlineFee', 'selectedSubjects'));
    }

    /**
     * Update the specified package.
     */
    public function update(PackageRequest $request, Package $package)
    {
        $validated = $request->validated();

        // Set default online fee if not provided
        if (in_array($validated['type'], ['online', 'hybrid']) && empty($validated['online_fee'])) {
            $validated['online_fee'] = self::DEFAULT_ONLINE_FEE;
        } elseif ($validated['type'] === 'offline') {
            $validated['online_fee'] = null;
        }

        // Handle features array
        $validated['features'] = $request->features ?? [];
        $validated['includes_materials'] = $request->boolean('includes_materials');

        DB::beginTransaction();
        try {
            $package->update($validated);

            // Sync subjects with sessions_per_month
            if ($request->has('subjects')) {
                $subjectsData = [];
                foreach ($request->subjects as $subjectId) {
                    $sessionsKey = "sessions_{$subjectId}";
                    $subjectsData[$subjectId] = [
                        'sessions_per_month' => $request->input($sessionsKey, 4)
                    ];
                }
                $package->subjects()->sync($subjectsData);
            } else {
                $package->subjects()->detach();
            }

            DB::commit();

            return redirect()->route('admin.packages.index')
                ->with('success', 'Package updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update package. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified package.
     */
    public function destroy(Package $package)
    {
        // Check if package has active enrollments
        if ($package->enrollments()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete package with active enrollments.');
        }

        $package->subjects()->detach();
        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }

    /**
     * Toggle package status.
     */
    public function toggleStatus(Package $package)
    {
        $newStatus = $package->status === 'active' ? 'inactive' : 'active';
        $package->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
        return back()->with('success', "Package {$statusText} successfully.");
    }

    /**
     * Restore a soft-deleted package.
     */
    public function restore($id)
    {
        $package = Package::withTrashed()->findOrFail($id);
        $package->restore();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package restored successfully.');
    }

    /**
     * Duplicate a package.
     */
    public function duplicate(Package $package)
    {
        DB::beginTransaction();
        try {

            if (str_contains($package->code, 'COPY')) {
                DB::rollBack();
                return back()->with('error', 'Cannot duplicate a duplicated package.');
            }

            $newPackage = $package->replicate();
            $newPackage->name = $package->name . ' (Copy)';
            $newPackage->code = $package->code . '-COPY-' . time();
            $newPackage->status = 'inactive';
            $newPackage->save();

            // Copy subject relationships
            foreach ($package->subjects as $subject) {
                $newPackage->subjects()->attach($subject->id, [
                    'sessions_per_month' => $subject->pivot->sessions_per_month
                ]);
            }

            DB::commit();

            return redirect()->route('admin.packages.edit', $newPackage)
                ->with('success', 'Package duplicated. Please update the details.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to duplicate package. ' . $e->getMessage());
        }
    }

    /**
     * Get pricing details for AJAX requests.
     */
    public function getPricing(Package $package)
    {
        return response()->json([
            'price' => $package->price,
            'online_fee' => $package->online_fee,
            'total_price' => $package->total_price,
            'type' => $package->type,
            'subjects_count' => $package->subjects()->count(),
        ]);
    }
}

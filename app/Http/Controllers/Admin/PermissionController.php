<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by module
        if ($request->filled('module')) {
            $query->where('name', 'like', $request->module . '-%');
        }

        $permissions = $query->orderBy('name')->paginate(15)->withQueryString();

        // Get unique modules for filter
        $modules = Permission::all()
            ->map(function ($permission) {
                $parts = explode('-', $permission->name);
                return $parts[0] ?? 'other';
            })
            ->unique()
            ->sort()
            ->values();

        // Group permissions by module if requested
        $groupedPermissions = null;
        if ($request->get('group_by_module') === '1') {
            $allPermissions = Permission::orderBy('name')->get();
            $groupedPermissions = $allPermissions->groupBy(function ($permission) {
                $parts = explode('-', $permission->name);
                return $parts[0] ?? 'other';
            });
        }

        return view('admin.permissions.index', compact('permissions', 'modules', 'groupedPermissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        // Get existing modules
        $modules = Permission::all()
            ->map(function ($permission) {
                $parts = explode('-', $permission->name);
                return $parts[0] ?? 'other';
            })
            ->unique()
            ->sort()
            ->values();

        return view('admin.permissions.create', compact('modules'));
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('permissions', 'name'),
            ],
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.regex' => 'Permission name must contain only lowercase letters, numbers, and hyphens.',
        ]);

        try {
            DB::beginTransaction();

            // Create permission
            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($permission)
                ->withProperties([
                    'permission_name' => $validated['name'],
                    'display_name' => $validated['display_name'] ?? null,
                ])
                ->log('Created permission: ' . $validated['name']);

            DB::commit();

            return redirect()
                ->route('admin.permissions.index')
                ->with('success', 'Permission created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        // Get roles that have this permission
        $roles = Role::whereHas('permissions', function ($query) use ($permission) {
            $query->where('permissions.id', $permission->id);
        })->get();

        // Get users count who have this permission through roles
        $usersCount = DB::table('model_has_permissions')
            ->where('permission_id', $permission->id)
            ->count();

        // Add users who have permission through roles
        foreach ($roles as $role) {
            $roleUsersCount = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->count();
            $usersCount += $roleUsersCount;
        }

        return view('admin.permissions.show', compact('permission', 'roles', 'usersCount'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        // Get existing modules
        $modules = Permission::all()
            ->map(function ($perm) {
                $parts = explode('-', $perm->name);
                return $parts[0] ?? 'other';
            })
            ->unique()
            ->sort()
            ->values();

        return view('admin.permissions.edit', compact('permission', 'modules'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.regex' => 'Permission name must contain only lowercase letters, numbers, and hyphens.',
        ]);

        try {
            DB::beginTransaction();

            // Update permission
            $permission->update([
                'name' => $validated['name'],
            ]);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($permission)
                ->withProperties([
                    'permission_name' => $validated['name'],
                    'display_name' => $validated['display_name'] ?? null,
                ])
                ->log('Updated permission: ' . $validated['name']);

            DB::commit();

            return redirect()
                ->route('admin.permissions.index')
                ->with('success', 'Permission updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update permission: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is assigned to any roles
        $rolesCount = DB::table('role_has_permissions')
            ->where('permission_id', $permission->id)
            ->count();

        if ($rolesCount > 0) {
            return back()->with('error', "Cannot delete permission. It is assigned to {$rolesCount} role(s).");
        }

        // Check if permission is directly assigned to any users
        $usersCount = DB::table('model_has_permissions')
            ->where('permission_id', $permission->id)
            ->count();

        if ($usersCount > 0) {
            return back()->with('error', "Cannot delete permission. It is assigned to {$usersCount} user(s).");
        }

        try {
            DB::beginTransaction();

            $permissionName = $permission->name;

            // Delete permission
            $permission->delete();

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['permission_name' => $permissionName])
                ->log('Deleted permission: ' . $permissionName);

            DB::commit();

            return redirect()
                ->route('admin.permissions.index')
                ->with('success', 'Permission deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete permission: ' . $e->getMessage());
        }
    }
}

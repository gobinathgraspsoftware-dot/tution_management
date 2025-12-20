<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    // System roles that cannot be deleted
    protected $systemRoles = ['super-admin', 'admin', 'staff', 'teacher', 'parent', 'student'];

    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::withCount('permissions');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'system') {
                $query->whereIn('name', $this->systemRoles);
            } else {
                $query->whereNotIn('name', $this->systemRoles);
            }
        }

        $roles = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles', 'name'),
                function ($attribute, $value, $fail) {
                    if (in_array($value, $this->systemRoles)) {
                        $fail('This role name is reserved for system use.');
                    }
                },
            ],
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.regex' => 'Role name must contain only lowercase letters, numbers, and hyphens.',
        ]);

        try {
            DB::beginTransaction();

            // Create role
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role_name' => $validated['name'],
                    'display_name' => $validated['display_name'] ?? null,
                ])
                ->log('Created custom role: ' . $validated['name']);

            DB::commit();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        $isSystemRole = in_array($role->name, $this->systemRoles);
        
        // Get users with this role
        $usersCount = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->count();

        return view('admin.roles.show', compact('role', 'isSystemRole', 'usersCount'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $isSystemRole = in_array($role->name, $this->systemRoles);
        
        return view('admin.roles.edit', compact('role', 'isSystemRole'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $isSystemRole = in_array($role->name, $this->systemRoles);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles', 'name')->ignore($role->id),
                function ($attribute, $value, $fail) use ($isSystemRole, $role) {
                    // If it's a system role, don't allow changing the name
                    if ($isSystemRole && $value !== $role->name) {
                        $fail('System role names cannot be changed.');
                    }
                    // Don't allow changing to a system role name
                    if (!$isSystemRole && in_array($value, $this->systemRoles)) {
                        $fail('This role name is reserved for system use.');
                    }
                },
            ],
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.regex' => 'Role name must contain only lowercase letters, numbers, and hyphens.',
        ]);

        try {
            DB::beginTransaction();

            // Update role
            $role->update([
                'name' => $validated['name'],
            ]);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role_name' => $validated['name'],
                    'display_name' => $validated['display_name'] ?? null,
                ])
                ->log('Updated role: ' . $validated['name']);

            DB::commit();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Check if it's a system role
        if (in_array($role->name, $this->systemRoles)) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        // Check if role is assigned to any users
        $usersCount = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->count();

        if ($usersCount > 0) {
            return back()->with('error', "Cannot delete role. It is assigned to {$usersCount} user(s).");
        }

        try {
            DB::beginTransaction();

            $roleName = $role->name;

            // Delete role (permissions will be automatically detached)
            $role->delete();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['role_name' => $roleName])
                ->log('Deleted custom role: ' . $roleName);

            DB::commit();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Role deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for managing role permissions.
     */
    public function permissions(Role $role)
    {
        // Get all permissions grouped by module
        $allPermissions = Permission::orderBy('name')->get();
        
        // Group permissions by module (extract prefix before first hyphen)
        $groupedPermissions = $allPermissions->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return $parts[0] ?? 'other';
        });

        // Get current role permissions
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        $isSystemRole = in_array($role->name, $this->systemRoles);

        return view('admin.roles.permissions', compact('role', 'groupedPermissions', 'rolePermissions', 'isSystemRole'));
    }

    /**
     * Update role permissions.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            // Sync permissions
            $permissions = $validated['permissions'] ?? [];
            $role->syncPermissions($permissions);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role_name' => $role->name,
                    'permissions_count' => count($permissions),
                ])
                ->log('Updated permissions for role: ' . $role->name);

            DB::commit();

            return redirect()
                ->route('admin.roles.permissions', $role->id)
                ->with('success', 'Role permissions updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update permissions: ' . $e->getMessage());
        }
    }
}

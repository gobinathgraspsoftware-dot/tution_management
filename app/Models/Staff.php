<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'staff_id',
        'ic_number',
        'address',
        'position',
        'department',
        'join_date',
        'salary',
        'emergency_contact',
        'emergency_phone',
        'notes',
    ];

    protected $casts = [
        'join_date' => 'date',
        'salary' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function physicalMaterialCollections()
    {
        return $this->hasMany(PhysicalMaterialCollection::class);
    }

    // Helpers
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    /**
     * Format IC number for display (add hyphens)
     * Format: 001005-10-1519
     */
    public function getFormattedIcNumberAttribute()
    {
        if (strlen($this->ic_number) === 12) {
            return substr($this->ic_number, 0, 6) . '-' .
                   substr($this->ic_number, 6, 2) . '-' .
                   substr($this->ic_number, 8);
        }
        return $this->ic_number;
    }

    // ============================================
    // ROLE MANAGEMENT HELPERS (OPTIONAL ENHANCEMENT)
    // ============================================

    /**
     * Get the staff member's primary role name
     * 
     * @return string|null
     */
    public function getRoleAttribute()
    {
        return $this->user?->roles?->first()?->name;
    }

    /**
     * Get the staff member's role display name (formatted)
     * Example: "super-admin" becomes "Super Admin"
     * 
     * @return string
     */
    public function getRoleDisplayNameAttribute()
    {
        $roleName = $this->role;
        
        if (!$roleName) {
            return 'No Role';
        }

        return ucwords(str_replace('-', ' ', $roleName));
    }

    /**
     * Check if staff member has a specific role
     * 
     * @param string|array $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->user?->hasRole($role) ?? false;
    }

    /**
     * Check if staff member has any of the given roles
     * 
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        return $this->user?->hasAnyRole($roles) ?? false;
    }

    /**
     * Check if staff member has all of the given roles
     * 
     * @param array $roles
     * @return bool
     */
    public function hasAllRoles($roles)
    {
        return $this->user?->hasAllRoles($roles) ?? false;
    }

    /**
     * Get all roles assigned to this staff member
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getRoles()
    {
        return $this->user?->roles ?? collect();
    }

    /**
     * Get role badge color based on role name
     * Useful for UI display
     * 
     * @return string
     */
    public function getRoleBadgeColorAttribute()
    {
        $roleName = $this->role;
        
        $colorMap = [
            'super-admin' => 'danger',
            'admin' => 'primary',
            'staff' => 'info',
            'teacher' => 'success',
            'parent' => 'warning',
            'student' => 'secondary',
        ];

        return $colorMap[$roleName] ?? 'secondary';
    }

    /**
     * Scope: Filter staff by role
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRole($query, $role)
    {
        return $query->whereHas('user.roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Scope: Filter staff by any of the given roles
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $roles
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAnyRole($query, array $roles)
    {
        return $query->whereHas('user.roles', function ($q) use ($roles) {
            $q->whereIn('name', $roles);
        });
    }

    /**
     * Scope: Get only active staff
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Scope: Get only inactive staff
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('status', 'inactive');
        });
    }
}

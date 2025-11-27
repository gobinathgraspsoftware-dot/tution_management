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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parents extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parents';

    protected $fillable = [
        'id',
        'user_id',
        'parent_id',
        'ic_number',
        'occupation',
        'address',
        'city',
        'state',
        'postcode',
        'emergency_contact',
        'emergency_phone',
        'relationship',
        'relationship_description',
        'whatsapp_number', // KEPT - WhatsApp notification support
        'notification_preference',
    ];

    protected $casts = [
        'notification_preference' => 'array',
    ];

    /**
     * Set emergency contact name to uppercase
     */
    public function setEmergencyContactAttribute($value)
    {
        $this->attributes['emergency_contact'] = $value ? strtoupper($value) : null;
    }

    /**
     * Get emergency contact in uppercase
     */
    public function getEmergencyContactAttribute($value)
    {
        return $value ? strtoupper($value) : null;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    // Helpers
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getPhoneAttribute()
    {
        return $this->user->phone;
    }

    /**
     * Get notification preferences for WhatsApp and Email
     */
    public function getWhatsappNotificationEnabledAttribute()
    {
        $preferences = $this->notification_preference ?? [];
        return $preferences['whatsapp'] ?? true;
    }

    public function getEmailNotificationEnabledAttribute()
    {
        $preferences = $this->notification_preference ?? [];
        return $preferences['email'] ?? true;
    }
}

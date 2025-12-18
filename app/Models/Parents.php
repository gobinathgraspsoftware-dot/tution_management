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
        'whatsapp_number',
        'notification_preference',
    ];

    protected $casts = [
        'notification_preference' => 'array',
    ];

    /**
     * Set IC number - store only 12 digits without hyphens
     */
    public function setIcNumberAttribute($value)
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        // Store only if exactly 12 digits
        if (strlen($cleaned) === 12) {
            $this->attributes['ic_number'] = $cleaned;
        } else {
            $this->attributes['ic_number'] = $cleaned;
        }
    }

    /**
     * Get IC number - return formatted with hyphens for display
     * Format: 001005-10-1519
     */
    public function getIcNumberAttribute($value)
    {
        if (!$value || strlen($value) !== 12) {
            return $value;
        }

        // Format: XXXXXX-XX-XXXX
        return substr($value, 0, 6) . '-' . substr($value, 6, 2) . '-' . substr($value, 8, 4);
    }

    /**
     * Get raw IC number without formatting (for database operations)
     */
    public function getRawIcNumberAttribute()
    {
        return $this->attributes['ic_number'] ?? null;
    }

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

    /**
     * Helper method to format IC number for display
     */
    public static function formatIcNumber($icNumber)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $icNumber);

        if (strlen($cleaned) === 12) {
            return substr($cleaned, 0, 6) . '-' . substr($cleaned, 6, 2) . '-' . substr($cleaned, 8, 4);
        }

        return $icNumber;
    }

    /**
     * Helper method to clean IC number (remove hyphens)
     */
    public static function cleanIcNumber($icNumber)
    {
        return preg_replace('/[^0-9]/', '', $icNumber);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'document_type',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'description',
        'expiry_date',
        'is_verified',
        'verified_by',
        'verified_at',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Document type options
     */
    public const DOCUMENT_TYPES = [
        'ic' => 'IC / Passport',
        'certificate' => 'Academic Certificate',
        'resume' => 'Resume / CV',
        'photo' => 'Passport Photo',
        'contract' => 'Employment Contract',
        'bank_statement' => 'Bank Statement',
        'professional_cert' => 'Professional Certificate',
        'teaching_cert' => 'Teaching Certificate',
        'reference_letter' => 'Reference Letter',
        'other' => 'Other Document',
    ];

    // ========================
    // RELATIONSHIPS
    // ========================

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ========================
    // SCOPES
    // ========================

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                     ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    // ========================
    // ACCESSORS
    // ========================

    public function getDocumentTypeNameAttribute()
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? $this->document_type;
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->expiry_date &&
               $this->expiry_date->isFuture() &&
               $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->is_expired) {
            return '<span class="badge bg-danger">Expired</span>';
        }

        if ($this->is_expiring_soon) {
            return '<span class="badge bg-warning text-dark">Expiring Soon</span>';
        }

        if (!$this->is_verified) {
            return '<span class="badge bg-secondary">Pending Verification</span>';
        }

        return '<span class="badge bg-success">Verified</span>';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'seminar_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'receipt_path',
        'notes',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    /**
     * Accessors
     */
    public function getCategoryLabelAttribute()
    {
        return \App\Services\SeminarAccountingService::getCategoryLabel($this->category);
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->approval_status) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'parent_id',
        'student_id',
        'ic_number',
        'date_of_birth',
        'gender',
        'school_name',
        'grade_level',
        'address',
        'medical_conditions',
        'registration_type',
        'registration_date',
        'enrollment_date',
        'referral_code',
        'referred_by',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registration_date' => 'date',
        'enrollment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    public function referrer()
    {
        return $this->belongsTo(Student::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(Student::class, 'referred_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function attendance()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function reviews()
    {
        return $this->hasMany(StudentReview::class);
    }

    public function materialViews()
    {
        return $this->hasMany(MaterialView::class);
    }

    public function physicalMaterialCollections()
    {
        return $this->hasMany(PhysicalMaterialCollection::class);
    }

    public function trialClasses()
    {
        return $this->hasMany(TrialClass::class);
    }

    public function referralsAsReferrer()
    {
        return $this->hasMany(Referral::class, 'referrer_student_id');
    }

    public function referralsAsReferred()
    {
        return $this->hasMany(Referral::class, 'referred_student_id');
    }

    public function referralVouchers()
    {
        return $this->hasMany(ReferralVoucher::class);
    }

    public function discountUsage()
    {
        return $this->hasMany(DiscountUsage::class);
    }

    public function attendanceSummary()
    {
        return $this->hasMany(ClassAttendanceSummary::class);
    }

    // Scopes
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

    public function scopeOnlineRegistration($query)
    {
        return $query->where('registration_type', 'online');
    }

    public function scopeOfflineRegistration($query)
    {
        return $query->where('registration_type', 'offline');
    }

    // Helpers
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function generateReferralCode()
    {
        if (!$this->referral_code) {
            $this->referral_code = 'REF' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
            $this->save();
        }
        return $this->referral_code;
    }
}

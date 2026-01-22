<?php

namespace App\Models;

use Carbon\Carbon;
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

    /**
     * REMOVED: $appends = ['waiting_time']
     *
     * Reason: The blade views (approvals-index-blade.php) calculate waiting time
     * inline using @php blocks, not via this accessor. Having it in $appends
     * forces Laravel to call getWaitingTimeAttribute() on every model load,
     * causing errors when method name doesn't match.
     *
     * If you need waiting_time, explicitly call $student->waiting_time or
     * add it back to $appends after verifying the accessor method exists.
     */

    /**
     * Get waiting time for approval queue display.
     *
     * Usage: $student->waiting_time['value'], $student->waiting_time['badge']
     *
     * Note: This is NOT auto-appended. Blade views calculate inline instead.
     * If you want to use this accessor, you can:
     * 1. Call $student->waiting_time directly in blade
     * 2. Or add 'waiting_time' back to $appends array above
     */
    public function getWaitingTimeAttribute(): array
    {
        $createdDate = $this->registration_date
            ? Carbon::parse($this->registration_date)
            : $this->created_at;

        if (!$createdDate) {
            return [
                'value' => '',
                'badge' => 'bg-secondary',
            ];
        }

        $now = Carbon::now();
        $diffInDays = (int) $createdDate->diffInDays($now);

        // Less than 1 day → show hours
        if ($diffInDays < 1) {
            $hours = (int) $createdDate->diffInHours($now);
            return [
                'value' => $hours . ' ' . ($hours === 1 ? 'hour' : 'hours'),
                'badge' => 'bg-success',
            ];
        }

        // More than 7 days - urgent
        if ($diffInDays > 7) {
            return [
                'value' => $diffInDays . ' days',
                'badge' => 'bg-danger',
            ];
        }

        // More than 3 days - warning
        if ($diffInDays > 3) {
            return [
                'value' => $diffInDays . ' days',
                'badge' => 'bg-warning',
            ];
        }

        // 1-3 days - normal
        return [
            'value' => $diffInDays . ' days',
            'badge' => 'bg-success',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

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

    // ==========================================
    // SCOPES
    // ==========================================

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

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function getAge(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return $this->date_of_birth->age;
    }

    /**
     * Generate unique student ID
     */
    public static function generateStudentId(): string
    {
        $year = date('Y');
        $lastStudent = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastStudent && preg_match('/STU-' . $year . '-(\d+)/', $lastStudent->student_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return 'STU-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique referral code
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = 'REF-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}

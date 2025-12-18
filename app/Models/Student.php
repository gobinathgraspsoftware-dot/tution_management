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

    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    public function generateReferralCode()
    {
        if (!$this->referral_code) {
            $this->referral_code = 'REF' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
            $this->save();
        }
        return $this->referral_code;
    }

    public function referredBy()
    {
        return $this->belongsTo(Student::class, 'referred_by');
    }

    /**
     * Get formatted IC number with hyphens (YYMMDD-BP-XXXX)
     *
     * @return string
     */
    public function getFormattedIcNumberAttribute()
    {
        if (empty($this->ic_number) || strlen($this->ic_number) !== 12) {
            return $this->ic_number;
        }

        return substr($this->ic_number, 0, 6) . '-' . 
               substr($this->ic_number, 6, 2) . '-' . 
               substr($this->ic_number, 8, 4);
    }

    /**
     * Get IC number without formatting (digits only)
     *
     * @return string
     */
    public function getCleanIcNumberAttribute()
    {
        return $this->ic_number;
    }

    /**
     * Format IC number for display
     *
     * @param string|null $icNumber
     * @return string
     */
    public static function formatIcNumber($icNumber)
    {
        if (empty($icNumber)) {
            return '';
        }

        // Remove any non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $icNumber);

        if (strlen($cleaned) !== 12) {
            return $icNumber;
        }

        return substr($cleaned, 0, 6) . '-' . 
               substr($cleaned, 6, 2) . '-' . 
               substr($cleaned, 8, 4);
    }

    /**
     * Clean IC number (remove hyphens, keep digits only)
     *
     * @param string|null $icNumber
     * @return string
     */
    public static function cleanIcNumber($icNumber)
    {
        if (empty($icNumber)) {
            return '';
        }

        return preg_replace('/[^0-9]/', '', $icNumber);
    }

    /**
     * Extract date of birth from IC number
     *
     * @param string|null $icNumber
     * @return string|null Date in Y-m-d format
     */
    public static function extractDobFromIc($icNumber)
    {
        $cleaned = self::cleanIcNumber($icNumber);
        
        if (strlen($cleaned) !== 12) {
            return null;
        }

        $year = substr($cleaned, 0, 2);
        $month = substr($cleaned, 2, 2);
        $day = substr($cleaned, 4, 2);

        // Determine century (00-25 = 2000s, 26-99 = 1900s)
        $fullYear = (intval($year) <= 25) ? '20' . $year : '19' . $year;

        return $fullYear . '-' . $month . '-' . $day;
    }

    /**
     * Extract gender from IC number
     *
     * @param string|null $icNumber
     * @return string|null 'male' or 'female'
     */
    public static function extractGenderFromIc($icNumber)
    {
        $cleaned = self::cleanIcNumber($icNumber);
        
        if (strlen($cleaned) !== 12) {
            return null;
        }

        $lastDigit = intval(substr($cleaned, 11, 1));
        
        return ($lastDigit % 2 === 0) ? 'female' : 'male';
    }

    /**
     * Validate IC number format
     *
     * @param string|null $icNumber
     * @return bool
     */
    public static function isValidIcNumber($icNumber)
    {
        $cleaned = self::cleanIcNumber($icNumber);
        
        // Must be exactly 12 digits
        if (strlen($cleaned) !== 12) {
            return false;
        }

        // Must be all numeric
        if (!ctype_digit($cleaned)) {
            return false;
        }

        // Validate date portion (basic validation)
        $year = intval(substr($cleaned, 0, 2));
        $month = intval(substr($cleaned, 2, 2));
        $day = intval(substr($cleaned, 4, 2));

        if ($month < 1 || $month > 12) {
            return false;
        }

        if ($day < 1 || $day > 31) {
            return false;
        }

        return true;
    }
}

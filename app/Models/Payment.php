<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const REVENUE_SOURCE_STUDENT_FEES_ONLINE = 'student_fees_online';
    const REVENUE_SOURCE_STUDENT_FEES_PHYSICAL = 'student_fees_physical';
    const REVENUE_SOURCE_SEMINAR = 'seminar_revenue';
    const REVENUE_SOURCE_POS = 'pos_sales';
    const REVENUE_SOURCE_MATERIAL = 'material_sales';
    const REVENUE_SOURCE_OTHER = 'other';

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'student_id',
        'amount',
        'payment_method',
        'revenue_source',
        'source_reference',
        'payment_date',
        'reference_number',
        'gateway_transaction_id',
        'gateway_response',
        'screenshot_path',
        'status',
        'processed_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'gateway_response' => 'array',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function gatewayTransaction()
    {
        return $this->hasOne(PaymentGatewayTransaction::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeCompleted($query)
    {
        return $query->where('payments.status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('payments.status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('payments.status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('payments.status', 'refunded');
    }

    public function scopeByCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeByQR($query)
    {
        return $query->where('payment_method', 'qr');
    }

    public function scopeByOnline($query)
    {
        return $query->where('payment_method', 'online_gateway');
    }

    public function scopeByRevenueSource($query, $source)
    {
        return $query->where('revenue_source', $source);
    }

    public function scopeStudentFeesOnline($query)
    {
        return $query->where('revenue_source', self::REVENUE_SOURCE_STUDENT_FEES_ONLINE);
    }

    public function scopeStudentFeesPhysical($query)
    {
        return $query->where('revenue_source', self::REVENUE_SOURCE_STUDENT_FEES_PHYSICAL);
    }

    public function scopeSeminarRevenue($query)
    {
        return $query->where('revenue_source', self::REVENUE_SOURCE_SEMINAR);
    }

    public function scopePosRevenue($query)
    {
        return $query->where('revenue_source', self::REVENUE_SOURCE_POS);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('payment_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                     ->whereYear('payment_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('payment_date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public static function generatePaymentNumber()
    {
        $lastPayment = static::latest('id')->first();
        $number = $lastPayment ? intval(substr($lastPayment->payment_number, 3)) + 1 : 1;

        return 'PAY' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public static function getRevenueSources()
    {
        return [
            self::REVENUE_SOURCE_STUDENT_FEES_ONLINE => 'Student Fees (Online)',
            self::REVENUE_SOURCE_STUDENT_FEES_PHYSICAL => 'Student Fees (Physical)',
            self::REVENUE_SOURCE_SEMINAR => 'Seminar Revenue',
            self::REVENUE_SOURCE_POS => 'POS Sales',
            self::REVENUE_SOURCE_MATERIAL => 'Material Sales',
            self::REVENUE_SOURCE_OTHER => 'Other',
        ];
    }

    public function getRevenueSourceLabel()
    {
        return self::getRevenueSources()[$this->revenue_source] ?? 'Unknown';
    }
}

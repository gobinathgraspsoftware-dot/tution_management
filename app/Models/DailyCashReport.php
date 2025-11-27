<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCashReport extends Model
{
    use HasFactory;
    protected $fillable = ['report_date', 'opening_cash', 'total_cash_sales', 'total_qr_sales', 'total_transactions', 'expected_closing', 'actual_closing', 'variance', 'notes', 'closed_by', 'status'];
    protected $casts = ['report_date' => 'date', 'opening_cash' => 'decimal:2', 'total_cash_sales' => 'decimal:2', 'total_qr_sales' => 'decimal:2', 'total_transactions' => 'integer', 'expected_closing' => 'decimal:2', 'actual_closing' => 'decimal:2', 'variance' => 'decimal:2'];
    public function closedBy() { return $this->belongsTo(User::class, 'closed_by'); }
    public function scopeOpen($query) { return $query->where('status', 'open'); }
    public function scopeClosed($query) { return $query->where('status', 'closed'); }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = ['category_id', 'description', 'amount', 'expense_date', 'payment_method', 'reference_number', 'receipt_path', 'is_recurring', 'recurring_frequency', 'status', 'approved_by', 'created_by', 'notes'];
    protected $casts = ['amount' => 'decimal:2', 'expense_date' => 'date', 'is_recurring' => 'boolean'];
    public function category() { return $this->belongsTo(ExpenseCategory::class, 'category_id'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopePending($query) { return $query->where('status', 'pending'); }
}

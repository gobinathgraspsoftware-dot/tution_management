<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExpenseCategory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'expense_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get expenses for this category
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get only inactive categories
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope to search by name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if category is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if category is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Get total expense amount for this category (approved/paid only)
     */
    public function getTotalExpenseAmount()
    {
        return $this->expenses()
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
            ->sum('amount');
    }

    /**
     * Get expense count for this category
     */
    public function getExpenseCount()
    {
        return $this->expenses()->count();
    }

    /**
     * Get approved/paid expense count for this category
     */
    public function getApprovedExpenseCount()
    {
        return $this->expenses()
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
            ->count();
    }

    /**
     * Check if category can be deleted (no expenses attached)
     */
    public function canBeDeleted(): bool
    {
        return $this->expenses()->count() === 0;
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status label for display
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? 'Unknown';
    }

    // ==========================================
    // STATIC HELPER METHODS
    // ==========================================

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * Get categories with expense summary
     */
    public static function getWithExpenseSummary($startDate = null, $endDate = null)
    {
        $query = static::active()
            ->withCount(['expenses as expense_count' => function ($q) use ($startDate, $endDate) {
                $q->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID]);
                if ($startDate && $endDate) {
                    $q->whereBetween('expense_date', [$startDate, $endDate]);
                }
            }])
            ->withSum(['expenses as total_amount' => function ($q) use ($startDate, $endDate) {
                $q->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID]);
                if ($startDate && $endDate) {
                    $q->whereBetween('expense_date', [$startDate, $endDate]);
                }
            }], 'amount');

        return $query->orderBy('name')->get();
    }
}

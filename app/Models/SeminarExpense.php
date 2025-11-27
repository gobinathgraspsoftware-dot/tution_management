<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarExpense extends Model
{
    use HasFactory;
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['seminar_id', 'category', 'description', 'amount', 'receipt_path'];
    protected $casts = ['amount' => 'decimal:2', 'created_at' => 'datetime'];
    public function seminar() { return $this->belongsTo(Seminar::class); }
}

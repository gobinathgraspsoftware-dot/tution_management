<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarParticipant extends Model
{
    use HasFactory;
    protected $fillable = ['seminar_id', 'student_id', 'name', 'email', 'phone', 'school', 'grade', 'registration_date', 'fee_amount', 'payment_status', 'payment_method', 'payment_date', 'attendance_status', 'certificate_issued', 'notes'];
    protected $casts = ['registration_date' => 'datetime', 'fee_amount' => 'decimal:2', 'payment_date' => 'datetime', 'certificate_issued' => 'boolean'];
    public function seminar() { return $this->belongsTo(Seminar::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function scopePaid($query) { return $query->where('payment_status', 'paid'); }
    public function scopeAttended($query) { return $query->where('attendance_status', 'attended'); }
}

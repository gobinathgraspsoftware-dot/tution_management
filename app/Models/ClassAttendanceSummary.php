<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassAttendanceSummary extends Model
{
    use HasFactory;
    protected $table = 'class_attendance_summary';
    protected $fillable = ['class_id', 'student_id', 'month', 'year', 'total_sessions', 'present_count', 'absent_count', 'late_count', 'excused_count', 'attendance_percentage'];
    protected $casts = ['month' => 'integer', 'year' => 'integer', 'total_sessions' => 'integer', 'present_count' => 'integer', 'absent_count' => 'integer', 'late_count' => 'integer', 'excused_count' => 'integer', 'attendance_percentage' => 'decimal:2'];
    public function class() { return $this->belongsTo(ClassModel::class, 'class_id'); }
    public function student() { return $this->belongsTo(Student::class); }
}

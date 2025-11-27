<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalMaterialCollection extends Model
{
    use HasFactory;
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['physical_material_id', 'student_id', 'collected_at', 'collected_by_name', 'staff_id', 'notes'];
    protected $casts = ['collected_at' => 'datetime', 'created_at' => 'datetime'];
    public function physicalMaterial() { return $this->belongsTo(PhysicalMaterial::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function staff() { return $this->belongsTo(Staff::class); }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialView extends Model
{
    use HasFactory;
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['material_id', 'student_id', 'viewed_at', 'duration_seconds'];
    protected $casts = ['viewed_at' => 'datetime', 'duration_seconds' => 'integer', 'created_at' => 'datetime'];
    public function material() { return $this->belongsTo(Material::class); }
    public function student() { return $this->belongsTo(Student::class); }
}

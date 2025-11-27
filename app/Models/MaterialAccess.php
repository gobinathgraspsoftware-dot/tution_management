<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialAccess extends Model
{
    use HasFactory;
    protected $table = 'material_access';
    protected $fillable = ['material_id', 'user_id', 'class_id', 'enrollment_id', 'access_granted_at', 'access_expires_at', 'granted_by'];
    protected $casts = ['access_granted_at' => 'datetime', 'access_expires_at' => 'datetime'];
    public function material() { return $this->belongsTo(Material::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function class() { return $this->belongsTo(ClassModel::class, 'class_id'); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }
    public function grantedBy() { return $this->belongsTo(User::class, 'granted_by'); }
}

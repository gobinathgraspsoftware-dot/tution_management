<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalMaterial extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'subject_id', 'grade_level', 'month', 'year', 'description', 'quantity_available', 'status'];
    protected $casts = ['year' => 'integer', 'quantity_available' => 'integer'];
    public function subject() { return $this->belongsTo(Subject::class); }
    public function collections() { return $this->hasMany(PhysicalMaterialCollection::class); }
    public function scopeAvailable($query) { return $query->where('status', 'available'); }
}

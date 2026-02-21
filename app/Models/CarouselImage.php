<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CarouselImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                            */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    /* ------------------------------------------------------------------ */
    /*  Accessors                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Get the full public URL for the image.
     * Returns null if file does not exist or path is empty.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        if (Storage::disk('public')->exists($this->image_path)) {
            return asset('storage/' . $this->image_path);
        }

        return null;
    }
}

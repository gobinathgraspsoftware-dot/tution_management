<?php

namespace App\Services;

use App\Models\Material;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MaterialService
{
    /**
     * Create a new material.
     */
    public function createMaterial(array $data): Material
    {
        return DB::transaction(function () use ($data) {
            // Handle file upload
            if (isset($data['file'])) {
                $file = $data['file'];
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('materials', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_type'] = $file->getClientOriginalExtension();
                $data['file_size'] = $file->getSize();

                unset($data['file']);
            }

            // Set default values
            $data['is_approved'] = false; // Requires admin approval

            // Create material
            $material = Material::create($data);

            return $material;
        });
    }

    /**
     * Update an existing material.
     */
    public function updateMaterial(Material $material, array $data): Material
    {
        return DB::transaction(function () use ($material, $data) {
            // Handle file upload if new file provided
            if (isset($data['file'])) {
                // Delete old file
                if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                    Storage::disk('public')->delete($material->file_path);
                }

                $file = $data['file'];
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('materials', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_type'] = $file->getClientOriginalExtension();
                $data['file_size'] = $file->getSize();

                // Reset approval if file changed
                $data['is_approved'] = false;
                $data['approved_by'] = null;

                unset($data['file']);
            }

            // Update material
            $material->update($data);

            return $material;
        });
    }

    /**
     * Grant material access to students in a class.
     */
    public function grantAccessToClass(Material $material, int $classId): void
    {
        $enrollments = \App\Models\Enrollment::where('class_id', $classId)
            ->where('status', 'active')
            ->get();

        foreach ($enrollments as $enrollment) {
            \App\Models\MaterialAccess::firstOrCreate([
                'material_id' => $material->id,
                'user_id' => $enrollment->student->user_id,
                'class_id' => $classId,
                'enrollment_id' => $enrollment->id,
            ], [
                'access_granted_at' => now(),
                'granted_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Revoke material access from a student.
     */
    public function revokeAccess(Material $material, int $userId): void
    {
        \App\Models\MaterialAccess::where('material_id', $material->id)
            ->where('user_id', $userId)
            ->delete();
    }
}

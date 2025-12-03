<?php

namespace App\Services;

use App\Models\PhysicalMaterial;
use App\Models\PhysicalMaterialCollection;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class PhysicalMaterialService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Record material collection.
     */
    public function recordCollection(PhysicalMaterial $material, array $data): PhysicalMaterialCollection
    {
        return DB::transaction(function () use ($material, $data) {
            // Create collection record
            $collection = PhysicalMaterialCollection::create([
                'physical_material_id' => $material->id,
                'student_id' => $data['student_id'],
                'collected_at' => now(),
                'collected_by_name' => $data['collected_by_name'],
                'staff_id' => $data['staff_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update quantity
            if ($material->quantity_available > 0) {
                $material->decrement('quantity_available');

                if ($material->quantity_available <= 0) {
                    $material->update(['status' => 'out_of_stock']);
                }
            }

            // Send notification to parent
            $student = $collection->student;
            if ($student->parent) {
                $this->notificationService->sendMaterialCollectionNotification(
                    $student->parent->user,
                    $student,
                    $material
                );
            }

            return $collection;
        });
    }

    /**
     * Bulk notify students about new material.
     */
    public function notifyStudentsAboutMaterial(PhysicalMaterial $material): void
    {
        // Get all active students
        $students = \App\Models\Student::approved()->get();

        foreach ($students as $student) {
            // Notify student
            $this->notificationService->sendNewMaterialNotification(
                $student->user,
                $material
            );

            // Notify parent
            if ($student->parent) {
                $this->notificationService->sendNewMaterialNotification(
                    $student->parent->user,
                    $material
                );
            }
        }
    }

    /**
     * Get collection report.
     */
    public function getCollectionReport(PhysicalMaterial $material): array
    {
        $totalStudents = \App\Models\Student::approved()->count();
        $collectedCount = $material->collections()->distinct('student_id')->count();
        $pendingCount = $totalStudents - $collectedCount;

        return [
            'total_students' => $totalStudents,
            'collected' => $collectedCount,
            'pending' => $pendingCount,
            'collection_rate' => $totalStudents > 0 ? ($collectedCount / $totalStudents) * 100 : 0,
        ];
    }
}

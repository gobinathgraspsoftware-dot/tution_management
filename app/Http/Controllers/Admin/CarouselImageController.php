<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarouselImageRequest;
use App\Models\ActivityLog;
use App\Models\CarouselImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CarouselImageController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  INDEX                                                             */
    /* ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $query = CarouselImage::with('createdBy')
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            })
            ->ordered();

        $carouselImages = $query->paginate(12)->withQueryString();

        return view('admin.carousel.index', compact('carouselImages'));
    }

    /* ------------------------------------------------------------------ */
    /*  CREATE                                                            */
    /* ------------------------------------------------------------------ */
    public function create()
    {
        $nextOrder = (CarouselImage::max('sort_order') ?? 0) + 1;

        return view('admin.carousel.create', compact('nextOrder'));
    }

    /* ------------------------------------------------------------------ */
    /*  STORE                                                             */
    /* ------------------------------------------------------------------ */
    public function store(CarouselImageRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle image upload — directly to public/uploads/carousel/
            if ($request->hasFile('image')) {
                $data['image_path'] = $this->uploadImage($request->file('image'));
            }

            $data['sort_order'] = $data['sort_order'] ?? ((CarouselImage::max('sort_order') ?? 0) + 1);
            $data['is_active']  = $request->has('is_active') ? true : false;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            unset($data['image']);

            $carousel = CarouselImage::create($data);

            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'create',
                'model_type'  => 'CarouselImage',
                'model_id'    => $carousel->id,
                'description' => "Uploaded carousel image #{$carousel->id}",
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.carousel.index')
                ->with('success', 'Carousel image uploaded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to upload carousel image: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /*  EDIT                                                              */
    /* ------------------------------------------------------------------ */
    public function edit(CarouselImage $carousel)
    {
        return view('admin.carousel.edit', compact('carousel'));
    }

    /* ------------------------------------------------------------------ */
    /*  UPDATE                                                            */
    /* ------------------------------------------------------------------ */
    public function update(CarouselImageRequest $request, CarouselImage $carousel)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle new image upload (replace old one)
            if ($request->hasFile('image')) {
                $this->deleteImage($carousel->image_path);
                $data['image_path'] = $this->uploadImage($request->file('image'));
            }

            $data['is_active']  = $request->has('is_active') ? true : false;
            $data['updated_by'] = auth()->id();

            unset($data['image']);

            $carousel->update($data);

            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'update',
                'model_type'  => 'CarouselImage',
                'model_id'    => $carousel->id,
                'description' => "Updated carousel image #{$carousel->id}",
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.carousel.index')
                ->with('success', 'Carousel image updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update carousel image: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /*  DESTROY                                                           */
    /* ------------------------------------------------------------------ */
    public function destroy(CarouselImage $carousel)
    {
        try {
            DB::beginTransaction();

            $imageId = $carousel->id;

            $this->deleteImage($carousel->image_path);

            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'delete',
                'model_type'  => 'CarouselImage',
                'model_id'    => $imageId,
                'description' => "Deleted carousel image #{$imageId}",
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);

            $carousel->delete();

            DB::commit();

            return redirect()->route('admin.carousel.index')
                ->with('success', 'Carousel image deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete carousel image: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /*  TOGGLE STATUS                                                     */
    /* ------------------------------------------------------------------ */
    public function toggleStatus(CarouselImage $carousel)
    {
        try {
            $carousel->update([
                'is_active'  => !$carousel->is_active,
                'updated_by' => auth()->id(),
            ]);

            $status = $carousel->is_active ? 'activated' : 'deactivated';

            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'update',
                'model_type'  => 'CarouselImage',
                'model_id'    => $carousel->id,
                'description' => "Carousel image #{$carousel->id} {$status}",
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);

            return back()->with('success', "Carousel image {$status} successfully.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to toggle status: ' . $e->getMessage());
        }
    }

    /* ================================================================== */
    /*  PRIVATE HELPERS                                                   */
    /* ================================================================== */

    /**
     * Upload image directly to public/uploads/carousel/
     * No symlink needed — works on ALL hosting environments.
     */
    private function uploadImage($file): string
    {
        $uploadDir = public_path('uploads/carousel');

        // Create directory if it doesn't exist
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        // Move file directly to public/uploads/carousel/
        $file->move($uploadDir, $filename);

        // Store as "carousel/filename.jpg" in DB (consistent with existing data)
        return 'carousel/' . $filename;
    }

    /**
     * Delete image from public/uploads/carousel/
     */
    private function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $filePath = public_path('uploads/' . $path);

        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}

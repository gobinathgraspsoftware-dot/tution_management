<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarouselImageRequest;
use App\Models\ActivityLog;
use App\Models\CarouselImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CarouselImageController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  INDEX – list all carousel images                                  */
    /* ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $query = CarouselImage::with('createdBy')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('caption', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            })
            ->ordered();

        $carouselImages = $query->paginate(12)->withQueryString();

        return view('admin.carousel.index', compact('carouselImages'));
    }

    /* ------------------------------------------------------------------ */
    /*  CREATE – show upload form                                         */
    /* ------------------------------------------------------------------ */
    public function create()
    {
        $nextOrder = (CarouselImage::max('sort_order') ?? 0) + 1;

        return view('admin.carousel.create', compact('nextOrder'));
    }

    /* ------------------------------------------------------------------ */
    /*  STORE – validate & save new image                                 */
    /* ------------------------------------------------------------------ */
    public function store(CarouselImageRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image_path'] = $this->uploadImage($request->file('image'));
            }

            // Set defaults
            $data['sort_order'] = $data['sort_order'] ?? ((CarouselImage::max('sort_order') ?? 0) + 1);
            $data['is_active']  = $request->has('is_active') ? true : false;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // Remove the raw 'image' key (we stored image_path)
            unset($data['image']);

            $carousel = CarouselImage::create($data);

            // Activity log
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'create',
                'model_type'  => 'CarouselImage',
                'model_id'    => $carousel->id,
                'description' => "Created carousel image: {$carousel->title}",
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
    /*  EDIT – show edit form                                             */
    /* ------------------------------------------------------------------ */
    public function edit(CarouselImage $carousel)
    {
        return view('admin.carousel.edit', compact('carousel'));
    }

    /* ------------------------------------------------------------------ */
    /*  UPDATE – validate & update existing image                         */
    /* ------------------------------------------------------------------ */
    public function update(CarouselImageRequest $request, CarouselImage $carousel)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle new image upload (replace old one)
            if ($request->hasFile('image')) {
                // Delete old image from storage
                $this->deleteImage($carousel->image_path);

                $data['image_path'] = $this->uploadImage($request->file('image'));
            }

            $data['is_active']  = $request->has('is_active') ? true : false;
            $data['updated_by'] = auth()->id();

            // Remove raw 'image' key
            unset($data['image']);

            $carousel->update($data);

            // Activity log
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'update',
                'model_type'  => 'CarouselImage',
                'model_id'    => $carousel->id,
                'description' => "Updated carousel image: {$carousel->title}",
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
    /*  DESTROY – delete image record & file                              */
    /* ------------------------------------------------------------------ */
    public function destroy(CarouselImage $carousel)
    {
        try {
            DB::beginTransaction();

            $title = $carousel->title ?? 'Untitled';

            // Delete physical file
            $this->deleteImage($carousel->image_path);

            // Activity log
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'delete',
                'model_type'  => 'CarouselImage',
                'model_id'    => $carousel->id,
                'description' => "Deleted carousel image: {$title}",
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
    /*  TOGGLE STATUS – AJAX or standard request                          */
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
                'description' => "Carousel image {$status}: {$carousel->title}",
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
     * Upload image to storage/app/public/carousel
     */
    private function uploadImage($file): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('carousel', $filename, 'public');
    }

    /**
     * Delete image from public disk (safe – checks existence first).
     */
    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

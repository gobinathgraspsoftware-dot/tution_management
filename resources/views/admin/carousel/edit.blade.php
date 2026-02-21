@extends('layouts.app')

@section('title', 'Edit Carousel Image')
@section('page-title', 'Edit Carousel Image')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Edit Carousel Image</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.carousel.index') }}">Carousel Images</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.carousel.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i>Edit Carousel Image
            </div>
            <div class="card-body">
                <form action="{{ route('admin.carousel.update', $carousel) }}" method="POST" enctype="multipart/form-data" id="carouselForm">
                    @csrf
                    @method('PUT')

                    {{-- Current Image --}}
                    <div class="mb-4">
                        <label class="form-label">Current Image</label>
                        <div class="border rounded-3 p-2 text-center" style="background:#fafafa; min-height:150px; display:flex; align-items:center; justify-content:center;">
                            @if($carousel->image_url)
                                <img src="{{ $carousel->image_url }}" alt="{{ $carousel->title }}"
                                     class="img-fluid rounded" style="max-height:250px;" id="currentImage">
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p class="small mb-0">Image file not found on server.</p>
                                    <p class="small text-danger mb-0">
                                        @if(!file_exists(public_path('storage')))
                                            Run: <code>php artisan storage:link</code>
                                        @else
                                            The file may have been deleted from storage.
                                        @endif
                                    </p>
                                    <p class="small text-muted mt-1">Stored path: {{ $carousel->image_path }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Replace Image --}}
                    <div class="mb-4">
                        <label for="image" class="form-label">Replace Image <small class="text-muted">(optional — leave empty to keep current)</small></label>
                        <div id="dropZone" class="border border-2 border-dashed rounded-3 p-3 text-center position-relative"
                             style="cursor:pointer; background:#fafafa; transition: all 0.3s;">
                            <div id="dropPlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-1"></i>
                                <p class="mb-0 small">Click to upload or drag & drop a replacement image</p>
                                <small class="text-muted">JPEG, PNG, WebP, GIF &middot; Max 2 MB &middot; Min 600×200px</small>
                            </div>
                            <img id="imagePreview" class="img-fluid rounded d-none" style="max-height:200px;" alt="New Preview">
                            <input type="file" name="image" id="image" class="position-absolute top-0 start-0 w-100 h-100"
                                   style="opacity:0; cursor:pointer;" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif">
                        </div>
                        @error('image')
                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div id="imageInfo" class="small text-muted mt-1 d-none"></div>
                    </div>

                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $carousel->title) }}" placeholder="e.g. Welcome to Arena Matriks" maxlength="150">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Caption --}}
                    <div class="mb-3">
                        <label for="caption" class="form-label">Caption</label>
                        <textarea name="caption" id="caption" rows="2"
                                  class="form-control @error('caption') is-invalid @enderror"
                                  placeholder="Brief description shown on the banner..." maxlength="255">{{ old('caption', $carousel->caption) }}</textarea>
                        @error('caption')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted"><span id="captionCount">0</span>/255</small>
                    </div>

                    {{-- Link URL --}}
                    <div class="mb-3">
                        <label for="link_url" class="form-label">Link URL <small class="text-muted">(optional)</small></label>
                        <input type="url" name="link_url" id="link_url" class="form-control @error('link_url') is-invalid @enderror"
                               value="{{ old('link_url', $carousel->link_url) }}" placeholder="https://example.com">
                        @error('link_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- Sort Order --}}
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">Display Order</label>
                            <input type="number" name="sort_order" id="sort_order"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $carousel->sort_order) }}" min="0" max="255">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Lower numbers appear first.</small>
                        </div>

                        {{-- Active Toggle --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       class="form-check-input" style="width:3em; height:1.5em;"
                                       {{ old('is_active', $carousel->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label ms-2">Active (visible on homepage)</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted align-self-center">
                            Last updated: {{ $carousel->updated_at->format('d M Y, h:i A') }}
                            @if($carousel->updatedBy)
                                by {{ $carousel->updatedBy->name }}
                            @endif
                        </small>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.carousel.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Help Panel --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Image Guidelines</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><strong>Formats:</strong> JPEG, PNG, WebP, GIF</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><strong>Max Size:</strong> 2 MB</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><strong>Min Dimensions:</strong> 600 × 200 px</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><strong>Max Dimensions:</strong> 3840 × 2160 px</li>
                    <li class="mb-2"><i class="fas fa-star text-warning me-2"></i><strong>Recommended:</strong> 1920 × 600 px</li>
                    <li><i class="fas fa-lightbulb text-info me-2"></i>Use landscape images for best results.</li>
                </ul>
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="card border-danger mt-3">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Permanently delete this carousel image.</p>
                <form action="{{ route('admin.carousel.destroy', $carousel) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to permanently delete this carousel image?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="fas fa-trash me-1"></i> Delete This Image
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const dropZone      = $('#dropZone');
    const imageInput     = $('#image');
    const imagePreview   = $('#imagePreview');
    const placeholder    = $('#dropPlaceholder');
    const imageInfo      = $('#imageInfo');
    const captionField   = $('#caption');
    const captionCount   = $('#captionCount');

    // ---- Drag & Drop styling ----
    dropZone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('border-primary bg-light');
    }).on('dragleave drop', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-light');
    }).on('drop', function(e) {
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            imageInput[0].files = files;
            imageInput.trigger('change');
        }
    });

    // ---- Preview on file select ----
    imageInput.on('change', function() {
        var file = this.files[0];
        if (!file) return;

        var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Allowed: JPEG, PNG, WebP, GIF.');
            this.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('File too large. Maximum size is 2 MB.');
            this.value = '';
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                if (img.width < 600 || img.height < 200) {
                    alert('Image too small. Minimum: 600×200 pixels. Yours: ' + img.width + '×' + img.height);
                    imageInput.val('');
                    return;
                }
                if (img.width > 3840 || img.height > 2160) {
                    alert('Image too large. Maximum: 3840×2160 pixels. Yours: ' + img.width + '×' + img.height);
                    imageInput.val('');
                    return;
                }

                imagePreview.attr('src', e.target.result).removeClass('d-none');
                placeholder.addClass('d-none');
                imageInfo.html('<i class="fas fa-image me-1"></i>' + file.name + ' &middot; ' +
                    (file.size / 1024).toFixed(1) + ' KB &middot; ' + img.width + '×' + img.height + 'px')
                    .removeClass('d-none');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    // ---- Caption counter ----
    captionField.on('input', function() {
        captionCount.text($(this).val().length);
    }).trigger('input');

    // ---- Prevent double submit ----
    $('#carouselForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');
    });
});
</script>
@endpush

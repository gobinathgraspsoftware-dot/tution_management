@extends('layouts.app')

@section('title', 'Add Carousel Image')
@section('page-title', 'Add Carousel Image')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Add Carousel Image</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.carousel.index') }}">Carousel Images</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.carousel.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- Storage Link Warning --}}
@if(!file_exists(public_path('storage')))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Storage link not found!</strong> Uploaded images will not display until you run:
    <code>php artisan storage:link</code>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-upload me-2"></i>Upload Banner Image
            </div>
            <div class="card-body">
                <form action="{{ route('admin.carousel.store') }}" method="POST" enctype="multipart/form-data" id="carouselForm">
                    @csrf

                    {{-- Image Upload --}}
                    <div class="mb-4">
                        <label for="image" class="form-label">Banner Image <span class="text-danger">*</span></label>
                        <div id="dropZone" class="border border-2 border-dashed rounded-3 p-4 text-center position-relative"
                             style="cursor:pointer; min-height:220px; background:#fafafa; transition: all 0.3s;">
                            <div id="dropPlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                <p class="mb-1 fw-semibold">Click to upload or drag & drop</p>
                                <small class="text-muted">JPEG, PNG, WebP, GIF &middot; Max 2 MB &middot; Min 600×200px</small>
                            </div>
                            <img id="imagePreview" class="img-fluid rounded d-none" style="max-height:300px;" alt="Preview">
                            <input type="file" name="image" id="image" class="position-absolute top-0 start-0 w-100 h-100"
                                   style="opacity:0; cursor:pointer;" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif">
                        </div>
                        @error('image')
                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div id="imageInfo" class="small text-muted mt-1 d-none"></div>
                    </div>

                    <div class="row">
                        {{-- Sort Order --}}
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">Display Order</label>
                            <input type="number" name="sort_order" id="sort_order"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $nextOrder) }}" min="0" max="255">
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
                                       {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label ms-2">Active (visible on homepage)</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.carousel.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i> Upload & Save
                        </button>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var dropZone     = $('#dropZone');
    var imageInput   = $('#image');
    var imagePreview = $('#imagePreview');
    var placeholder  = $('#dropPlaceholder');
    var imageInfo    = $('#imageInfo');

    // Drag & Drop styling
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

    // Preview on file select
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
                    alert('Image too small. Minimum: 600×200 pixels.\nYours: ' + img.width + '×' + img.height);
                    imageInput.val('');
                    return;
                }
                if (img.width > 3840 || img.height > 2160) {
                    alert('Image too large. Maximum: 3840×2160 pixels.\nYours: ' + img.width + '×' + img.height);
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

    // Prevent double submit
    $('#carouselForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Uploading...');
    });
});
</script>
@endpush

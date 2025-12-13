<div class="row">
    <!-- Basic Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Seminar Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $seminar->name ?? '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="4">{{ old('description', $seminar->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Provide a detailed description of the seminar</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Seminar Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="spm" {{ old('type', $seminar->type ?? '') == 'spm' ? 'selected' : '' }}>SPM Program</option>
                            <option value="workshop" {{ old('type', $seminar->type ?? '') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                            <option value="bootcamp" {{ old('type', $seminar->type ?? '') == 'bootcamp' ? 'selected' : '' }}>Bootcamp</option>
                            <option value="other" {{ old('type', $seminar->type ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="facilitator" class="form-label">Facilitator</label>
                        <input type="text" class="form-control @error('facilitator') is-invalid @enderror" 
                               id="facilitator" name="facilitator" value="{{ old('facilitator', $seminar->facilitator ?? '') }}">
                        @error('facilitator')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Date & Time -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Date & Time</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date" class="form-label">Seminar Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" 
                               id="date" name="date" value="{{ old('date', isset($seminar->date) ? $seminar->date->format('Y-m-d') : '') }}" required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                               id="start_time" name="start_time" value="{{ old('start_time', isset($seminar->start_time) ? \Carbon\Carbon::parse($seminar->start_time)->format('H:i') : '') }}">
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                               id="end_time" name="end_time" value="{{ old('end_time', isset($seminar->end_time) ? \Carbon\Carbon::parse($seminar->end_time)->format('H:i') : '') }}">
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="registration_deadline" class="form-label">Registration Deadline</label>
                        <input type="date" class="form-control @error('registration_deadline') is-invalid @enderror" 
                               id="registration_deadline" name="registration_deadline" 
                               value="{{ old('registration_deadline', isset($seminar->registration_deadline) ? $seminar->registration_deadline->format('Y-m-d') : '') }}">
                        @error('registration_deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Last day to accept registrations</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Venue Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Venue Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_online" name="is_online" value="1"
                               {{ old('is_online', $seminar->is_online ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_online">
                            This is an online seminar
                        </label>
                    </div>
                </div>

                <div id="venueField" class="mb-3">
                    <label for="venue" class="form-label">Venue <span class="text-danger venue-required">*</span></label>
                    <input type="text" class="form-control @error('venue') is-invalid @enderror" 
                           id="venue" name="venue" value="{{ old('venue', $seminar->venue ?? '') }}">
                    @error('venue')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="meetingLinkField" class="mb-3" style="display: none;">
                    <label for="meeting_link" class="form-label">Meeting Link <span class="text-danger meeting-link-required">*</span></label>
                    <input type="url" class="form-control @error('meeting_link') is-invalid @enderror" 
                           id="meeting_link" name="meeting_link" value="{{ old('meeting_link', $seminar->meeting_link ?? '') }}"
                           placeholder="https://zoom.us/j/...">
                    @error('meeting_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-md-4">
        <!-- Status & Capacity -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Status & Capacity</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="draft" {{ old('status', $seminar->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="open" {{ old('status', $seminar->status ?? '') == 'open' ? 'selected' : '' }}>Open for Registration</option>
                        <option value="closed" {{ old('status', $seminar->status ?? '') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="completed" {{ old('status', $seminar->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $seminar->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                           id="capacity" name="capacity" value="{{ old('capacity', $seminar->capacity ?? '') }}"
                           min="1" max="1000" placeholder="Leave empty for unlimited">
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Maximum number of participants</small>
                </div>

                @if(isset($seminar) && $seminar->exists)
                <div class="alert alert-info">
                    <small>
                        <strong>Current Participants:</strong> {{ $seminar->current_participants }}
                        @if($seminar->capacity)
                        / {{ $seminar->capacity }}
                        @endif
                    </small>
                </div>
                @endif
            </div>
        </div>

        <!-- Pricing -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pricing</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="regular_fee" class="form-label">Regular Fee (RM) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('regular_fee') is-invalid @enderror" 
                           id="regular_fee" name="regular_fee" value="{{ old('regular_fee', $seminar->regular_fee ?? '') }}"
                           step="0.01" min="0" required>
                    @error('regular_fee')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="early_bird_fee" class="form-label">Early Bird Fee (RM)</label>
                    <input type="number" class="form-control @error('early_bird_fee') is-invalid @enderror" 
                           id="early_bird_fee" name="early_bird_fee" value="{{ old('early_bird_fee', $seminar->early_bird_fee ?? '') }}"
                           step="0.01" min="0">
                    @error('early_bird_fee')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Special price for early registrations</small>
                </div>

                <div class="mb-3">
                    <label for="early_bird_deadline" class="form-label">Early Bird Deadline</label>
                    <input type="date" class="form-control @error('early_bird_deadline') is-invalid @enderror" 
                           id="early_bird_deadline" name="early_bird_deadline" 
                           value="{{ old('early_bird_deadline', isset($seminar->early_bird_deadline) ? $seminar->early_bird_deadline->format('Y-m-d') : '') }}">
                    @error('early_bird_deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Last day for early bird pricing</small>
                </div>
            </div>
        </div>

        <!-- Image Upload -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Seminar Image</h5>
            </div>
            <div class="card-body">
                @if(isset($seminar) && $seminar->image)
                <div class="mb-3 text-center">
                    <img src="{{ asset('storage/' . $seminar->image) }}" alt="Seminar Image" class="img-fluid rounded" style="max-height: 200px;">
                </div>
                @endif

                <div class="mb-3">
                    <label for="image" class="form-label">Upload Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                           id="image" name="image" accept="image/*">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Max 2MB (JPEG, PNG, JPG, WEBP)</small>
                </div>

                <div id="imagePreview" class="text-center" style="display: none;">
                    <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle venue/meeting link fields
    function toggleVenueFields() {
        const isOnline = $('#is_online').is(':checked');
        if (isOnline) {
            $('#venueField').hide();
            $('#meetingLinkField').show();
            $('#venue').removeAttr('required');
            $('#meeting_link').attr('required', 'required');
        } else {
            $('#venueField').show();
            $('#meetingLinkField').hide();
            $('#venue').attr('required', 'required');
            $('#meeting_link').removeAttr('required');
        }
    }

    $('#is_online').change(toggleVenueFields);
    toggleVenueFields(); // Initial state

    // Image preview
    $('#image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Validate early bird fee
    $('#early_bird_fee, #regular_fee').on('input', function() {
        const regularFee = parseFloat($('#regular_fee').val()) || 0;
        const earlyBirdFee = parseFloat($('#early_bird_fee').val()) || 0;
        
        if (earlyBirdFee > 0 && earlyBirdFee >= regularFee) {
            $('#early_bird_fee').addClass('is-invalid');
            $('#early_bird_fee').next('.invalid-feedback').remove();
            $('#early_bird_fee').after('<div class="invalid-feedback" style="display: block;">Early bird fee must be less than regular fee</div>');
        } else {
            $('#early_bird_fee').removeClass('is-invalid');
            $('#early_bird_fee').next('.invalid-feedback').remove();
        }
    });
});
</script>
@endpush

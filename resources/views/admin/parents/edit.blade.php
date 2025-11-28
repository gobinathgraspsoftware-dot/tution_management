@extends('layouts.app')

@section('title', 'Edit Parent')
@section('page-title', 'Edit Parent')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i> Edit Parent</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">Parents</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.parents.update', $parent) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Account Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i> Account Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Parent ID</label>
                        <input type="text" class="form-control" value="{{ $parent->parent_id }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $parent->user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $parent->user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $parent->user->phone) }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to keep current</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-id-card me-2"></i> Personal Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">IC Number <span class="text-danger">*</span></label>
                        <input type="text" name="ic_number" class="form-control @error('ic_number') is-invalid @enderror"
                               value="{{ old('ic_number', $parent->ic_number) }}" required>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Relationship <span class="text-danger">*</span></label>
                            <select name="relationship" class="form-select @error('relationship') is-invalid @enderror" required>
                                <option value="">Select Relationship</option>
                                <option value="father" {{ old('relationship', $parent->relationship) == 'father' ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ old('relationship', $parent->relationship) == 'mother' ? 'selected' : '' }}>Mother</option>
                                <option value="guardian" {{ old('relationship', $parent->relationship) == 'guardian' ? 'selected' : '' }}>Guardian</option>
                            </select>
                            @error('relationship')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" name="occupation" class="form-control @error('occupation') is-invalid @enderror"
                                   value="{{ old('occupation', $parent->occupation) }}">
                            @error('occupation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror"
                               value="{{ old('whatsapp_number', $parent->whatsapp_number) }}">
                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-map-marker-alt me-2"></i> Address Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="2" required>{{ old('address', $parent->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                   value="{{ old('city', $parent->city) }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Postcode <span class="text-danger">*</span></label>
                            <input type="text" name="postcode" class="form-control @error('postcode') is-invalid @enderror"
                                   value="{{ old('postcode', $parent->postcode) }}" required>
                            @error('postcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">State <span class="text-danger">*</span></label>
                        <select name="state" class="form-select @error('state') is-invalid @enderror" required>
                            <option value="">Select State</option>
                            @foreach(['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Labuan', 'Putrajaya'] as $state)
                                <option value="{{ $state }}" {{ old('state', $parent->state) == $state ? 'selected' : '' }}>{{ $state }}</option>
                            @endforeach
                        </select>
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency & Notifications -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-phone-alt me-2"></i> Emergency Contact
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror"
                               value="{{ old('emergency_contact', $parent->emergency_contact) }}">
                        @error('emergency_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Emergency Contact Phone</label>
                        <input type="text" name="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror"
                               value="{{ old('emergency_phone', $parent->emergency_phone) }}">
                        @error('emergency_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bell me-2"></i> Notification Preferences
                </div>
                <div class="card-body">
                    @php
                        $notifPrefs = $parent->notification_preference ?? [];
                    @endphp
                    <div class="form-check mb-2">
                        <input type="checkbox" name="notification_preference[whatsapp]" class="form-check-input"
                               id="notif_whatsapp" value="1" {{ old('notification_preference.whatsapp', $notifPrefs['whatsapp'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_whatsapp">WhatsApp Notifications</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="notification_preference[email]" class="form-check-input"
                               id="notif_email" value="1" {{ old('notification_preference.email', $notifPrefs['email'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_email">Email Notifications</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="notification_preference[sms]" class="form-check-input"
                               id="notif_sms" value="1" {{ old('notification_preference.sms', $notifPrefs['sms'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_sms">SMS Notifications</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Link Students -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-link me-2"></i> Linked Students
                </div>
                <div class="card-body">
                    @if($availableStudents->count() > 0)
                    <p class="text-muted mb-3">Select students to link to this parent:</p>
                    <div class="row">
                        @foreach($availableStudents as $student)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="link_students[]" class="form-check-input"
                                       id="student_{{ $student->id }}" value="{{ $student->id }}"
                                       {{ $student->parent_id == $parent->id ? 'checked' : '' }}>
                                <label class="form-check-label" for="student_{{ $student->id }}">
                                    {{ $student->user->name }} ({{ $student->student_id }})
                                    @if($student->parent_id == $parent->id)
                                        <span class="badge bg-success ms-1">Currently Linked</span>
                                    @endif
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">No students available to link.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.parents.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Update Parent
        </button>
    </div>
</form>
@endsection

{{--
================================================================================
STATUTORY SETTINGS PARTIAL (FIXED VERSION)
================================================================================
File: resources/views/admin/teachers/partials/statutory-settings.blade.php

Usage:
  - CREATE form: @include('admin.teachers.partials.statutory-settings')
  - EDIT form:   @include('admin.teachers.partials.statutory-settings', ['teacher' => $teacher])

FIX: Added hidden fields to preserve EPF/SOCSO values when toggles are disabled.
     When form fields are disabled by JavaScript, they are NOT submitted with the
     form. Hidden fields ensure values are always submitted regardless of toggle state.
================================================================================
--}}

<!-- Bank & Statutory Information -->
<div class="col-md-12">
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-university me-2"></i> Bank & Statutory Information
        </div>
        <div class="card-body">

            <!-- Bank Details -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Name</label>
                    <input type="text"
                           name="bank_name"
                           class="form-control @error('bank_name') is-invalid @enderror"
                           value="{{ old('bank_name', $teacher->bank_name ?? '') }}"
                           placeholder="e.g., Maybank, CIMB, Public Bank">
                    @error('bank_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Account Number</label>
                    <input type="text"
                           name="bank_account"
                           class="form-control @error('bank_account') is-invalid @enderror"
                           value="{{ old('bank_account', $teacher->bank_account ?? '') }}"
                           placeholder="e.g., 1234567890">
                    @error('bank_account')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <!-- Statutory Contributions Section -->
            <h6 class="mb-3">
                <i class="fas fa-file-invoice-dollar me-2"></i>Statutory Contributions
            </h6>

            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Configure EPF and SOCSO deductions for this teacher.
                When disabled, no statutory contributions will be calculated during payslip generation.
                <br><small class="text-muted">The numbers are preserved when disabled and restored when re-enabled.</small>
            </div>

            <div class="row">
                <!-- EPF Settings -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100 bg-light border">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-piggy-bank text-primary me-2"></i>EPF (KWSP)
                            </h6>

                            <!-- EPF Enable/Disable Toggle -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="epfEnabled"
                                       name="epf_enabled"
                                       value="1"
                                       {{ old('epf_enabled', isset($teacher) ? $teacher->epf_enabled : true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="epfEnabled">
                                    <strong>Enable EPF Deduction</strong>
                                </label>
                            </div>

                            <!--
                                FIX: Hidden field to preserve EPF number when toggle is disabled
                                This ensures the value is always submitted with the form
                            -->
                            <input type="hidden"
                                   id="epfNumberHidden"
                                   name="epf_number_preserved"
                                   value="{{ old('epf_number', $teacher->epf_number ?? '') }}">

                            <!-- EPF Details (shown when enabled) -->
                            <div id="epfDetails">
                                <div class="mb-3">
                                    <label class="form-label">EPF Number</label>
                                    <input type="text"
                                           id="epfNumber"
                                           name="epf_number"
                                           class="form-control @error('epf_number') is-invalid @enderror"
                                           value="{{ old('epf_number', $teacher->epf_number ?? '') }}"
                                           placeholder="e.g., 12345678">
                                    @error('epf_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-calculator me-1"></i>
                                    <strong>Rates:</strong> Employee 11% + Employer 13%<br>
                                    <span class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Calculated from EPF contribution table
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SOCSO Settings -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100 bg-light border">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-shield-alt text-success me-2"></i>SOCSO (PERKESO)
                            </h6>

                            <!-- SOCSO Enable/Disable Toggle -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="socsoEnabled"
                                       name="socso_enabled"
                                       value="1"
                                       {{ old('socso_enabled', isset($teacher) ? $teacher->socso_enabled : true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="socsoEnabled">
                                    <strong>Enable SOCSO Deduction</strong>
                                </label>
                            </div>

                            <!--
                                FIX: Hidden fields to preserve SOCSO values when toggle is disabled
                                This ensures values are always submitted with the form
                            -->
                            <input type="hidden"
                                   id="socsoNumberHidden"
                                   name="socso_number_preserved"
                                   value="{{ old('socso_number', $teacher->socso_number ?? '') }}">
                            <input type="hidden"
                                   id="socsoTypeHidden"
                                   name="socso_type_preserved"
                                   value="{{ old('socso_type', $teacher->socso_type ?? 'regular') }}">

                            <!-- SOCSO Details (shown when enabled) -->
                            <div id="socsoDetails">
                                <div class="mb-3">
                                    <label class="form-label">SOCSO Type</label>
                                    <select name="socso_type"
                                            id="socsoType"
                                            class="form-select @error('socso_type') is-invalid @enderror">
                                        <option value="regular"
                                            {{ old('socso_type', $teacher->socso_type ?? 'regular') == 'regular' ? 'selected' : '' }}>
                                            Regular (Employment Injury & Invalidity)
                                        </option>
                                        <option value="insurance_only"
                                            {{ old('socso_type', $teacher->socso_type ?? '') == 'insurance_only' ? 'selected' : '' }}>
                                            Insurance Only (Age 60+ / Foreign Workers)
                                        </option>
                                    </select>
                                    @error('socso_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select "Insurance Only" for employees above 60 years or foreign workers.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">SOCSO Number</label>
                                    <input type="text"
                                           id="socsoNumber"
                                           name="socso_number"
                                           class="form-control @error('socso_number') is-invalid @enderror"
                                           value="{{ old('socso_number', $teacher->socso_number ?? '') }}"
                                           placeholder="e.g., A12345678">
                                    @error('socso_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="small text-muted">
                                    <span class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Calculated from SOCSO contribution table
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- JavaScript for Toggle Functionality --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // EPF Toggle Elements
    const epfEnabled = document.getElementById('epfEnabled');
    const epfDetails = document.getElementById('epfDetails');
    const epfNumber = document.getElementById('epfNumber');
    const epfNumberHidden = document.getElementById('epfNumberHidden');

    // SOCSO Toggle Elements
    const socsoEnabled = document.getElementById('socsoEnabled');
    const socsoDetails = document.getElementById('socsoDetails');
    const socsoNumber = document.getElementById('socsoNumber');
    const socsoType = document.getElementById('socsoType');
    const socsoNumberHidden = document.getElementById('socsoNumberHidden');
    const socsoTypeHidden = document.getElementById('socsoTypeHidden');

    /**
     * Toggle EPF details visibility and form inputs
     * FIX: Now preserves values in hidden field and syncs when re-enabled
     */
    function toggleEpfDetails() {
        if (epfEnabled && epfDetails) {
            if (epfEnabled.checked) {
                // EPF is ENABLED - show details, enable inputs
                epfDetails.style.opacity = '1';
                epfDetails.style.pointerEvents = 'auto';

                // Enable the visible input
                if (epfNumber) {
                    epfNumber.disabled = false;
                    // Restore value from hidden field if visible field is empty
                    if (!epfNumber.value && epfNumberHidden && epfNumberHidden.value) {
                        epfNumber.value = epfNumberHidden.value;
                    }
                }
            } else {
                // EPF is DISABLED - hide details, disable inputs
                epfDetails.style.opacity = '0.5';
                epfDetails.style.pointerEvents = 'none';

                // Disable the visible input but preserve value in hidden field
                if (epfNumber) {
                    // Store current value in hidden field before disabling
                    if (epfNumberHidden && epfNumber.value) {
                        epfNumberHidden.value = epfNumber.value;
                    }
                    epfNumber.disabled = true;
                }
            }
        }
    }

    /**
     * Toggle SOCSO details visibility and form inputs
     * FIX: Now preserves values in hidden fields and syncs when re-enabled
     */
    function toggleSocsoDetails() {
        if (socsoEnabled && socsoDetails) {
            if (socsoEnabled.checked) {
                // SOCSO is ENABLED - show details, enable inputs
                socsoDetails.style.opacity = '1';
                socsoDetails.style.pointerEvents = 'auto';

                // Enable the visible inputs
                if (socsoNumber) {
                    socsoNumber.disabled = false;
                    // Restore value from hidden field if visible field is empty
                    if (!socsoNumber.value && socsoNumberHidden && socsoNumberHidden.value) {
                        socsoNumber.value = socsoNumberHidden.value;
                    }
                }
                if (socsoType) {
                    socsoType.disabled = false;
                    // Restore value from hidden field
                    if (socsoTypeHidden && socsoTypeHidden.value) {
                        socsoType.value = socsoTypeHidden.value;
                    }
                }
            } else {
                // SOCSO is DISABLED - hide details, disable inputs
                socsoDetails.style.opacity = '0.5';
                socsoDetails.style.pointerEvents = 'none';

                // Disable the visible inputs but preserve values in hidden fields
                if (socsoNumber) {
                    // Store current value in hidden field before disabling
                    if (socsoNumberHidden && socsoNumber.value) {
                        socsoNumberHidden.value = socsoNumber.value;
                    }
                    socsoNumber.disabled = true;
                }
                if (socsoType) {
                    // Store current value in hidden field before disabling
                    if (socsoTypeHidden && socsoType.value) {
                        socsoTypeHidden.value = socsoType.value;
                    }
                    socsoType.disabled = true;
                }
            }
        }
    }

    /**
     * Sync visible field changes to hidden field (for EPF)
     */
    function syncEpfToHidden() {
        if (epfNumber && epfNumberHidden) {
            epfNumberHidden.value = epfNumber.value;
        }
    }

    /**
     * Sync visible field changes to hidden fields (for SOCSO)
     */
    function syncSocsoToHidden() {
        if (socsoNumber && socsoNumberHidden) {
            socsoNumberHidden.value = socsoNumber.value;
        }
        if (socsoType && socsoTypeHidden) {
            socsoTypeHidden.value = socsoType.value;
        }
    }

    // Attach event listeners for toggles
    if (epfEnabled) {
        epfEnabled.addEventListener('change', toggleEpfDetails);
        toggleEpfDetails(); // Initialize on page load
    }

    if (socsoEnabled) {
        socsoEnabled.addEventListener('change', toggleSocsoDetails);
        toggleSocsoDetails(); // Initialize on page load
    }

    // Sync visible fields to hidden fields when changed
    if (epfNumber) {
        epfNumber.addEventListener('input', syncEpfToHidden);
        epfNumber.addEventListener('change', syncEpfToHidden);
    }

    if (socsoNumber) {
        socsoNumber.addEventListener('input', syncSocsoToHidden);
        socsoNumber.addEventListener('change', syncSocsoToHidden);
    }

    if (socsoType) {
        socsoType.addEventListener('change', syncSocsoToHidden);
    }
});
</script>
@endpush

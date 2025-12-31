{{--
================================================================================
STATUTORY SETTINGS PARTIAL
================================================================================
File: resources/views/admin/teachers/partials/statutory-settings.blade.php

Usage:
  - CREATE form: @include('admin.teachers.partials.statutory-settings')
  - EDIT form:   @include('admin.teachers.partials.statutory-settings', ['teacher' => $teacher])
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

                            <!-- EPF Details (shown when enabled) -->
                            <div id="epfDetails">
                                <div class="mb-3">
                                    <label class="form-label">EPF Number</label>
                                    <input type="text"
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
    // EPF Toggle
    const epfEnabled = document.getElementById('epfEnabled');
    const epfDetails = document.getElementById('epfDetails');

    // SOCSO Toggle
    const socsoEnabled = document.getElementById('socsoEnabled');
    const socsoDetails = document.getElementById('socsoDetails');

    /**
     * Toggle EPF details visibility and form inputs
     */
    function toggleEpfDetails() {
        if (epfEnabled && epfDetails) {
            if (epfEnabled.checked) {
                epfDetails.style.opacity = '1';
                epfDetails.style.pointerEvents = 'auto';
            } else {
                epfDetails.style.opacity = '0.5';
                epfDetails.style.pointerEvents = 'none';
            }

            // Enable/disable form inputs
            const inputs = epfDetails.querySelectorAll('input, select');
            inputs.forEach(function(input) {
                input.disabled = !epfEnabled.checked;
            });
        }
    }

    /**
     * Toggle SOCSO details visibility and form inputs
     */
    function toggleSocsoDetails() {
        if (socsoEnabled && socsoDetails) {
            if (socsoEnabled.checked) {
                socsoDetails.style.opacity = '1';
                socsoDetails.style.pointerEvents = 'auto';
            } else {
                socsoDetails.style.opacity = '0.5';
                socsoDetails.style.pointerEvents = 'none';
            }

            // Enable/disable form inputs
            const inputs = socsoDetails.querySelectorAll('input, select');
            inputs.forEach(function(input) {
                input.disabled = !socsoEnabled.checked;
            });
        }
    }

    // Attach event listeners
    if (epfEnabled) {
        epfEnabled.addEventListener('change', toggleEpfDetails);
        toggleEpfDetails(); // Initialize on page load
    }

    if (socsoEnabled) {
        socsoEnabled.addEventListener('change', toggleSocsoDetails);
        toggleSocsoDetails(); // Initialize on page load
    }
});
</script>
@endpush

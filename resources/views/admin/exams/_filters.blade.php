{{-- Exam Filters --}}
{{-- CDN assets centrally managed in layouts/app.blade.php --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.exams.index') }}" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search exams..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{--
                |--------------------------------------------------------------
                | CHANGED: Grade Level dropdown — dynamic from master table
                |--------------------------------------------------------------
                | BEFORE: iterated plain strings from Student::distinct()
                | AFTER:  iterates GradeLevel objects (id + name) from DB
                |         On change, dynamically filters the Class dropdown
                |--------------------------------------------------------------
                --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Grade Level</label>
                    <select name="grade_level" class="form-select" id="gradeLevelFilter">
                        <option value="">All Grades</option>
                        @foreach($gradeLevels as $gl)
                            <option value="{{ $gl->id }}" {{ request('grade_level') == $gl->id ? 'selected' : '' }}>
                                {{ $gl->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{--
                |--------------------------------------------------------------
                | CHANGED: Class dropdown — data-grade-level-id added
                |--------------------------------------------------------------
                | Each <option> now carries its class's grade_level_id so JS
                | can show/hide options when a grade level is selected above.
                |--------------------------------------------------------------
                --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Class</label>
                    <select name="class_id" class="form-select" id="classFilter">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}"
                                    data-grade-level-id="{{ $class->grade_level_id }}"
                                    {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary" title="Filter">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary" title="Reset">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>

            {{-- Row 2: Date range --}}
            <div class="row g-3 mt-1">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Grade Level → Class dynamic linking --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const gradeLevelSelect = document.getElementById('gradeLevelFilter');
    const classSelect       = document.getElementById('classFilter');

    // Store all original class options (skip the first "All Classes" option)
    const allClassOptions = Array.from(classSelect.options).map(function (opt) {
        return {
            value:        opt.value,
            text:         opt.text,
            gradeLevelId: opt.getAttribute('data-grade-level-id'),
            selected:     opt.selected
        };
    });

    gradeLevelSelect.addEventListener('change', function () {
        const selectedGrade = this.value;

        // Clear all options except "All Classes"
        classSelect.innerHTML = '';

        allClassOptions.forEach(function (opt) {
            // Always keep the "All Classes" placeholder (value = "")
            if (opt.value === '') {
                const o = new Option(opt.text, opt.value, false, false);
                classSelect.add(o);
                return;
            }

            // Show option if no grade selected OR grade matches
            if (!selectedGrade || opt.gradeLevelId === selectedGrade) {
                const o = new Option(opt.text, opt.value, false, opt.selected);
                o.setAttribute('data-grade-level-id', opt.gradeLevelId);
                classSelect.add(o);
            }
        });

        // If the previously selected class is no longer visible, reset to "All Classes"
        const currentClassValue = '{{ request('class_id') }}';
        const stillExists = Array.from(classSelect.options).some(function (opt) {
            return opt.value === currentClassValue;
        });
        if (!stillExists) {
            classSelect.value = '';
        }
    });

    // Trigger on page load to apply filter if grade_level is pre-selected
    if (gradeLevelSelect.value) {
        gradeLevelSelect.dispatchEvent(new Event('change'));

        // Re-select the class if it was in the URL
        const preselectedClass = '{{ request('class_id') }}';
        if (preselectedClass) {
            classSelect.value = preselectedClass;
        }
    }
});
</script>

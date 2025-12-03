<div class="collapse mb-3" id="filtersCollapse">
    <form method="GET" action="{{ route('admin.exams.index') }}">
        <div class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="subject_id" class="form-select">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                    <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
                </div>
            </div>
        </div>
        <div class="mt-2">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-search"></i> Apply Filters
            </button>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>
</div>

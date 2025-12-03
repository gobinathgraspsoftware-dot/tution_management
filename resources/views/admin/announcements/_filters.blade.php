<div class="collapse mb-3" id="filtersCollapse">
    <form method="GET" action="{{ route('admin.announcements.index') }}">
        <div class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                    <option value="academic" {{ request('type') == 'academic' ? 'selected' : '' }}>Academic</option>
                    <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Event</option>
                    <option value="holiday" {{ request('type') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                    <option value="urgent" {{ request('type') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="target_audience" class="form-select">
                    <option value="">All Audiences</option>
                    <option value="all" {{ request('target_audience') == 'all' ? 'selected' : '' }}>All</option>
                    <option value="students" {{ request('target_audience') == 'students' ? 'selected' : '' }}>Students</option>
                    <option value="parents" {{ request('target_audience') == 'parents' ? 'selected' : '' }}>Parents</option>
                    <option value="teachers" {{ request('target_audience') == 'teachers' ? 'selected' : '' }}>Teachers</option>
                </select>
            </div>
        </div>
        <div class="mt-2">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-search"></i> Apply Filters
            </button>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>
</div>

{{-- Bulk Entry Form Partial --}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="bulkEntryTable">
        <thead class="table-light">
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Student ID</th>
                <th style="width: 25%;">Student Name</th>
                <th style="width: 15%;">Marks (Max: {{ number_format($exam->max_marks, 0) }})</th>
                <th style="width: 10%;">Percentage</th>
                <th style="width: 10%;">Grade</th>
                <th style="width: 20%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $enrollment)
                @php
                    $student = $enrollment->student;
                    $existingResult = $student->results->first();
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <span class="fw-semibold text-muted">{{ $student->student_id ?? 'N/A' }}</span>
                        <input type="hidden" name="results[{{ $index }}][student_id]" value="{{ $student->id }}">
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                                <i class="fas fa-user text-muted small"></i>
                            </div>
                            <span>{{ $student->user->name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td>
                        <input type="number" 
                               name="results[{{ $index }}][marks_obtained]" 
                               class="form-control form-control-sm marks-input" 
                               value="{{ old("results.{$index}.marks_obtained", $existingResult->marks_obtained ?? '') }}"
                               min="0" 
                               max="{{ $exam->max_marks }}" 
                               step="0.01"
                               placeholder="0"
                               data-max="{{ $exam->max_marks }}"
                               data-index="{{ $index }}">
                    </td>
                    <td>
                        <span class="percentage-display badge bg-light text-dark" id="percentage-{{ $index }}">
                            {{ $existingResult ? number_format($existingResult->percentage, 1) . '%' : '-' }}
                        </span>
                    </td>
                    <td>
                        <span class="grade-display badge" id="grade-{{ $index }}">
                            @if($existingResult && $existingResult->grade)
                                @php
                                    $gradeColors = [
                                        'A+' => 'bg-success', 'A' => 'bg-success',
                                        'B+' => 'bg-info', 'B' => 'bg-info',
                                        'C' => 'bg-warning text-dark', 'D' => 'bg-warning text-dark',
                                        'F' => 'bg-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $gradeColors[$existingResult->grade] ?? 'bg-secondary' }}">
                                    {{ $existingResult->grade }}
                                </span>
                            @else
                                -
                            @endif
                        </span>
                    </td>
                    <td>
                        <input type="text" 
                               name="results[{{ $index }}][remarks]" 
                               class="form-control form-control-sm" 
                               value="{{ old("results.{$index}.remarks", $existingResult->remarks ?? '') }}"
                               placeholder="Optional remarks"
                               maxlength="500">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate percentage and grade on marks input
    $('.marks-input').on('input change', function() {
        var $input = $(this);
        var marks = parseFloat($input.val()) || 0;
        var maxMarks = parseFloat($input.data('max')) || 100;
        var index = $input.data('index');

        if (marks > maxMarks) {
            $input.val(maxMarks);
            marks = maxMarks;
        }
        if (marks < 0) {
            $input.val(0);
            marks = 0;
        }

        var percentage = (marks / maxMarks) * 100;
        var grade = calculateGrade(percentage);

        $('#percentage-' + index).text(percentage.toFixed(1) + '%');

        var gradeColorClass = getGradeColorClass(grade);
        $('#grade-' + index).html('<span class="badge ' + gradeColorClass + '">' + grade + '</span>');
    });

    function calculateGrade(percentage) {
        if (percentage >= 90) return 'A+';
        if (percentage >= 80) return 'A';
        if (percentage >= 70) return 'B+';
        if (percentage >= 60) return 'B';
        if (percentage >= 50) return 'C';
        if (percentage >= 40) return 'D';
        return 'F';
    }

    function getGradeColorClass(grade) {
        var colors = {
            'A+': 'bg-success', 'A': 'bg-success',
            'B+': 'bg-info', 'B': 'bg-info',
            'C': 'bg-warning text-dark', 'D': 'bg-warning text-dark',
            'F': 'bg-danger'
        };
        return colors[grade] || 'bg-secondary';
    }
});
</script>
@endpush

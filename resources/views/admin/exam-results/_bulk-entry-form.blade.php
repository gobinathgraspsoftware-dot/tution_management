<div class="mb-3">
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="quickFillAll()">
        <i class="fas fa-magic"></i> Quick Fill All
    </button>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Student Name</th>
                <th style="width: 15%;">Student ID</th>
                <th style="width: 15%;">Marks Obtained<span class="text-danger">*</span></th>
                <th style="width: 10%;">Percentage</th>
                <th style="width: 10%;">Grade</th>
                <th style="width: 20%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $enrollment)
                @php
                    $student = $enrollment->student;
                    $existingResult = $student->results()->where('exam_id', $exam->id)->first();
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $student->user->name }}</strong>
                        <input type="hidden" name="results[{{ $index }}][student_id]" value="{{ $student->id }}">
                    </td>
                    <td>{{ $student->student_id }}</td>
                    <td>
                        <input type="number"
                               name="results[{{ $index }}][marks_obtained]"
                               id="marks_{{ $index }}"
                               class="form-control form-control-sm"
                               min="0"
                               max="{{ $exam->max_marks }}"
                               step="0.01"
                               value="{{ $existingResult ? $existingResult->marks_obtained : '' }}"
                               onchange="calculateResult({{ $index }})"
                               placeholder="0">
                        <small class="text-muted">Max: {{ $exam->max_marks }}</small>
                    </td>
                    <td>
                        <span id="percentage_{{ $index }}" class="badge bg-info">
                            {{ $existingResult ? number_format($existingResult->percentage, 2) : '0.00' }}%
                        </span>
                    </td>
                    <td>
                        <span id="grade_{{ $index }}" class="badge bg-primary">
                            {{ $existingResult ? $existingResult->grade : '-' }}
                        </span>
                    </td>
                    <td>
                        <input type="text"
                               name="results[{{ $index }}][remarks]"
                               class="form-control form-control-sm"
                               value="{{ $existingResult ? $existingResult->remarks : '' }}"
                               placeholder="Optional">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

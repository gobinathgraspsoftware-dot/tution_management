@component('mail::message')
# Attendance Report

Dear {{ $parent_name ?? 'Parent' }},

This is the attendance report for **{{ $student_name }}** for the period from **{{ \Carbon\Carbon::parse($date_from)->format('d F Y') }}** to **{{ \Carbon\Carbon::parse($date_to)->format('d F Y') }}**.

## Attendance Summary

@component('mail::table')
| Metric | Value |
|:-------|------:|
| Total Sessions | {{ $total_sessions }} |
| Present | {{ $present_count }} |
| Absent | {{ $absent_count }} |
| **Attendance Rate** | **{{ number_format($attendance_percentage, 1) }}%** |
@endcomponent

@if($attendance_percentage < 75)
@component('mail::panel')
**⚠️ Attention Required**

Your child's attendance is below the recommended 75% threshold. Regular attendance is crucial for academic success. Please ensure your child attends classes regularly.
@endcomponent
@elseif($attendance_percentage >= 90)
@component('mail::panel')
**🎉 Excellent Attendance!**

Great job! Your child has maintained excellent attendance this period. Keep up the good work!
@endcomponent
@endif

@if(isset($class_breakdown) && count($class_breakdown) > 0)
## Class-wise Breakdown

@component('mail::table')
| Class | Subject | Present | Total | Rate |
|:------|:--------|--------:|------:|-----:|
@foreach($class_breakdown as $class)
| {{ $class['class_name'] }} | {{ $class['subject'] }} | {{ $class['present'] }} | {{ $class['total'] }} | {{ number_format($class['percentage'], 1) }}% |
@endforeach
@endcomponent
@endif

@if(isset($absent_dates) && count($absent_dates) > 0)
## Absent Dates

Your child was absent on the following dates:

@foreach($absent_dates as $date)
- {{ \Carbon\Carbon::parse($date['date'])->format('l, d F Y') }} - {{ $date['class_name'] }}
@endforeach
@endif

---

For more detailed information, please log in to your parent portal.

@component('mail::button', ['url' => config('app.url') . '/parent/attendance'])
View Full Report
@endcomponent

If you have any questions about your child's attendance, please contact the administration.

Best regards,<br>
{{ config('app.name') }}

---

<small>
This is an automated email from the Tuition Centre Management System. Please do not reply directly to this email.
</small>
@endcomponent

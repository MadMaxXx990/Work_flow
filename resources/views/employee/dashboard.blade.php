@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['Assigned Tasks', $stats['totalAssigned'] ?? 0, 'bi-kanban-fill',        '#4f46e5', '#eef2ff'],
        ['Completed',      $stats['completed'] ?? 0,     'bi-check-circle-fill',  '#10b981', '#d1fae5'],
        ['Overdue',        $stats['overdue'] ?? 0,       'bi-exclamation-circle-fill', '#ef4444', '#fee2e2'],
    ] as [$label, $value, $icon, $color, $bg])
    <div class="col-6 col-md-4">
        <div class="card-fl p-3 d-flex align-items-center gap-3">
            <div class="rounded-3" style="background:{{ $bg }};width:44px;height:44px;place-items:center;display:grid;">
                <i class="bi {{ $icon }} fs-5" style="color:{{ $color }};"></i>
            </div>
            <div>
                <div class="fw-bold fs-5 lh-1">{{ $value }}</div>
                <div class="text-muted" style="font-size:.75rem;">{{ $label }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Today attendance --}}
@if(isset($todayAttendance))
<div class="alert alert-info d-flex gap-2 align-items-center py-2 small mb-4">
    <i class="bi bi-calendar-check-fill"></i>
    Today: Time in <strong>{{ $todayAttendance->time_in ?? '—' }}</strong>
    / Time out <strong>{{ $todayAttendance->time_out ?? '—' }}</strong>
    — <strong>{{ $todayAttendance->attendance_status }}</strong>
</div>
@else
<div class="alert alert-warning d-flex gap-2 align-items-center py-2 small mb-4">
    <i class="bi bi-exclamation-circle-fill"></i>
    No attendance record for today.
    <a href="{{ route('employee.attendance.index') }}" class="ms-auto btn btn-sm btn-warning py-0 px-2">Log Attendance</a>
</div>
@endif

<h6 class="fw-semibold mb-3">My Recent Tasks</h6>
<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Task</th><th>Priority</th><th>Due Date</th><th>Stage</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($recentTasks as $task)
                <tr>
                    <td class="fw-semibold">{{ $task->task_title }}</td>
                    <td><span class="priority-{{ strtolower($task->priority?->priority_name ?? 'low') }} fw-semibold">{{ $task->priority?->priority_name }}</span></td>
                    <td class="{{ $task->due_date < now() ? 'text-danger' : '' }}">{{ $task->due_date?->format('M j, Y') }}</td>
                    <td>
                        @php $s = strtolower(str_replace(' ', '_', $task->stage?->stage_name ?? '')); @endphp
                        <span class="badge badge-stage-{{ $s }}">{{ $task->stage?->stage_name }}</span>
                    </td>
                    <td><a href="{{ route('employee.tasks.show', $task->task_id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">No tasks assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

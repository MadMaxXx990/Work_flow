@extends('layouts.app')
@section('title', $employee->full_name)
@section('page-title', 'Employee Profile')

@section('content')
<div class="row g-4">

    {{-- ── Left: Profile card ──────────────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card-fl p-4 text-center mb-3">
            @if($employee->profile_photo_url)
                <img src="{{ asset('storage/'.$employee->profile_photo_url) }}"
                     class="rounded-circle mb-3" width="80" height="80" style="object-fit:cover;">
            @else
                <div class="rounded-circle bg-primary text-white fw-bold mx-auto mb-3 d-grid"
                     style="width:80px;height:80px;place-items:center;font-size:2rem;display:grid;">
                    {{ strtoupper(substr($employee->first_name,0,1)) }}
                </div>
            @endif
            <h5 class="fw-bold mb-0">{{ $employee->full_name }}</h5>
            <p class="text-muted small mb-2">{{ $employee->position?->position_name }}</p>

            @php
                $badge = match($employee->status) { 'Active' => 'success', 'On Leave' => 'warning', default => 'secondary' };
            @endphp
            <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }} border mb-3">{{ $employee->status }}</span>

            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('admin.employees.edit', $employee->employee_id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="{{ route('admin.employees.destroy', $employee->employee_id) }}"
                      method="POST" onsubmit="return confirm('Deactivate this employee?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-person-dash me-1"></i> Deactivate
                    </button>
                </form>
            </div>
        </div>

        {{-- Info list --}}
        <div class="card-fl p-3 small">
            @foreach([
                ['bi-envelope',    'Email',       $employee->email],
                ['bi-telephone',   'Phone',       $employee->phone ?? '—'],
                ['bi-building',    'Department',  $employee->department?->department_name ?? '—'],
                ['bi-person-fill', 'Supervisor',  $employee->supervisor?->full_name ?? '—'],
                ['bi-briefcase',   'Emp. Type',   $employee->employment_type ?? '—'],
                ['bi-calendar',    'Hire Date',   $employee->hire_date?->format('M j, Y') ?? '—'],
                ['bi-shield-fill', 'System Role', $employee->user?->role?->role_name ?? '—'],
                ['bi-person',      'Username',    $employee->user?->username ?? '—'],
            ] as [$icon, $label, $value])
            <div class="d-flex align-items-start gap-2 py-2 border-bottom">
                <i class="bi {{ $icon }} text-muted mt-1" style="width:16px;"></i>
                <div>
                    <div class="text-muted" style="font-size:.7rem;">{{ $label }}</div>
                    <div class="fw-semibold">{{ $value }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Right: Stats + Tasks + Attendance ───────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Stats row --}}
        <div class="row g-3 mb-4">
            @foreach([
                ['Tasks Assigned', $totalTasks,     '#4f46e5', '#eef2ff', 'bi-kanban-fill'],
                ['Completed',      $completedTasks, '#10b981', '#d1fae5', 'bi-check-circle-fill'],
                ['Completion %',
                    $totalTasks > 0 ? round($completedTasks / $totalTasks * 100) . '%' : '—',
                    '#f59e0b', '#fef3c7', 'bi-bar-chart-fill'],
            ] as [$lbl, $val, $color, $bg, $icon])
            <div class="col-4">
                <div class="card-fl p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3" style="background:{{ $bg }};width:40px;height:40px;place-items:center;display:grid;">
                        <i class="bi {{ $icon }}" style="color:{{ $color }};font-size:1rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold lh-1">{{ $val }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $lbl }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Task assignments --}}
        <div class="card-fl mb-4">
            <div class="px-3 pt-3 pb-2 border-bottom d-flex justify-content-between">
                <span class="fw-semibold small">Assigned Tasks</span>
                <span class="text-muted small">{{ $employee->taskAssignments->count() }} total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr><th>Task</th><th>Priority</th><th>Due Date</th><th>Stage</th></tr>
                    </thead>
                    <tbody>
                        @forelse($employee->taskAssignments->take(8) as $assignment)
                        <tr>
                            <td class="fw-semibold">
                                <a href="{{ route('admin.tasks.show', $assignment->task?->task_id) }}"
                                   class="text-decoration-none text-dark">
                                    {{ $assignment->task?->task_title ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <span class="priority-{{ strtolower($assignment->task?->priority?->priority_name ?? 'low') }} fw-semibold">
                                    {{ $assignment->task?->priority?->priority_name ?? '—' }}
                                </span>
                            </td>
                            <td class="{{ $assignment->task?->due_date < now() ? 'text-danger' : 'text-muted' }}">
                                {{ $assignment->task?->due_date?->format('M j, Y') ?? '—' }}
                            </td>
                            <td>
                                @php $s = strtolower(str_replace(' ', '_', $assignment->task?->stage?->stage_name ?? '')); @endphp
                                <span class="badge badge-stage-{{ $s }}">
                                    {{ $assignment->task?->stage?->stage_name ?? '—' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">No tasks assigned.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent attendance --}}
        <div class="card-fl">
            <div class="px-3 pt-3 pb-2 border-bottom d-flex justify-content-between">
                <span class="fw-semibold small">Recent Attendance</span>
                <a href="{{ route('admin.attendance.index', ['employee_id' => $employee->employee_id]) }}"
                   class="btn btn-sm btn-outline-secondary py-0 px-2 small">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 small">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($employee->attendance as $a)
                        <tr>
                            <td>{{ $a->date?->format('M j, Y') }}</td>
                            <td>{{ $a->time_in ?? '—' }}</td>
                            <td>{{ $a->time_out ?? '—' }}</td>
                            <td>
                                @php $ab = match($a->attendance_status) { 'Present' => 'success', 'Absent' => 'danger', default => 'warning' }; @endphp
                                <span class="badge bg-{{ $ab }}-subtle text-{{ $ab }}">{{ $a->attendance_status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">No attendance records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Manager Dashboard')

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['My Tasks',         $totalMyTasks,    'bi-kanban-fill',        '#4f46e5', '#eef2ff'],
        ['Pending Approval', $pendingApproval, 'bi-eye-fill',           '#f59e0b', '#fef3c7'],
        ['Completed',        $completedTasks,  'bi-check-circle-fill',  '#10b981', '#d1fae5'],
        ['Overdue',          $overdueTasks,    'bi-exclamation-circle-fill', '#ef4444', '#fee2e2'],
        ['Total Employees',  $totalEmployees,  'bi-people-fill',        '#0891b2', '#ecfeff'],
    ] as [$label, $value, $icon, $color, $bg])
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-fl p-3 d-flex align-items-center gap-3">
            <div class="rounded-3 d-grid" style="background:{{ $bg }};width:44px;height:44px;place-items:center;display:grid;">
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

{{-- ── Team Workload ────────────────────────────────────────────────────── --}}
<div class="card-fl mb-4">
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
        <div>
            <span class="fw-semibold">Team Workload</span>
            <span class="text-muted small ms-2">active tasks per employee · sorted heaviest first</span>
        </div>
        <a href="{{ route('manager.tasks.create') }}" class="btn btn-sm btn-primary py-0 px-2">
            <i class="bi bi-plus-circle me-1"></i> Assign Task
        </a>
    </div>
    <div class="p-3">
        @forelse($teamWorkload as $member)
        @php
            $pct      = $maxWorkload > 0 ? round(($member->active_tasks / $maxWorkload) * 100) : 0;
            $barColor = match(true) {
                $member->active_tasks >= 7 => '#ef4444',
                $member->active_tasks >= 4 => '#f59e0b',
                $member->active_tasks >= 1 => '#10b981',
                default                    => '#d1d5db',
            };
            $label = match(true) {
                $member->active_tasks >= 7 => 'Heavy',
                $member->active_tasks >= 4 => 'Moderate',
                $member->active_tasks >= 1 => 'Available',
                default                    => 'Free',
            };
        @endphp
        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
            <div class="rounded-circle text-white fw-bold d-grid flex-shrink-0"
                 style="width:34px;height:34px;background:{{ $barColor }};place-items:center;display:grid;font-size:.78rem;">
                {{ strtoupper(substr($member->first_name, 0, 1)) }}
            </div>
            <div style="min-width:140px;">
                <div class="fw-semibold small">{{ $member->first_name }} {{ $member->last_name }}</div>
                <div style="font-size:.7rem;color:#9ca3af;">
                    @if($member->overdue_tasks > 0)
                        <span class="text-danger">{{ $member->overdue_tasks }} overdue</span>
                    @else
                        No overdue
                    @endif
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="progress" style="height:8px;border-radius:99px;">
                    <div class="progress-bar" role="progressbar"
                         style="width:{{ max($pct, 3) }}%;background:{{ $barColor }};border-radius:99px;transition:width .4s ease;">
                    </div>
                </div>
            </div>
            <div class="text-end flex-shrink-0" style="min-width:110px;">
                <span class="fw-bold small">{{ $member->active_tasks }}</span>
                <span class="text-muted small"> active task{{ $member->active_tasks != 1 ? 's' : '' }}</span>
                <span class="badge ms-1 rounded-pill"
                      style="background:{{ $barColor }}22;color:{{ $barColor }};font-size:.65rem;border:1px solid {{ $barColor }}44;">
                    {{ $label }}
                </span>
            </div>
        </div>
        @empty
        <div class="text-center py-4 text-muted small">
            <i class="bi bi-people opacity-50 d-block fs-3 mb-1"></i>
            No active task assignments yet. <a href="{{ route('manager.tasks.create') }}">Assign the first task</a>.
        </div>
        @endforelse
    </div>
</div>

{{-- ── Recent Tasks ─────────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-semibold mb-0">Recent Tasks</h6>
    <a href="{{ route('manager.tasks.index') }}" class="btn btn-outline-secondary btn-sm">View all</a>
</div>
<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Task</th><th>Assigned To</th><th>Priority</th><th>Due Date</th><th>Stage</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($recentTasks as $task)
                <tr>
                    <td class="fw-semibold">{{ $task->task_title }}</td>
                    <td>
                        @foreach($task->assignments->take(2) as $a)
                            <span class="badge bg-light text-dark border me-1">{{ $a->employee?->full_name }}</span>
                        @endforeach
                    </td>
                    <td><span class="priority-{{ strtolower($task->priority?->priority_name ?? 'low') }} fw-semibold">{{ $task->priority?->priority_name ?? '—' }}</span></td>
                    <td class="{{ $task->due_date < now() ? 'text-danger' : '' }}">{{ $task->due_date?->format('M j, Y') }}</td>
                    <td>
                        @php $s = strtolower(str_replace(' ', '_', $task->stage?->stage_name ?? '')); @endphp
                        <span class="badge badge-stage-{{ $s }}">{{ $task->stage?->stage_name }}</span>
                    </td>
                    <td><a href="{{ route('manager.tasks.show', $task->task_id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No tasks assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

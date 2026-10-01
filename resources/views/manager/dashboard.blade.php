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

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-semibold mb-0">Recent Tasks</h6>
    <a href="{{ route('manager.tasks.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Assign Task
    </a>
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

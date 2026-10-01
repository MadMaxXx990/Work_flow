@extends('layouts.app')
@section('title','Task Board')
@section('page-title','Task Board')

@push('styles')
<style>
.kanban-wrap { display:flex; gap:1rem; overflow-x:auto; padding-bottom:1rem; }
.kanban-col  { flex:0 0 260px; background:#f8f9fa; border-radius:10px; padding:.75rem; }
.kanban-col-header { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.75rem; display:flex; justify-content:space-between; align-items:center; }
.kanban-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:.75rem; margin-bottom:.6rem; cursor:pointer; transition:box-shadow .15s; }
.kanban-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); }
.kanban-card-title { font-weight:600; font-size:.82rem; margin-bottom:.3rem; color:#1e1b4b; }
.kanban-card-meta  { font-size:.7rem; color:#9ca3af; }
.progress { height:4px; border-radius:99px; }
</style>
@endpush

@section('content')
{{-- ── Toolbar ────────────────────────────────────────────────────────────── --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-4">
    <form class="d-flex flex-wrap gap-2" method="GET">
        <input type="text" name="search" class="form-control form-control-sm" style="width:200px"
               placeholder="Search tasks…" value="{{ request('search') }}">
        <select name="priority_id" class="form-select form-select-sm" style="width:140px">
            <option value="">All Priorities</option>
            @foreach($priorities as $p)
                <option value="{{ $p->priority_id }}" {{ request('priority_id') == $p->priority_id ? 'selected':'' }}>
                    {{ $p->priority_name }}</option>
            @endforeach
        </select>
        <select name="employee_id" class="form-select form-select-sm" style="width:170px">
            <option value="">All Employees</option>
            @foreach($employees as $e)
                <option value="{{ $e->employee_id }}" {{ request('employee_id') == $e->employee_id ? 'selected':'' }}>
                    {{ $e->full_name }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        @if(request()->hasAny(['search','priority_id','employee_id']))
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-light">Clear</a>
        @endif
    </form>
    <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Assign Task
    </a>
</div>

{{-- ── Overdue alert ───────────────────────────────────────────────────────── --}}
@if($overdueTasks->count() > 0)
<div class="alert alert-danger d-flex gap-2 align-items-center py-2 small mb-4">
    <i class="bi bi-exclamation-circle-fill"></i>
    <strong>{{ $overdueTasks->count() }} overdue task(s).</strong>
    Review them and update their stages.
</div>
@endif

{{-- ── Kanban Board ────────────────────────────────────────────────────────── --}}
<div class="kanban-wrap">
    @foreach($stages as $stage)
    @php
        $stageTasks = $tasksByStage[$stage->stage_name] ?? collect();
        $color = match($stage->stage_name) {
            'Pending'     => '#9ca3af',
            'In Progress' => '#3b82f6',
            'For Review'  => '#f59e0b',
            'Completed'   => '#10b981',
            'Overdue'     => '#ef4444',
            default       => '#6b7280',
        };
    @endphp
    <div class="kanban-col">
        <div class="kanban-col-header">
            <span style="color:{{ $color }}">{{ $stage->stage_name }}</span>
            <span class="badge rounded-pill" style="background:{{ $color }}20;color:{{ $color }}">
                {{ $stageTasks->count() }}
            </span>
        </div>

        @forelse($stageTasks as $task)
        <a href="{{ route('admin.tasks.show', $task->task_id) }}" class="text-decoration-none">
        <div class="kanban-card">
            <div class="kanban-card-title">{{ Str::limit($task->task_title, 45) }}</div>
            <div class="kanban-card-meta d-flex justify-content-between mb-2">
                <span class="priority-{{ strtolower($task->priority?->priority_name ?? 'low') }} fw-semibold">
                    {{ $task->priority?->priority_name }}
                </span>
                <span class="{{ $task->due_date < now() ? 'text-danger' : '' }}">
                    {{ $task->due_date?->format('M j') }}
                </span>
            </div>
            @php $progress = $task->latestProgress(); @endphp
            <div class="progress mb-2">
                <div class="progress-bar" style="width:{{ $progress }}%;background:{{ $color }};"></div>
            </div>
            <div class="kanban-card-meta d-flex justify-content-between">
                <span>{{ $task->assignments->count() }} assigned</span>
                <span>{{ $progress }}%</span>
            </div>
        </div>
        </a>
        @empty
        <div class="text-center py-3 text-muted" style="font-size:.75rem;">No tasks</div>
        @endforelse
    </div>
    @endforeach
</div>
@endsection

@extends('layouts.app')
@section('title','My Tasks')
@section('page-title','My Tasks')

@push('styles')
<style>
.kanban-wrap{display:flex;gap:1rem;overflow-x:auto;padding-bottom:1rem;}
.kanban-col{flex:0 0 240px;background:#f8f9fa;border-radius:10px;padding:.75rem;}
.kanban-col-header{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.75rem;display:flex;justify-content:space-between;}
.kanban-card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:.75rem;margin-bottom:.6rem;transition:box-shadow .15s;}
.kanban-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.08);}
.progress{height:4px;border-radius:99px;}
</style>
@endpush

@section('content')
@if($overdueTasks->count())
<div class="alert alert-danger d-flex gap-2 align-items-center py-2 small mb-4">
    <i class="bi bi-exclamation-circle-fill"></i>
    <strong>{{ $overdueTasks->count() }} overdue task(s).</strong> Submit them for review promptly.
</div>
@endif

<div class="kanban-wrap">
    @foreach($stages as $stage)
    @php
        $stageTasks = $tasksByStage[$stage->stage_name] ?? collect();
        $color = match($stage->stage_name){ 'Pending'=>'#9ca3af','In Progress'=>'#3b82f6','For Review'=>'#f59e0b','Completed'=>'#10b981',default=>'#ef4444' };
    @endphp
    <div class="kanban-col">
        <div class="kanban-col-header">
            <span style="color:{{ $color }}">{{ $stage->stage_name }}</span>
            <span class="badge rounded-pill" style="background:{{ $color }}20;color:{{ $color }}">{{ $stageTasks->count() }}</span>
        </div>
        @forelse($stageTasks as $task)
        <a href="{{ route('employee.tasks.show', $task->task_id) }}" class="text-decoration-none">
        <div class="kanban-card">
            <div class="fw-semibold small mb-1" style="color:#1e1b4b;">{{ Str::limit($task->task_title, 42) }}</div>
            <div class="d-flex justify-content-between mb-2" style="font-size:.7rem;color:#9ca3af;">
                <span class="priority-{{ strtolower($task->priority?->priority_name??'low') }} fw-semibold">{{ $task->priority?->priority_name }}</span>
                <span class="{{ $task->due_date < now() ? 'text-danger':'' }}">{{ $task->due_date?->format('M j') }}</span>
            </div>
            @php $prog = $task->latestProgress(); @endphp
            <div class="progress"><div class="progress-bar" style="width:{{ $prog }}%;background:{{ $color }};"></div></div>
            <div class="d-flex justify-content-end mt-1" style="font-size:.7rem;color:#9ca3af;">{{ $prog }}%</div>
        </div></a>
        @empty
        <div class="text-center py-3 text-muted" style="font-size:.75rem;">No tasks</div>
        @endforelse
    </div>
    @endforeach
</div>
@endsection

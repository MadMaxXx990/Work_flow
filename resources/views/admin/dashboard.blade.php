@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
{{-- ── Stat cards ──────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total Employees', $totalEmployees,  'bi-people-fill',       '#4f46e5', '#eef2ff'],
        ['Total Tasks',     $totalTasks,      'bi-kanban-fill',        '#0891b2', '#ecfeff'],
        ['Pending',         $pendingCount,    'bi-hourglass-split',    '#9ca3af', '#f3f4f6'],
        ['In Progress',     $inProgressCount, 'bi-arrow-repeat',       '#3b82f6', '#dbeafe'],
        ['For Review',      $forReviewCount,  'bi-eye-fill',           '#f59e0b', '#fef3c7'],
        ['Completed',       $completedCount,  'bi-check-circle-fill',  '#10b981', '#d1fae5'],
        ['Overdue',         $overdueCount,    'bi-exclamation-circle-fill', '#ef4444', '#fee2e2'],
        ['Present Today',   $presentToday,    'bi-calendar-check-fill','#8b5cf6', '#f5f3ff'],
    ] as [$label, $value, $icon, $color, $bg])
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-fl p-3 d-flex align-items-center gap-3">
            <div class="rounded-3 d-grid place-items-center" style="background:{{ $bg }};width:44px;height:44px;place-items:center;">
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

{{-- ── Charts ──────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="card-fl p-3 h-100">
            <p class="fw-semibold mb-3 small text-uppercase text-muted" style="letter-spacing:.08em;">Tasks by Stage</p>
            <canvas id="stageChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card-fl p-3 h-100">
            <p class="fw-semibold mb-3 small text-uppercase text-muted" style="letter-spacing:.08em;">Tasks Completed — Last 7 Days</p>
            <canvas id="weeklyChart" height="180"></canvas>
        </div>
    </div>
</div>

{{-- ── Team Workload ────────────────────────────────────────────────────── --}}
<div class="card-fl mb-4">
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
        <div>
            <span class="fw-semibold">Team Workload</span>
            <span class="text-muted small ms-2">active tasks per employee · sorted heaviest first</span>
        </div>
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2">Task Board</a>
    </div>
    <div class="p-3">
        @forelse($teamWorkload as $member)
        @php
            $pct      = $maxWorkload > 0 ? round(($member->active_tasks / $maxWorkload) * 100) : 0;
            $barColor = match(true) {
                $member->active_tasks >= 7 => '#ef4444',  // red  — heavy load
                $member->active_tasks >= 4 => '#f59e0b',  // amber — moderate
                $member->active_tasks >= 1 => '#10b981',  // green — light
                default                    => '#d1d5db',  // grey  — nothing assigned
            };
            $label = match(true) {
                $member->active_tasks >= 7 => 'Heavy',
                $member->active_tasks >= 4 => 'Moderate',
                $member->active_tasks >= 1 => 'Available',
                default                    => 'Free',
            };
        @endphp
        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
            {{-- Avatar --}}
            <div class="rounded-circle text-white fw-bold d-grid flex-shrink-0"
                 style="width:34px;height:34px;background:{{ $barColor }};place-items:center;display:grid;font-size:.78rem;">
                {{ strtoupper(substr($member->first_name, 0, 1)) }}
            </div>
            {{-- Name --}}
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
            {{-- Load bar --}}
            <div class="flex-grow-1">
                <div class="progress" style="height:8px;border-radius:99px;">
                    <div class="progress-bar" role="progressbar"
                         style="width:{{ max($pct, 3) }}%;background:{{ $barColor }};border-radius:99px;transition:width .4s ease;">
                    </div>
                </div>
            </div>
            {{-- Count + label --}}
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
            No active employees with task assignments yet.
        </div>
        @endforelse
    </div>
</div>

{{-- ── Recent tasks table ──────────────────────────────────────────────── --}}
<div class="card-fl">
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
        <span class="fw-semibold">Recent Tasks</span>
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Task</th><th>Assigned To</th><th>Priority</th><th>Due Date</th><th>Stage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTasks as $task)
                <tr>
                    <td>
                        <a href="{{ route('admin.tasks.show', $task->task_id) }}" class="fw-semibold text-decoration-none text-dark">
                            {{ $task->task_title }}
                        </a>
                    </td>
                    <td>
                        @foreach($task->assignments->take(2) as $a)
                            <span class="badge bg-light text-dark border me-1">{{ $a->employee?->full_name }}</span>
                        @endforeach
                        @if($task->assignments->count() > 2)
                            <span class="text-muted">+{{ $task->assignments->count() - 2 }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="priority-{{ strtolower($task->priority?->priority_name ?? 'low') }} fw-semibold">
                            {{ $task->priority?->priority_name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @if($task->due_date < now())
                            <span class="text-danger">{{ $task->due_date?->format('M j, Y') }}</span>
                        @else
                            {{ $task->due_date?->format('M j, Y') }}
                        @endif
                    </td>
                    <td>
                        @php $s = strtolower(str_replace(' ', '_', $task->stage?->stage_name ?? '')); @endphp
                        <span class="badge badge-stage-{{ $s }}">{{ $task->stage?->stage_name ?? '—' }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">No tasks yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const stageData = @json($tasksByStage);
new Chart(document.getElementById('stageChart'), {
    type: 'doughnut',
    data: {
        labels: stageData.labels,
        datasets: [{ data: stageData.data, backgroundColor: stageData.colors, borderWidth: 2 }]
    },
    options: {
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
        cutout: '62%'
    }
});

const weekly = @json($weeklyChart);
new Chart(document.getElementById('weeklyChart'), {
    type: 'line',
    data: {
        labels: weekly.labels,
        datasets: [{
            label: 'Completed',
            data: weekly.data,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79,70,229,.1)',
            fill: true, tension: .4, pointRadius: 4
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        },
        plugins: { legend: { display: false } }
    }
});
</script>
@endpush

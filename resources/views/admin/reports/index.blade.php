@extends('layouts.app')
@section('title','Reports')
@section('page-title','Performance Reports')

@push('styles')
<style>
    .stat-chip { display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;background:#fff;border:1px solid #e9ecef;border-radius:10px; }
    .stat-chip .icon { width:42px;height:42px;border-radius:8px;display:grid;place-items:center;flex-shrink:0; }
    .stat-chip .label { font-size:.72rem;color:#6b7280; }
    .stat-chip .value { font-size:1.3rem;font-weight:700;line-height:1.1; }
</style>
@endpush

@section('content')

{{-- ── Filters ──────────────────────────────────────────────────────────────── --}}
<form method="GET" class="card-fl p-3 mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label small fw-semibold mb-1">From</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label small fw-semibold mb-1">To</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label small fw-semibold mb-1">Department</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->department_id }}" {{ $deptId == $d->department_id ? 'selected':'' }}>{{ $d->department_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label small fw-semibold mb-1">Priority</label>
            <select name="priority_id" class="form-select form-select-sm">
                <option value="">All Priorities</option>
                @foreach($priorities as $p)
                    <option value="{{ $p->priority_id }}" {{ $priorityId == $p->priority_id ? 'selected':'' }}>{{ $p->priority_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label small fw-semibold mb-1">Stage</label>
            <select name="stage_id" class="form-select form-select-sm">
                <option value="">All Stages</option>
                @foreach($stages as $s)
                    <option value="{{ $s->stage_id }}" {{ $stageId == $s->stage_id ? 'selected':'' }}>{{ $s->stage_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label small fw-semibold mb-1">Employee</label>
            <select name="employee_id" class="form-select form-select-sm">
                <option value="">All Employees</option>
                @foreach($employees as $e)
                    <option value="{{ $e->employee_id }}" {{ $empId == $e->employee_id ? 'selected':'' }}>{{ $e->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel me-1"></i> Apply Filters
            </button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-light btn-sm">Reset</a>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format'=>'pdf'])) }}"
                   class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-pdf me-1"></i> Export PDF
                </a>
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format'=>'excel'])) }}"
                   class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-spreadsheet me-1"></i> Export CSV
                </a>
            </div>
        </div>
    </div>
</form>

{{-- ── KPI Cards ────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total Tasks',      $totalTasks,      '#4f46e5','#eef2ff','bi-kanban-fill'],
        ['Completed',        $completedTasks,  '#10b981','#d1fae5','bi-check-circle-fill'],
        ['Overdue',          $overdueTasks,    '#ef4444','#fee2e2','bi-exclamation-circle-fill'],
        ['Completion Rate',  $completionRate.'%', '#f59e0b','#fef3c7','bi-bar-chart-fill'],
        ['Present Today',    $attendanceSummary['Present'] ?? 0, '#0891b2','#ecfeff','bi-calendar-check-fill'],
    ] as [$lbl,$val,$color,$bg,$icon])
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-chip">
            <div class="icon" style="background:{{ $bg }}"><i class="bi {{ $icon }}" style="color:{{ $color }};font-size:1.1rem;"></i></div>
            <div><div class="value">{{ $val }}</div><div class="label">{{ $lbl }}</div></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Charts ───────────────────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card-fl p-3 h-100">
            <p class="fw-semibold small text-uppercase text-muted mb-3" style="letter-spacing:.06em;">Tasks by Stage</p>
            <canvas id="stageChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-fl p-3 h-100">
            <p class="fw-semibold small text-uppercase text-muted mb-3" style="letter-spacing:.06em;">Tasks by Priority</p>
            <canvas id="priorityChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-fl p-3 h-100">
            <p class="fw-semibold small text-uppercase text-muted mb-3" style="letter-spacing:.06em;">Attendance (Period)</p>
            <canvas id="attendanceChart" height="220"></canvas>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card-fl p-3">
            <p class="fw-semibold small text-uppercase text-muted mb-3" style="letter-spacing:.06em;">Completions — Last 30 Days</p>
            <canvas id="dailyChart" height="90"></canvas>
        </div>
    </div>
</div>

{{-- ── Employee Performance Table ───────────────────────────────────────────── --}}
<div class="card-fl mb-4">
    <div class="px-3 pt-3 pb-2 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-semibold small">Employee Performance</span>
        <span class="text-muted small">{{ $dateFrom }} — {{ $dateTo }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="text-center">Assigned</th>
                    <th class="text-center">Completed</th>
                    <th class="text-center">Overdue</th>
                    <th>Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employeePerformance as $row)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $row['employee']->full_name }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ $row['employee']->position?->position_name }}</div>
                    </td>
                    <td class="text-muted">{{ $row['employee']->department?->department_name ?? '—' }}</td>
                    <td class="text-center">{{ $row['assigned'] }}</td>
                    <td class="text-center text-success fw-semibold">{{ $row['completed'] }}</td>
                    <td class="text-center text-danger">{{ $row['overdue'] }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar {{ $row['completion_rate'] >= 80 ? 'bg-success' : ($row['completion_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width:{{ $row['completion_rate'] }}%">
                                </div>
                            </div>
                            <span class="fw-semibold" style="min-width:36px;text-align:right;">{{ $row['completion_rate'] }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Department Summary ───────────────────────────────────────────────────── --}}
<div class="card-fl">
    <div class="px-3 pt-3 pb-2 border-bottom fw-semibold small">Tasks by Department</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Department</th><th class="text-center">Total Tasks</th></tr>
            </thead>
            <tbody>
                @foreach($departments as $dept)
                <tr>
                    <td class="fw-semibold">{{ $dept->department_name }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary">{{ $dept->total_tasks }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Tasks by Stage
new Chart(document.getElementById('stageChart'), {
    type: 'doughnut',
    data: {
        labels: @json($stages->pluck('stage_name')),
        datasets: [{ data: @json($tasksByStage->values()), backgroundColor: ['#9ca3af','#3b82f6','#f59e0b','#10b981','#ef4444'], borderWidth: 2 }]
    },
    options: { plugins: { legend: { position:'bottom', labels:{ boxWidth:12,font:{size:11} } } }, cutout:'60%' }
});

// Tasks by Priority
new Chart(document.getElementById('priorityChart'), {
    type: 'bar',
    data: {
        labels: @json($priorities->pluck('priority_name')),
        datasets: [{ label: 'Tasks', data: @json($tasksByPriority->values()), backgroundColor: ['#10b981','#f59e0b','#ef4444'], borderRadius: 6 }]
    },
    options: { plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, ticks:{stepSize:1} } } }
});

// Attendance
const att = @json($attendanceSummary);
new Chart(document.getElementById('attendanceChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(att).length ? Object.keys(att) : ['No data'],
        datasets: [{ data: Object.keys(att).length ? Object.values(att) : [1], backgroundColor: ['#10b981','#ef4444','#f59e0b','#9ca3af'], borderWidth: 2 }]
    },
    options: { plugins:{ legend:{position:'bottom',labels:{boxWidth:12,font:{size:11}}} }, cutout:'60%' }
});

// Daily completions
const daily = @json($dailyChart);
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: daily.labels,
        datasets: [{
            label: 'Completed', data: daily.data,
            borderColor:'#4f46e5', backgroundColor:'rgba(79,70,229,.08)',
            fill:true, tension:.4, pointRadius:3
        }]
    },
    options: { scales:{ y:{ beginAtZero:true, ticks:{stepSize:1} } }, plugins:{ legend:{display:false} } }
});
</script>
@endpush

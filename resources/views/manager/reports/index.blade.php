@extends('layouts.app')
@section('title','Reports')
@section('page-title','My Task Reports')

@section('content')

<form method="GET" class="card-fl p-3 mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-6 col-md-3">
            <label class="form-label small fw-semibold mb-1">From</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small fw-semibold mb-1">To</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <div class="col-12 col-md-6 d-flex gap-2 align-items-end flex-wrap">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Apply</button>
            <a href="{{ route('manager.reports.index') }}" class="btn btn-light btn-sm">Reset</a>
            <a href="{{ route('manager.reports.export', array_merge(request()->query(), ['format'=>'pdf'])) }}"
               class="btn btn-sm btn-outline-danger ms-auto">
                <i class="bi bi-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('manager.reports.export', array_merge(request()->query(), ['format'=>'csv'])) }}"
               class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-spreadsheet me-1"></i> Export CSV
            </a>
        </div>
    </div>
</form>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total Tasks',     $totalTasks,     '#4f46e5','#eef2ff','bi-kanban-fill'],
        ['Completed',       $completedTasks, '#10b981','#d1fae5','bi-check-circle-fill'],
        ['Overdue',         $overdueTasks,   '#ef4444','#fee2e2','bi-exclamation-circle-fill'],
        ['Completion Rate', $completionRate.'%','#f59e0b','#fef3c7','bi-bar-chart-fill'],
    ] as [$l,$v,$c,$bg,$i])
    <div class="col-6 col-md-3">
        <div class="card-fl p-3 d-flex align-items-center gap-3">
            <div class="rounded-3" style="background:{{ $bg }};width:44px;height:44px;place-items:center;display:grid;">
                <i class="bi {{ $i }} fs-5" style="color:{{ $c }};"></i>
            </div>
            <div><div class="fw-bold fs-5 lh-1">{{ $v }}</div><div class="text-muted" style="font-size:.75rem;">{{ $l }}</div></div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card-fl p-3">
            <p class="fw-semibold small text-uppercase text-muted mb-3" style="letter-spacing:.06em;">Tasks by Stage</p>
            <canvas id="stageChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card-fl p-3">
            <p class="fw-semibold small text-uppercase text-muted mb-3" style="letter-spacing:.06em;">Tasks by Priority</p>
            <canvas id="priorityChart" height="180"></canvas>
        </div>
    </div>
</div>

{{-- Employee performance --}}
<div class="card-fl">
    <div class="px-3 pt-3 pb-2 border-bottom fw-semibold small">Assignee Performance</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Employee</th><th>Department</th><th class="text-center">Assigned</th><th class="text-center">Completed</th><th>Completion Rate</th></tr>
            </thead>
            <tbody>
                @forelse($employees as $row)
                <tr>
                    <td class="fw-semibold">{{ $row['employee']->full_name }}</td>
                    <td class="text-muted">{{ $row['employee']->department?->department_name ?? '—' }}</td>
                    <td class="text-center">{{ $row['assigned'] }}</td>
                    <td class="text-center text-success fw-semibold">{{ $row['completed'] }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar {{ $row['completion_rate'] >= 80 ? 'bg-success' : ($row['completion_rate'] >= 50 ? 'bg-warning':'bg-danger') }}"
                                     style="width:{{ $row['completion_rate'] }}%"></div>
                            </div>
                            <span class="fw-semibold" style="min-width:36px;text-align:right;">{{ $row['completion_rate'] }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">No data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const sc = @json($stageChart);
new Chart(document.getElementById('stageChart'), {
    type: 'doughnut',
    data: { labels: sc.labels, datasets: [{ data: sc.data, backgroundColor: sc.colors, borderWidth: 2 }] },
    options: { plugins:{ legend:{ position:'bottom', labels:{boxWidth:12,font:{size:11}} } }, cutout:'60%' }
});
new Chart(document.getElementById('priorityChart'), {
    type: 'bar',
    data: {
        labels: @json($priorities->pluck('priority_name')),
        datasets: [{ label: 'Tasks', data: @json($tasksByPriority->values()), backgroundColor: ['#10b981','#f59e0b','#ef4444'], borderRadius: 6 }]
    },
    options: { plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, ticks:{stepSize:1} } } }
});
</script>
@endpush

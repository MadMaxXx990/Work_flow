<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flowline Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; padding: 24px; }
        h1 { font-size: 18px; font-weight: bold; color: #4f46e5; margin-bottom: 2px; }
        .subtitle { font-size: 10px; color: #6b7280; margin-bottom: 20px; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
                         color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin: 18px 0 8px; }
        .kpi-grid { display: flex; gap: 10px; margin-bottom: 16px; }
        .kpi-box { flex: 1; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; text-align: center; }
        .kpi-box .num { font-size: 20px; font-weight: bold; color: #4f46e5; }
        .kpi-box .lbl { font-size: 9px; color: #6b7280; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        th { background: #f3f4f6; padding: 5px 8px; text-align: left; font-weight: 600; }
        td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 99px; font-size: 8px; font-weight: 600; }
        .badge-ok  { background: #d1fae5; color: #065f46; }
        .badge-bad { background: #fee2e2; color: #991b1b; }
        .badge-mid { background: #fef3c7; color: #92400e; }
        .rate-bar { display: inline-block; height: 6px; border-radius: 99px; background: #4f46e5; }
        footer { margin-top: 24px; font-size: 8px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>Flowline — Performance Report</h1>
    <div class="subtitle">Period: {{ $dateFrom }} — {{ $dateTo }} &nbsp;|&nbsp; Generated: {{ now()->format('M j, Y g:i a') }}</div>

    <div class="section-title">Summary</div>
    <div class="kpi-grid">
        <div class="kpi-box"><div class="num">{{ $totalTasks }}</div><div class="lbl">Total Tasks</div></div>
        <div class="kpi-box"><div class="num">{{ $completedTasks }}</div><div class="lbl">Completed</div></div>
        <div class="kpi-box"><div class="num">{{ $overdueTasks }}</div><div class="lbl">Overdue</div></div>
        <div class="kpi-box"><div class="num">{{ $completionRate }}%</div><div class="lbl">Completion Rate</div></div>
        <div class="kpi-box"><div class="num">{{ $attendanceSummary['Present'] ?? 0 }}</div><div class="lbl">Present Today</div></div>
    </div>

    <div class="section-title">Employee Performance</div>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Assigned</th>
                <th>Completed</th>
                <th>Overdue</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employeePerformance as $row)
            <tr>
                <td>{{ $row['employee']->full_name }}</td>
                <td>{{ $row['employee']->department?->department_name ?? '—' }}</td>
                <td>{{ $row['assigned'] }}</td>
                <td>{{ $row['completed'] }}</td>
                <td>{{ $row['overdue'] }}</td>
                <td>
                    @php $r = $row['completion_rate']; @endphp
                    <span class="badge {{ $r >= 80 ? 'badge-ok' : ($r >= 50 ? 'badge-mid' : 'badge-bad') }}">
                        {{ $r }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Tasks by Stage</div>
    <table>
        <thead><tr><th>Stage</th><th>Count</th></tr></thead>
        <tbody>
            @foreach($stages as $i => $stage)
            <tr><td>{{ $stage->stage_name }}</td><td>{{ $tasksByStage[$i] }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Tasks by Priority</div>
    <table>
        <thead><tr><th>Priority</th><th>Count</th></tr></thead>
        <tbody>
            @foreach($priorities as $i => $p)
            <tr><td>{{ $p->priority_name }}</td><td>{{ $tasksByPriority[$i] }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <footer>Flowline Employee Workflow System &nbsp;|&nbsp; Confidential</footer>
</body>
</html>

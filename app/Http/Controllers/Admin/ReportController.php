<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Employee;
use App\Models\WorkflowStage;
use App\Models\TaskPriority;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // ── Shared query builder ──────────────────────────────────────────────────
    private function buildReportData(Request $request): array
    {
        $dateFrom   = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo     = $request->get('date_to',   now()->toDateString());
        $deptId     = $request->get('department_id');
        $priorityId = $request->get('priority_id');
        $stageId    = $request->get('stage_id');
        $empId      = $request->get('employee_id');

        // ── Task stats ────────────────────────────────────────────────────────
        $taskQuery = Task::where('is_deleted', false)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($priorityId) $taskQuery->where('priority_id', $priorityId);
        if ($stageId)    $taskQuery->where('stage_id', $stageId);
        if ($deptId) {
            $taskQuery->whereHas('assignments.employee', fn($q) => $q->where('department_id', $deptId));
        }
        if ($empId) {
            $taskQuery->whereHas('assignments', fn($q) => $q->where('employee_id', $empId));
        }

        $totalTasks     = (clone $taskQuery)->count();
        $completedId    = WorkflowStage::where('stage_name', 'Completed')->value('stage_id');
        $completedTasks = (clone $taskQuery)->where('stage_id', $completedId)->count();
        $overdueTasks   = (clone $taskQuery)
            ->where('due_date', '<', now()->toDateString())
            ->where('stage_id', '!=', $completedId)
            ->count();

        $completionRate = $totalTasks > 0 ? round($completedTasks / $totalTasks * 100) : 0;

        // ── Tasks by stage (for chart) ────────────────────────────────────────
        $stages = WorkflowStage::orderBy('step_order')->get();
        $tasksByStage = $stages->map(fn($s) =>
            (clone $taskQuery)->where('stage_id', $s->stage_id)->count()
        );

        // ── Tasks by priority ─────────────────────────────────────────────────
        $priorities    = TaskPriority::all();
        $tasksByPriority = $priorities->map(fn($p) =>
            (clone $taskQuery)->where('priority_id', $p->priority_id)->count()
        );

        // ── Tasks completed per day (last 30 days) ────────────────────────────
        $last30 = collect(range(29, 0))->map(fn($d) => now()->subDays($d)->toDateString());
        $completedByDay = Task::where('is_deleted', false)
            ->where('stage_id', $completedId)
            ->whereBetween('updated_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(updated_at) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        $dailyChart = [
            'labels' => $last30->map(fn($d) => date('M j', strtotime($d)))->values(),
            'data'   => $last30->map(fn($d) => $completedByDay[$d] ?? 0)->values(),
        ];

        // ── Tasks by department ───────────────────────────────────────────────
        $departments = Department::where('is_deleted', false)->withCount([
            'employees as total_tasks' => function ($q) use ($dateFrom, $dateTo, $completedId, $priorityId, $stageId) {
                $q->join('task_assignments', 'employees.employee_id', '=', 'task_assignments.employee_id')
                  ->join('tasks', 'task_assignments.task_id', '=', 'tasks.task_id')
                  ->where('tasks.is_deleted', false)
                  ->whereBetween('tasks.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                  ->select(DB::raw('COUNT(DISTINCT tasks.task_id)'));
            }
        ])->get();

        // ── Employee performance table ────────────────────────────────────────
        $empQuery = Employee::with(['department', 'position'])
            ->where('is_deleted', false);
        if ($deptId) $empQuery->where('department_id', $deptId);

        $employeePerformance = $empQuery->get()->map(function ($emp) use ($dateFrom, $dateTo, $completedId) {
            $assignedIds = TaskAssignment::where('employee_id', $emp->employee_id)->pluck('task_id');
            $assigned    = Task::whereIn('task_id', $assignedIds)->where('is_deleted', false)
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();
            $completed   = Task::whereIn('task_id', $assignedIds)->where('is_deleted', false)
                ->where('stage_id', $completedId)
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();
            $overdue     = Task::whereIn('task_id', $assignedIds)->where('is_deleted', false)
                ->where('due_date', '<', now()->toDateString())
                ->where('stage_id', '!=', $completedId)
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();

            return [
                'employee'        => $emp,
                'assigned'        => $assigned,
                'completed'       => $completed,
                'overdue'         => $overdue,
                'completion_rate' => $assigned > 0 ? round($completed / $assigned * 100) : 0,
            ];
        })->sortByDesc('completion_rate')->values();

        // ── Attendance summary ────────────────────────────────────────────────
        $attendanceSummary = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->select('attendance_status', DB::raw('COUNT(*) as count'))
            ->groupBy('attendance_status')
            ->pluck('count', 'attendance_status');

        return compact(
            'dateFrom', 'dateTo', 'deptId', 'priorityId', 'stageId', 'empId',
            'totalTasks', 'completedTasks', 'overdueTasks', 'completionRate',
            'stages', 'tasksByStage', 'priorities', 'tasksByPriority',
            'dailyChart', 'departments', 'employeePerformance', 'attendanceSummary'
        );
    }

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $data        = $this->buildReportData($request);
        $departments = Department::where('is_deleted', false)->get();
        $priorities  = TaskPriority::all();
        $stages      = WorkflowStage::orderBy('step_order')->get();
        $employees   = Employee::where('is_deleted', false)->get();

        return view('admin.reports.index', array_merge($data, compact(
            'departments', 'priorities', 'stages', 'employees'
        )));
    }

    // ── Export ────────────────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $data   = $this->buildReportData($request);

        if ($format === 'excel') {
            return $this->exportExcel($data, $request);
        }

        return $this->exportPdf($data);
    }

    private function exportPdf(array $data)
    {
        $pdf = Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('flowline-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportExcel(array $data, Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReportExport($data),
            'flowline-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    private function exportCsv(array $data)
    {
        $filename = 'flowline-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Flowline — Performance Report', 'Generated: ' . now()->format('M j, Y')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Total Tasks', $data['totalTasks']]);
            fputcsv($handle, ['Completed',   $data['completedTasks']]);
            fputcsv($handle, ['Overdue',     $data['overdueTasks']]);
            fputcsv($handle, ['Completion Rate', $data['completionRate'] . '%']);
            fputcsv($handle, []);
            fputcsv($handle, ['Employee', 'Department', 'Assigned', 'Completed', 'Overdue', 'Rate %']);
            foreach ($data['employeePerformance'] as $row) {
                fputcsv($handle, [
                    $row['employee']->full_name,
                    $row['employee']->department?->department_name,
                    $row['assigned'],
                    $row['completed'],
                    $row['overdue'],
                    $row['completion_rate'],
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

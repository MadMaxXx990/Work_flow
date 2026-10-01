<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Employee;
use App\Models\WorkflowStage;
use App\Models\TaskPriority;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    private function buildData(Request $request): array
    {
        $userId   = Auth::id();
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->toDateString());

        $base = Task::where('created_by_user_id', $userId)
            ->where('is_deleted', false)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        $completedId    = WorkflowStage::where('stage_name', 'Completed')->value('stage_id');
        $totalTasks     = (clone $base)->count();
        $completedTasks = (clone $base)->where('stage_id', $completedId)->count();
        $overdueTasks   = (clone $base)->where('due_date', '<', now())->where('stage_id', '!=', $completedId)->count();
        $completionRate = $totalTasks > 0 ? round($completedTasks / $totalTasks * 100) : 0;

        $stages      = WorkflowStage::orderBy('step_order')->get();
        $tasksByStage = $stages->map(fn($s) => (clone $base)->where('stage_id', $s->stage_id)->count());

        $priorities      = TaskPriority::all();
        $tasksByPriority = $priorities->map(fn($p) => (clone $base)->where('priority_id', $p->priority_id)->count());

        $employees = Employee::where('is_deleted', false)->get()->map(function ($emp) use ($userId, $dateFrom, $dateTo, $completedId) {
            $ids       = TaskAssignment::where('employee_id', $emp->employee_id)->pluck('task_id');
            $assigned  = Task::whereIn('task_id', $ids)->where('created_by_user_id', $userId)
                ->where('is_deleted', false)->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])->count();
            $completed = Task::whereIn('task_id', $ids)->where('created_by_user_id', $userId)
                ->where('stage_id', $completedId)->where('is_deleted', false)->count();
            return [
                'employee'        => $emp,
                'assigned'        => $assigned,
                'completed'       => $completed,
                'completion_rate' => $assigned > 0 ? round($completed / $assigned * 100) : 0,
            ];
        })->filter(fn($r) => $r['assigned'] > 0)->sortByDesc('completion_rate')->values();

        $stageChart = [
            'labels' => $stages->pluck('stage_name'),
            'data'   => $tasksByStage->values(),
            'colors' => ['#9ca3af','#3b82f6','#f59e0b','#10b981','#ef4444'],
        ];

        return compact(
            'dateFrom','dateTo','totalTasks','completedTasks','overdueTasks','completionRate',
            'stages','tasksByStage','priorities','tasksByPriority','employees','stageChart'
        );
    }

    public function index(Request $request)
    {
        $data = $this->buildData($request);
        return view('manager.reports.index', $data);
    }

    public function export(Request $request)
    {
        $data   = $this->buildData($request);
        $format = $request->get('format', 'pdf');

        if ($format === 'csv') {
            return $this->exportCsv($data);
        }

        $pdf = Pdf::loadView('manager.reports.pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('my-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportCsv(array $data)
    {
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="report.csv"'];
        return response()->stream(function () use ($data) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Manager Report', now()->format('M j, Y')]);
            fputcsv($h, []);
            fputcsv($h, ['Total Tasks', $data['totalTasks'], 'Completed', $data['completedTasks'], 'Overdue', $data['overdueTasks']]);
            fputcsv($h, []);
            fputcsv($h, ['Employee','Assigned','Completed','Rate%']);
            foreach ($data['employees'] as $r) {
                fputcsv($h, [$r['employee']->full_name, $r['assigned'], $r['completed'], $r['completion_rate']]);
            }
            fclose($h);
        }, 200, $headers);
    }
}

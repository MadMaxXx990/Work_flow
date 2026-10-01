<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Task;
use App\Models\WorkflowStage;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Core counts ───────────────────────────────────────────────────────
        $totalEmployees  = Employee::where('is_deleted', false)->count();
        $totalTasks      = Task::where('is_deleted', false)->count();

        $stages = WorkflowStage::pluck('stage_name', 'stage_id');

        $pendingStageId    = WorkflowStage::where('stage_name', 'Pending')->value('stage_id');
        $inProgressStageId = WorkflowStage::where('stage_name', 'In Progress')->value('stage_id');
        $forReviewStageId  = WorkflowStage::where('stage_name', 'For Review')->value('stage_id');
        $completedStageId  = WorkflowStage::where('stage_name', 'Completed')->value('stage_id');

        $pendingCount    = Task::where('is_deleted', false)->where('stage_id', $pendingStageId)->count();
        $inProgressCount = Task::where('is_deleted', false)->where('stage_id', $inProgressStageId)->count();
        $forReviewCount  = Task::where('is_deleted', false)->where('stage_id', $forReviewStageId)->count();
        $completedCount  = Task::where('is_deleted', false)->where('stage_id', $completedStageId)->count();
        $overdueCount    = Task::where('is_deleted', false)
            ->where('due_date', '<', now()->toDateString())
            ->where('stage_id', '!=', $completedStageId)
            ->count();

        // ── Tasks per stage (for doughnut chart) ─────────────────────────────
        $tasksByStage = [
            'labels' => ['Pending', 'In Progress', 'For Review', 'Completed', 'Overdue'],
            'data'   => [$pendingCount, $inProgressCount, $forReviewCount, $completedCount, $overdueCount],
            'colors' => ['#9ca3af', '#3b82f6', '#f59e0b', '#10b981', '#ef4444'],
        ];

        // ── Tasks completed last 7 days (for line chart) ──────────────────────
        $last7 = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->toDateString());
        $completedByDay = Task::where('is_deleted', false)
            ->where('stage_id', $completedStageId)
            ->where('updated_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(updated_at) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        $weeklyChart = [
            'labels' => $last7->map(fn($d) => date('D', strtotime($d)))->values(),
            'data'   => $last7->map(fn($d) => $completedByDay[$d] ?? 0)->values(),
        ];

        // ── Today's attendance ────────────────────────────────────────────────
        $presentToday = Attendance::whereDate('date', today())
            ->where('attendance_status', 'Present')->count();

        // ── Recent 5 tasks ────────────────────────────────────────────────────
        $recentTasks = Task::with(['stage', 'priority', 'assignments.employee'])
            ->where('is_deleted', false)
            ->latest()
            ->take(5)
            ->get();

        // ── Team Workload ─────────────────────────────────────────────────────
        // All employees with at least one active (non-completed) task assigned.
        // Includes employees with zero active tasks too (full picture).
        $teamWorkload = Employee::select('employees.employee_id', 'employees.first_name', 'employees.last_name')
            ->addSelect(DB::raw('COUNT(DISTINCT CASE WHEN tasks.stage_id != ' . (int)$completedStageId . ' AND tasks.is_deleted = 0 THEN tasks.task_id END) as active_tasks'))
            ->addSelect(DB::raw('SUM(CASE WHEN tasks.due_date < CURDATE() AND tasks.stage_id != ' . (int)$completedStageId . ' AND tasks.is_deleted = 0 THEN 1 ELSE 0 END) as overdue_tasks'))
            ->leftJoin('task_assignments', 'employees.employee_id', '=', 'task_assignments.employee_id')
            ->leftJoin('tasks', 'task_assignments.task_id', '=', 'tasks.task_id')
            ->where('employees.is_deleted', false)
            ->where('employees.status', 'Active')
            ->groupBy('employees.employee_id', 'employees.first_name', 'employees.last_name')
            ->orderByDesc('active_tasks')
            ->get();

        $maxWorkload = $teamWorkload->max('active_tasks') ?: 1;

        return view('admin.dashboard', compact(
            'totalEmployees', 'totalTasks',
            'pendingCount', 'inProgressCount', 'forReviewCount', 'completedCount', 'overdueCount',
            'tasksByStage', 'weeklyChart', 'presentToday', 'recentTasks',
            'teamWorkload', 'maxWorkload'
        ));
    }
}

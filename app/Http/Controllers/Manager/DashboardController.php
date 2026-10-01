<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\WorkflowStage;
use App\Models\Employee;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $completedStageId = WorkflowStage::where('stage_name', 'Completed')->value('stage_id');
        $forReviewStageId = WorkflowStage::where('stage_name', 'For Review')->value('stage_id');

        $myTasks         = Task::where('created_by_user_id', $userId)->where('is_deleted', false);
        $totalMyTasks    = $myTasks->count();
        $pendingApproval = Task::where('created_by_user_id', $userId)
            ->where('stage_id', $forReviewStageId)
            ->where('is_deleted', false)
            ->count();
        $completedTasks  = Task::where('created_by_user_id', $userId)
            ->where('stage_id', $completedStageId)
            ->where('is_deleted', false)
            ->count();
        $overdueTasks    = Task::where('created_by_user_id', $userId)
            ->where('due_date', '<', now()->toDateString())
            ->where('stage_id', '!=', $completedStageId)
            ->where('is_deleted', false)
            ->count();

        $recentTasks = Task::with(['stage', 'priority', 'assignments.employee'])
            ->where('created_by_user_id', $userId)
            ->where('is_deleted', false)
            ->latest()
            ->take(6)
            ->get();

        $totalEmployees = Employee::where('is_deleted', false)->count();

        // ── Team Workload ─────────────────────────────────────────────────────
        // Active = any stage that is not Completed, on a task created by this manager.
        // Grouped by employee, sorted heaviest first.
        $teamWorkload = Employee::select('employees.employee_id', 'employees.first_name', 'employees.last_name')
            ->selectRaw('COUNT(DISTINCT tasks.task_id) as active_tasks')
            ->selectRaw('SUM(CASE WHEN tasks.due_date < CURDATE() THEN 1 ELSE 0 END) as overdue_tasks')
            ->join('task_assignments', 'employees.employee_id', '=', 'task_assignments.employee_id')
            ->join('tasks', 'task_assignments.task_id', '=', 'tasks.task_id')
            ->where('tasks.created_by_user_id', $userId)
            ->where('tasks.is_deleted', false)
            ->where('tasks.stage_id', '!=', $completedStageId)
            ->where('employees.is_deleted', false)
            ->groupBy('employees.employee_id', 'employees.first_name', 'employees.last_name')
            ->orderByDesc('active_tasks')
            ->get();

        // Max active tasks across the team — used to size the load bars
        $maxWorkload = $teamWorkload->max('active_tasks') ?: 1;

        return view('manager.dashboard', compact(
            'totalMyTasks', 'pendingApproval', 'completedTasks', 'overdueTasks',
            'recentTasks', 'totalEmployees',
            'teamWorkload', 'maxWorkload'
        ));
    }
}

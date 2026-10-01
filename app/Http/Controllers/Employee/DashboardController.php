<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\TaskAssignment;
use App\Models\Task;
use App\Models\WorkflowStage;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return view('employee.dashboard', ['assignedTasks' => collect(), 'stats' => []]);
        }

        $completedStageId = WorkflowStage::where('stage_name', 'Completed')->value('stage_id');

        $assignedTaskIds = TaskAssignment::where('employee_id', $employee->employee_id)
            ->pluck('task_id');

        $totalAssigned = $assignedTaskIds->count();
        $completed     = Task::whereIn('task_id', $assignedTaskIds)
            ->where('stage_id', $completedStageId)
            ->where('is_deleted', false)
            ->count();
        $overdue       = Task::whereIn('task_id', $assignedTaskIds)
            ->where('due_date', '<', now()->toDateString())
            ->where('stage_id', '!=', $completedStageId)
            ->where('is_deleted', false)
            ->count();

        // Today's attendance
        $todayAttendance = Attendance::where('employee_id', $employee->employee_id)
            ->whereDate('date', today())
            ->first();

        $recentTasks = Task::with(['stage', 'priority'])
            ->whereIn('task_id', $assignedTaskIds)
            ->where('is_deleted', false)
            ->latest()
            ->take(5)
            ->get();

        $stats = compact('totalAssigned', 'completed', 'overdue');

        return view('employee.dashboard', compact('stats', 'recentTasks', 'todayAttendance'));
    }
}

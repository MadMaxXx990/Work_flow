<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\WorkflowStage;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

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

        return view('manager.dashboard', compact(
            'totalMyTasks', 'pendingApproval', 'completedTasks', 'overdueTasks',
            'recentTasks', 'totalEmployees'
        ));
    }
}

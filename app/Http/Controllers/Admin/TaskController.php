<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\WorkflowStage;
use App\Models\TaskPriority;
use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\TaskComment;
use App\Models\Approval;
use App\Models\TaskStageHistory;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    // ── Index / Kanban board ──────────────────────────────────────────────────
    public function index(Request $request)
    {
        $stages     = WorkflowStage::orderBy('step_order')->get();
        $priorities = TaskPriority::all();
        $employees  = Employee::where('is_deleted', false)->get();

        $query = Task::with(['stage', 'priority', 'assignments.employee', 'updates'])
            ->where('is_deleted', false);

        if ($search = $request->get('search')) {
            $query->where('task_title', 'like', "%{$search}%");
        }
        if ($priorityId = $request->get('priority_id')) {
            $query->where('priority_id', $priorityId);
        }
        if ($employeeId = $request->get('employee_id')) {
            $query->whereHas('assignments', fn($q) => $q->where('employee_id', $employeeId));
        }

        $tasks = $query->latest()->get();

        // Group by stage + add overdue flag
        $completedId = WorkflowStage::where('stage_name', 'Completed')->value('stage_id');
        $tasksByStage = $stages->mapWithKeys(function ($stage) use ($tasks) {
            return [$stage->stage_name => $tasks->where('stage_id', $stage->stage_id)->values()];
        });

        // Overdue (past due_date, not completed)
        $overdueTasks = $tasks->filter(fn($t) =>
            $t->due_date < now()->toDateString() && $t->stage_id !== $completedId
        );

        return view('admin.tasks.index', compact(
            'stages', 'tasksByStage', 'overdueTasks',
            'priorities', 'employees'
        ));
    }

    // ── Create (Step 1 of wizard) ─────────────────────────────────────────────
    public function create()
    {
        $priorities = TaskPriority::all();
        $employees  = Employee::with('department')
            ->where('is_deleted', false)
            ->where('status', 'Active')
            ->get();

        return view('admin.tasks.create', compact('priorities', 'employees'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'task_title'       => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'priority_id'      => 'required|exists:task_priorities,priority_id',
            'start_date'       => 'nullable|date',
            'due_date'         => 'required|date|after_or_equal:today',
            'employee_ids'     => 'required|array|min:1',
            'employee_ids.*'   => 'exists:employees,employee_id',
            'attachments.*'    => 'nullable|file|max:10240',
        ]);

        DB::transaction(function () use ($request) {
            $pendingStage = WorkflowStage::where('stage_name', 'Pending')->firstOrFail();

            $task = Task::create([
                'stage_id'         => $pendingStage->stage_id,
                'task_title'       => $request->task_title,
                'task_description' => $request->task_description,
                'priority_id'      => $request->priority_id,
                'start_date'       => $request->start_date,
                'due_date'         => $request->due_date,
                'created_by_user_id' => Auth::id(),
            ]);

            // Log initial stage history
            TaskStageHistory::create([
                'task_id'           => $task->task_id,
                'changed_by_user_id'=> Auth::id(),
                'old_stage_id'      => null,
                'new_stage_id'      => $pendingStage->stage_id,
                'remarks'           => 'Task created.',
            ]);

            // Assign employees
            $notifyUserIds = [];
            foreach ($request->employee_ids as $empId) {
                TaskAssignment::create([
                    'task_id'            => $task->task_id,
                    'employee_id'        => $empId,
                    'assigned_by_user_id'=> Auth::id(),
                ]);
                $emp = Employee::find($empId);
                if ($emp?->user) {
                    $notifyUserIds[] = $emp->user->id;
                }
            }

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('task_attachments', 'public');
                    \App\Models\Attachment::create([
                        'task_id'            => $task->task_id,
                        'uploaded_by_user_id'=> Auth::id(),
                        'file_name'          => $file->getClientOriginalName(),
                        'file_path'          => $path,
                        'file_type'          => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            // Notify assigned employees
            if ($notifyUserIds) {
                NotificationService::send(
                    $notifyUserIds,
                    'assignment',
                    'New Task Assigned',
                    "You have been assigned to: \"{$task->task_title}\".",
                    $task->task_id
                );
            }

            ActivityLogService::log('assignment', "Created and assigned task: {$task->task_title}", 'tasks', $task->task_id);
        });

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created and assigned successfully.');
    }

    // ── Show / Detail ─────────────────────────────────────────────────────────
    public function show(Task $task)
    {
        abort_if($task->is_deleted, 404);

        $task->load([
            'stage', 'priority', 'creator',
            'assignments.employee.user',
            'updates.employee',
            'stageHistory.changedBy',
            'stageHistory.oldStage',
            'stageHistory.newStage',
            'comments' => fn($q) => $q->where('is_deleted', false)->with('user.employee'),
            'approvals.approver.employee',
            'attachments' => fn($q) => $q->where('is_deleted', false)->with('uploadedBy.employee'),
        ]);

        $stages    = WorkflowStage::orderBy('step_order')->get();
        $latestApproval = $task->approvals->last();

        return view('admin.tasks.show', compact('task', 'stages', 'latestApproval'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────
    public function edit(Task $task)
    {
        abort_if($task->is_deleted, 404);
        $priorities = TaskPriority::all();
        $employees  = Employee::where('is_deleted', false)->where('status', 'Active')->get();
        $assignedIds = $task->assignments->pluck('employee_id')->toArray();

        return view('admin.tasks.edit', compact('task', 'priorities', 'employees', 'assignedIds'));
    }

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, Task $task)
    {
        abort_if($task->is_deleted, 404);

        $request->validate([
            'task_title'       => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'priority_id'      => 'required|exists:task_priorities,priority_id',
            'start_date'       => 'nullable|date',
            'due_date'         => 'required|date',
            'employee_ids'     => 'required|array|min:1',
            'employee_ids.*'   => 'exists:employees,employee_id',
        ]);

        DB::transaction(function () use ($request, $task) {
            $task->update([
                'task_title'         => $request->task_title,
                'task_description'   => $request->task_description,
                'priority_id'        => $request->priority_id,
                'start_date'         => $request->start_date,
                'due_date'           => $request->due_date,
                'updated_by_user_id' => Auth::id(),
            ]);

            // Re-sync assignments
            $existing = $task->assignments->pluck('employee_id')->toArray();
            $new = array_map('intval', $request->employee_ids);
            $toAdd    = array_diff($new, $existing);
            $toRemove = array_diff($existing, $new);

            TaskAssignment::where('task_id', $task->task_id)
                ->whereIn('employee_id', $toRemove)
                ->delete();

            $notifyUserIds = [];
            foreach ($toAdd as $empId) {
                TaskAssignment::create([
                    'task_id'            => $task->task_id,
                    'employee_id'        => $empId,
                    'assigned_by_user_id'=> Auth::id(),
                ]);
                $emp = Employee::find($empId);
                if ($emp?->user) $notifyUserIds[] = $emp->user->id;
            }

            if ($notifyUserIds) {
                NotificationService::send($notifyUserIds, 'assignment',
                    'Task Assignment Updated',
                    "You have been added to task: \"{$task->task_title}\".",
                    $task->task_id);
            }

            ActivityLogService::log('update', "Updated task: {$task->task_title}", 'tasks', $task->task_id);
        });

        return redirect()->route('admin.tasks.show', $task->task_id)
            ->with('success', 'Task updated.');
    }

    // ── Soft Delete ───────────────────────────────────────────────────────────
    public function destroy(Task $task)
    {
        abort_if($task->is_deleted, 404);
        $task->update(['is_deleted' => true]);
        ActivityLogService::log('delete', "Deleted task: {$task->task_title}", 'tasks', $task->task_id);
        return redirect()->route('admin.tasks.index')->with('success', 'Task removed.');
    }

    // ── Change Stage ──────────────────────────────────────────────────────────
    public function changeStage(Request $request, Task $task)
    {
        $request->validate(['stage_id' => 'required|exists:workflow_stages,stage_id', 'remarks' => 'nullable|string']);

        $oldStageId = $task->stage_id;
        $task->update(['stage_id' => $request->stage_id, 'updated_by_user_id' => Auth::id()]);

        TaskStageHistory::create([
            'task_id'           => $task->task_id,
            'changed_by_user_id'=> Auth::id(),
            'old_stage_id'      => $oldStageId,
            'new_stage_id'      => $request->stage_id,
            'remarks'           => $request->remarks,
        ]);

        $newStage = WorkflowStage::find($request->stage_id);
        $notifyIds = $task->assignments->map(fn($a) => $a->employee?->user?->id)->filter()->values()->toArray();
        if ($notifyIds) {
            NotificationService::send($notifyIds, 'status',
                'Task Stage Updated',
                "Task \"{$task->task_title}\" moved to {$newStage->stage_name}.",
                $task->task_id);
        }

        ActivityLogService::log('update', "Changed stage of task {$task->task_id} to {$newStage->stage_name}", 'tasks', $task->task_id);

        return back()->with('success', "Stage changed to {$newStage->stage_name}.");
    }

    // ── Approve ───────────────────────────────────────────────────────────────
    public function approve(Request $request, Task $task)
    {
        $request->validate([
            'approval_status' => 'required|in:Approved,Revision Requested',
            'remarks'         => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $task) {
            Approval::create([
                'task_id'          => $task->task_id,
                'approver_user_id' => Auth::id(),
                'approval_status'  => $request->approval_status,
                'remarks'          => $request->remarks,
                'approval_date'    => now(),
            ]);

            if ($request->approval_status === 'Approved') {
                $completedStage = WorkflowStage::where('stage_name', 'Completed')->first();
                $oldStageId = $task->stage_id;
                $task->update(['stage_id' => $completedStage->stage_id, 'updated_by_user_id' => Auth::id()]);
                TaskStageHistory::create([
                    'task_id'           => $task->task_id,
                    'changed_by_user_id'=> Auth::id(),
                    'old_stage_id'      => $oldStageId,
                    'new_stage_id'      => $completedStage->stage_id,
                    'remarks'           => 'Approved by manager.',
                ]);
            } else {
                $inProgressStage = WorkflowStage::where('stage_name', 'In Progress')->first();
                $oldStageId = $task->stage_id;
                $task->update(['stage_id' => $inProgressStage->stage_id, 'updated_by_user_id' => Auth::id()]);
                TaskStageHistory::create([
                    'task_id'           => $task->task_id,
                    'changed_by_user_id'=> Auth::id(),
                    'old_stage_id'      => $oldStageId,
                    'new_stage_id'      => $inProgressStage->stage_id,
                    'remarks'           => 'Returned for revision.',
                ]);
            }

            $notifyIds = $task->assignments->map(fn($a) => $a->employee?->user?->id)->filter()->values()->toArray();
            if ($notifyIds) {
                NotificationService::send($notifyIds, 'approval',
                    'Task Review Decision',
                    "Task \"{$task->task_title}\": {$request->approval_status}." . ($request->remarks ? " Remarks: {$request->remarks}" : ''),
                    $task->task_id);
            }

            ActivityLogService::log('approval', "Reviewed task {$task->task_id}: {$request->approval_status}", 'tasks', $task->task_id);
        });

        return back()->with('success', "Task {$request->approval_status}.");
    }

    // ── Add Comment ───────────────────────────────────────────────────────────
    public function addComment(Request $request, Task $task)
    {
        $request->validate(['comment_text' => 'required|string|max:2000']);

        TaskComment::create([
            'task_id'      => $task->task_id,
            'user_id'      => Auth::id(),
            'comment_text' => $request->comment_text,
        ]);

        ActivityLogService::log('comment', "Commented on task {$task->task_id}", 'task_comments', $task->task_id);

        return back()->with('success', 'Comment posted.');
    }

    // ── Delete Comment ────────────────────────────────────────────────────────
    public function deleteComment(Task $task, TaskComment $comment)
    {
        abort_if($comment->task_id !== $task->task_id, 404);
        $comment->update(['is_deleted' => true]);
        return back()->with('success', 'Comment removed.');
    }
}

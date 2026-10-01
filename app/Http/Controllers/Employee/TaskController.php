<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskUpdate;
use App\Models\TaskComment;
use App\Models\TaskStageHistory;
use App\Models\WorkflowStage;
use App\Models\Attachment;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    private function employeeId(): int
    {
        return Auth::user()->employee?->employee_id ?? 0;
    }

    private function assignedTaskIds(): \Illuminate\Support\Collection
    {
        return TaskAssignment::where('employee_id', $this->employeeId())->pluck('task_id');
    }

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $ids    = $this->assignedTaskIds();
        $stages = WorkflowStage::orderBy('step_order')->get();

        $query = Task::with(['stage', 'priority'])
            ->whereIn('task_id', $ids)
            ->where('is_deleted', false);

        if ($s = $request->get('stage_id'))    $query->where('stage_id', $s);
        if ($p = $request->get('priority_id')) $query->where('priority_id', $p);

        $tasks = $query->latest()->get();

        $completedId  = WorkflowStage::where('stage_name', 'Completed')->value('stage_id');
        $tasksByStage = $stages->mapWithKeys(fn($stage) => [
            $stage->stage_name => $tasks->where('stage_id', $stage->stage_id)->values()
        ]);
        $overdueTasks = $tasks->filter(fn($t) =>
            $t->due_date < now()->toDateString() && $t->stage_id !== $completedId
        );

        return view('employee.tasks.index', compact('stages', 'tasksByStage', 'overdueTasks', 'tasks'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────
    public function show(Task $task)
    {
        abort_if($task->is_deleted, 404);
        abort_unless($this->assignedTaskIds()->contains($task->task_id), 403);

        $task->load([
            'stage', 'priority', 'creator.employee',
            'updates' => fn($q) => $q->latest('update_date'),
            'stageHistory.changedBy', 'stageHistory.oldStage', 'stageHistory.newStage',
            'comments' => fn($q) => $q->where('is_deleted', false)->with('user.employee'),
            'approvals',
            'attachments' => fn($q) => $q->where('is_deleted', false)->with('uploadedBy.employee'),
        ]);

        $latestUpdate   = $task->updates->first();
        $latestApproval = $task->approvals->last();

        return view('employee.tasks.show', compact('task', 'latestUpdate', 'latestApproval'));
    }

    // ── Post Progress Update ──────────────────────────────────────────────────
    public function postUpdate(Request $request, Task $task)
    {
        abort_unless($this->assignedTaskIds()->contains($task->task_id), 403);

        $request->validate([
            'update_message'  => 'required|string|max:2000',
            'progress_percent'=> 'required|integer|min:0|max:100',
        ]);

        TaskUpdate::create([
            'task_id'          => $task->task_id,
            'employee_id'      => $this->employeeId(),
            'update_message'   => $request->update_message,
            'progress_percent' => $request->progress_percent,
        ]);

        // Auto-move to In Progress if still Pending
        $pendingStage    = WorkflowStage::where('stage_name', 'Pending')->first();
        $inProgressStage = WorkflowStage::where('stage_name', 'In Progress')->first();
        if ($task->stage_id === $pendingStage->stage_id) {
            $task->update(['stage_id' => $inProgressStage->stage_id, 'updated_by_user_id' => Auth::id()]);
            TaskStageHistory::create([
                'task_id'           => $task->task_id,
                'changed_by_user_id'=> Auth::id(),
                'old_stage_id'      => $pendingStage->stage_id,
                'new_stage_id'      => $inProgressStage->stage_id,
                'remarks'           => 'Progress update posted.',
            ]);
        }

        ActivityLogService::log('update', "Posted progress update on task {$task->task_id}", 'task_updates', $task->task_id);

        return back()->with('success', 'Progress update saved.');
    }

    // ── Submit For Review ─────────────────────────────────────────────────────
    public function submitForReview(Request $request, Task $task)
    {
        abort_unless($this->assignedTaskIds()->contains($task->task_id), 403);

        $forReviewStage = WorkflowStage::where('stage_name', 'For Review')->firstOrFail();
        $oldStageId     = $task->stage_id;

        $task->update(['stage_id' => $forReviewStage->stage_id, 'updated_by_user_id' => Auth::id()]);
        TaskStageHistory::create([
            'task_id'           => $task->task_id,
            'changed_by_user_id'=> Auth::id(),
            'old_stage_id'      => $oldStageId,
            'new_stage_id'      => $forReviewStage->stage_id,
            'remarks'           => 'Submitted for review by employee.',
        ]);

        // Notify the task creator (manager)
        NotificationService::send(
            $task->created_by_user_id,
            'approval',
            'Task Ready for Review',
            "Task \"{$task->task_title}\" has been submitted for your review.",
            $task->task_id
        );

        ActivityLogService::log('update', "Submitted task {$task->task_id} for review", 'tasks', $task->task_id);

        return back()->with('success', 'Task submitted for review.');
    }

    // ── Add Comment ───────────────────────────────────────────────────────────
    public function addComment(Request $request, Task $task)
    {
        abort_unless($this->assignedTaskIds()->contains($task->task_id), 403);
        $request->validate(['comment_text' => 'required|string|max:2000']);

        TaskComment::create([
            'task_id'      => $task->task_id,
            'user_id'      => Auth::id(),
            'comment_text' => $request->comment_text,
        ]);

        return back()->with('success', 'Comment posted.');
    }

    // ── Upload Attachment ─────────────────────────────────────────────────────
    public function uploadAttachment(Request $request, Task $task)
    {
        abort_unless($this->assignedTaskIds()->contains($task->task_id), 403);
        $request->validate(['file' => 'required|file|max:10240']);

        $file = $request->file('file');
        $path = $file->store('task_attachments', 'public');

        Attachment::create([
            'task_id'            => $task->task_id,
            'uploaded_by_user_id'=> Auth::id(),
            'file_name'          => $file->getClientOriginalName(),
            'file_path'          => $path,
            'file_type'          => $file->getClientOriginalExtension(),
        ]);

        return back()->with('success', 'File uploaded.');
    }
}

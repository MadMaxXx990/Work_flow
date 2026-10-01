<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $primaryKey = 'task_id';

    protected $fillable = [
        'stage_id',
        'task_title',
        'task_description',
        'priority_id',
        'start_date',
        'due_date',
        'created_by_user_id',
        'updated_by_user_id',
        'is_deleted',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date'   => 'date',
        'is_deleted' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function stage()
    {
        return $this->belongsTo(WorkflowStage::class, 'stage_id', 'stage_id');
    }

    public function priority()
    {
        return $this->belongsTo(TaskPriority::class, 'priority_id', 'priority_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_id', 'task_id');
    }

    public function assignedEmployees()
    {
        return $this->hasManyThrough(
            Employee::class,
            TaskAssignment::class,
            'task_id',       // FK on task_assignments
            'employee_id',   // FK on employees
            'task_id',       // local key on tasks
            'employee_id'    // local key on task_assignments
        );
    }

    public function updates()
    {
        return $this->hasMany(TaskUpdate::class, 'task_id', 'task_id');
    }

    public function stageHistory()
    {
        return $this->hasMany(TaskStageHistory::class, 'task_id', 'task_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id', 'task_id')
                    ->where('is_deleted', false);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'task_id', 'task_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'task_id', 'task_id')
                    ->where('is_deleted', false);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Returns true if past due and not completed. */
    public function isOverdue(): bool
    {
        return $this->due_date < now()->toDateString()
            && $this->stage?->stage_name !== 'Completed';
    }

    /** Latest progress percent from task_updates. */
    public function latestProgress(): int
    {
        return $this->updates()->latest('update_date')->value('progress_percent') ?? 0;
    }
}

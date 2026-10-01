<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStageHistory extends Model
{
    protected $table = 'task_stage_history';

    protected $primaryKey = 'history_id';

    protected $fillable = [
        'task_id',
        'changed_by_user_id',
        'old_stage_id',
        'new_stage_id',
        'remarks',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function oldStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'old_stage_id', 'stage_id');
    }

    public function newStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'new_stage_id', 'stage_id');
    }
}

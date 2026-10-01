<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStage extends Model
{
    protected $primaryKey = 'stage_id';

    protected $fillable = [
        'stage_name',
        'step_order',
        'is_terminal',
    ];

    protected $casts = [
        'is_terminal' => 'boolean',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'stage_id', 'stage_id');
    }
}

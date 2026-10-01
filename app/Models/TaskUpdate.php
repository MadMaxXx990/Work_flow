<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUpdate extends Model
{
    protected $primaryKey = 'update_id';

    protected $fillable = [
        'task_id',
        'employee_id',
        'update_message',
        'progress_percent',
        'update_date',
    ];

    protected $casts = [
        'update_date' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}

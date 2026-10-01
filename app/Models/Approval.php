<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $primaryKey = 'approval_id';

    protected $fillable = [
        'task_id',
        'approver_user_id',
        'approval_status',
        'remarks',
        'requested_at',
        'approval_date',
    ];

    protected $casts = [
        'requested_at'  => 'datetime',
        'approval_date' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}

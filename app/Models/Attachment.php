<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $primaryKey = 'attachment_id';

    protected $fillable = [
        'task_id',
        'uploaded_by_user_id',
        'file_name',
        'file_path',
        'file_type',
        'is_deleted',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'is_deleted'  => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}

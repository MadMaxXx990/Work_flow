<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskPriority extends Model
{
    protected $primaryKey = 'priority_id';

    protected $fillable = ['priority_name'];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'priority_id', 'priority_id');
    }
}

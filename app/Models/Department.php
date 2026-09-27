<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $primaryKey = 'department_id';
    protected $fillable = ['department_name', 'description', 'is_deleted'];

    public function employees() {
        return $this->hasMany(Employee::class, 'department_id', 'department_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $primaryKey = 'employee_id';

    protected $fillable = [
        'department_id',
        'position_id',
        'supervisor_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'employment_type',
        'emergency_contact',
        'profile_photo_url',
        'hire_date',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'hire_date'  => 'date',
        'is_deleted' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id', 'employee_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id', 'employee_id');
    }

    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'employee_id', 'employee_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'employee_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}

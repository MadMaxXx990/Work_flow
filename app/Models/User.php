<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'employee_id',
        'role_id',
        'username',
        'password',
        'status',
        'reset_token',
        'failed_login_attempts',
        'last_login',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'reset_token',
    ];

    protected $casts = [
        'last_login'  => 'datetime',
        'is_deleted'  => 'boolean',
    ];

    // Laravel uses this to find the user during Auth::attempt — keep as 'username'
    // but do NOT override getAuthIdentifier() so Auth::id() still returns the integer id
    public function getAuthPassword(): string
    {
        return $this->password;
    }
    // ── Relationships ─────────────────────────────────────────────────────────

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasRole(string $roleName): bool
    {
        return $this->role?->role_name === $roleName;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Administrator');
    }

    public function isManager(): bool
    {
        return $this->hasRole('Manager');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('Employee');
    }

    public function unreadNotificationCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }
}

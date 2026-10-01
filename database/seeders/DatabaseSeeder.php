<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkflowStage;
use App\Models\TaskPriority;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Roles ──────────────────────────────────────────────────────────
        $adminRole = Role::create(['role_name' => 'Administrator', 'description' => 'Full system access']);
        $managerRole = Role::create(['role_name' => 'Manager',       'description' => 'Task creation and approval']);
        Role::create(['role_name' => 'Employee', 'description' => 'Task execution and updates']);

        // ── 2. Workflow Stages (in order) ─────────────────────────────────────
        WorkflowStage::create(['stage_name' => 'Pending',     'step_order' => 1, 'is_terminal' => false]);
        WorkflowStage::create(['stage_name' => 'In Progress', 'step_order' => 2, 'is_terminal' => false]);
        WorkflowStage::create(['stage_name' => 'For Review',  'step_order' => 3, 'is_terminal' => false]);
        WorkflowStage::create(['stage_name' => 'Completed',   'step_order' => 4, 'is_terminal' => true]);
        WorkflowStage::create(['stage_name' => 'Overdue',     'step_order' => 5, 'is_terminal' => false]);

        // ── 3. Task Priorities ────────────────────────────────────────────────
        TaskPriority::create(['priority_name' => 'Low']);
        TaskPriority::create(['priority_name' => 'Medium']);
        TaskPriority::create(['priority_name' => 'High']);

        // ── 4. Default Departments & Positions ───────────────────────────────
        $itDept   = Department::create(['department_name' => 'IT',          'description' => 'Information Technology']);
        $hrDept   = Department::create(['department_name' => 'HR',          'description' => 'Human Resources']);
        $opsDept  = Department::create(['department_name' => 'Operations',  'description' => 'Operations and Logistics']);

        $adminPos   = Position::create(['position_name' => 'System Administrator']);
        $managerPos = Position::create(['position_name' => 'Department Manager']);
        $staffPos   = Position::create(['position_name' => 'Staff']);
        $analystPos = Position::create(['position_name' => 'Analyst']);

        // ── 5. Default Admin Account ──────────────────────────────────────────
        $adminEmployee = Employee::create([
            'first_name'    => 'System',
            'last_name'     => 'Admin',
            'email'         => 'admin@flowline.local',
            'department_id' => $itDept->department_id,
            'position_id'   => $adminPos->position_id,
            'status'        => 'Active',
        ]);

        User::create([
            'employee_id' => $adminEmployee->employee_id,
            'role_id'     => $adminRole->role_id,
            'username'    => 'admin',
            'password'    => Hash::make('password123'),
            'status'      => 'active',
        ]);

        // ── 6. Sample Manager Account ─────────────────────────────────────────
        $managerEmployee = Employee::create([
            'first_name'    => 'Maria',
            'last_name'     => 'Cruz',
            'email'         => 'manager@flowline.local',
            'department_id' => $hrDept->department_id,
            'position_id'   => $managerPos->position_id,
            'status'        => 'Active',
        ]);

        User::create([
            'employee_id' => $managerEmployee->employee_id,
            'role_id'     => $managerRole->role_id,
            'username'    => 'manager',
            'password'    => Hash::make('password123'),
            'status'      => 'active',
        ]);
    }
}

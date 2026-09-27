<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles
        $adminRoleId = DB::table('roles')->insertGetId(['role_name' => 'Administrator', 'created_at' => now()]);
        $managerRoleId = DB::table('roles')->insertGetId(['role_name' => 'Manager', 'created_at' => now()]);
        $employeeRoleId = DB::table('roles')->insertGetId(['role_name' => 'Employee', 'created_at' => now()]);

        // 2. Seed Predefined Task Priorities
        DB::table('task_priorities')->insert([
            ['priority_name' => 'Low', 'created_at' => now()],
            ['priority_name' => 'Medium', 'created_at' => now()],
            ['priority_name' => 'High', 'created_at' => now()],
        ]);

        // 3. Seed Predefined Workflow Stages
        DB::table('workflow_stages')->insert([
            ['stage_name' => 'Pending', 'step_order' => 1, 'is_terminal' => false, 'created_at' => now()],
            ['stage_name' => 'In Progress', 'step_order' => 2, 'is_terminal' => false, 'created_at' => now()],
            ['stage_name' => 'For Review', 'step_order' => 3, 'is_terminal' => false, 'created_at' => now()],
            ['stage_name' => 'Completed', 'step_order' => 4, 'is_terminal' => true, 'created_at' => now()],
        ]);

        // 4. Seed Departments & Positions
        $deptId = DB::table('departments')->insertGetId(['department_name' => 'IT', 'created_at' => now()]);
        $posId = DB::table('positions')->insertGetId(['position_name' => 'System Administrator', 'created_at' => now()]);

        // 5. Seed Initial Admin Account
        $employeeId = DB::table('employees')->insertGetId([
            'department_id' => $deptId,
            'position_id' => $posId,
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'admin@company.com',
            'status' => 'Active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'employee_id' => $employeeId,
            'role_id' => $adminRoleId,
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'created_at' => now()
        ]);
    }
}
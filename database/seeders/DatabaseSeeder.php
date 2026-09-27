<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\Employee;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles
        $adminRole = Role::create(['role_name' => 'Administrator', 'description' => 'System Admin']);
        Role::create(['role_name' => 'Manager', 'description' => 'Department Manager']);
        Role::create(['role_name' => 'Employee', 'description' => 'Staff Member']);

        // 2. Seed Initial Department & Position
        $dept = Department::create(['department_name' => 'IT', 'description' => 'Information Technology']);
        $pos = Position::create(['position_name' => 'System Administrator', 'description' => 'Admin Role']);

        // 3. Seed Admin Employee Record
        $employee = Employee::create([
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'admin@flowline.local',
            'department_id' => $dept->department_id,
            'position_id' => $pos->position_id,
            'status' => 'Active',
        ]);

        // 4. Seed Admin Login User
        User::create([
            'employee_id' => $employee->employee_id,
            'role_id' => $adminRole->role_id,
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
    }
}
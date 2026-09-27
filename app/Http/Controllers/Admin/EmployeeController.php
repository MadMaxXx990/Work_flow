<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index() {
        $employees = Employee::with(['department', 'position', 'user.role'])
            ->where('is_deleted', false)
            ->get();

        return view('admin.employees.index', compact('employees'));
    }

    public function create() {
        $departments = Department::where('is_deleted', false)->get();
        $positions = Position::all();
        $roles = Role::all();

        return view('admin.employees.create', compact('departments', 'positions', 'roles'));
    }

    public function store(Request $request) {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'department_id' => 'required|exists:departments,department_id',
            'position_id' => 'required|exists:positions,position_id',
            'role_id' => 'required|exists:roles,role_id',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        DB::transaction(function () use ($request) {
            $employee = Employee::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'status' => 'Active',
            ]);

            User::create([
                'employee_id' => $employee->employee_id,
                'role_id' => $request->role_id,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position', 'user.role'])
            ->where('is_deleted', false);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($deptId = $request->get('department_id')) {
            $query->where('department_id', $deptId);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $employees   = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::where('is_deleted', false)->get();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    // ── Create ────────────────────────────────────────────────────────────────
    public function create()
    {
        $departments = Department::where('is_deleted', false)->get();
        $positions   = Position::all();
        $roles       = Role::all();
        $supervisors = Employee::with('user')
            ->where('is_deleted', false)
            ->get();

        return view('admin.employees.create', compact('departments', 'positions', 'roles', 'supervisors'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:employees,email',
            'phone'           => 'nullable|string|max:30',
            'department_id'   => 'required|exists:departments,department_id',
            'position_id'     => 'required|exists:positions,position_id',
            'supervisor_id'   => 'nullable|exists:employees,employee_id',
            'employment_type' => 'nullable|string|max:100',
            'hire_date'       => 'nullable|date',
            'role_id'         => 'required|exists:roles,role_id',
            'username'        => 'required|string|unique:users,username|max:60',
            'password'        => 'required|string|min:6',
            'profile_photo'   => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('profile_photos', 'public');
            }

            $employee = Employee::create([
                'first_name'      => $request->first_name,
                'last_name'       => $request->last_name,
                'email'           => $request->email,
                'phone'           => $request->phone,
                'department_id'   => $request->department_id,
                'position_id'     => $request->position_id,
                'supervisor_id'   => $request->supervisor_id ?: null,
                'employment_type' => $request->employment_type,
                'hire_date'       => $request->hire_date,
                'status'          => 'Active',
                'profile_photo_url' => $photoPath,
            ]);

            User::create([
                'employee_id' => $employee->employee_id,
                'role_id'     => $request->role_id,
                'username'    => $request->username,
                'password'    => Hash::make($request->password),
                'status'      => 'active',
            ]);

            ActivityLogService::log(
                'create',
                "Created employee: {$employee->first_name} {$employee->last_name}",
                'employees',
                $employee->employee_id
            );
        });

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee account created successfully.');
    }

    // ── Show ──────────────────────────────────────────────────────────────────
    public function show(Employee $employee)
    {
        abort_if($employee->is_deleted, 404);

        $employee->load([
            'department', 'position', 'user.role', 'supervisor',
            'taskAssignments.task.stage',
            'taskAssignments.task.priority',
            'attendance' => fn($q) => $q->latest('date')->take(10),
        ]);

        // Task stats
        $totalTasks     = $employee->taskAssignments->count();
        $completedTasks = $employee->taskAssignments
            ->filter(fn($a) => $a->task?->stage?->stage_name === 'Completed')
            ->count();

        return view('admin.employees.show', compact('employee', 'totalTasks', 'completedTasks'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────
    public function edit(Employee $employee)
    {
        abort_if($employee->is_deleted, 404);
        $employee->load('user');

        $departments = Department::where('is_deleted', false)->get();
        $positions   = Position::all();
        $roles       = Role::all();
        $supervisors = Employee::where('is_deleted', false)
            ->where('employee_id', '!=', $employee->employee_id)
            ->get();

        return view('admin.employees.edit', compact('employee', 'departments', 'positions', 'roles', 'supervisors'));
    }

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, Employee $employee)
    {
        abort_if($employee->is_deleted, 404);

        $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => "required|email|unique:employees,email,{$employee->employee_id},employee_id",
            'phone'           => 'nullable|string|max:30',
            'department_id'   => 'required|exists:departments,department_id',
            'position_id'     => 'required|exists:positions,position_id',
            'supervisor_id'   => 'nullable|exists:employees,employee_id',
            'employment_type' => 'nullable|string|max:100',
            'hire_date'       => 'nullable|date',
            'status'          => 'required|in:Active,On Leave,Inactive',
            'role_id'         => 'required|exists:roles,role_id',
            'username'        => "required|string|max:60|unique:users,username,{$employee->user?->id}",
            'password'        => 'nullable|string|min:6',
            'profile_photo'   => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request, $employee) {
            $photoPath = $employee->profile_photo_url;
            if ($request->hasFile('profile_photo')) {
                if ($photoPath) Storage::disk('public')->delete($photoPath);
                $photoPath = $request->file('profile_photo')->store('profile_photos', 'public');
            }

            $employee->update([
                'first_name'       => $request->first_name,
                'last_name'        => $request->last_name,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'department_id'    => $request->department_id,
                'position_id'      => $request->position_id,
                'supervisor_id'    => $request->supervisor_id ?: null,
                'employment_type'  => $request->employment_type,
                'hire_date'        => $request->hire_date,
                'status'           => $request->status,
                'profile_photo_url'=> $photoPath,
            ]);

            // Update associated user account
            $userUpdates = ['role_id' => $request->role_id, 'username' => $request->username];
            if ($request->filled('password')) {
                $userUpdates['password'] = Hash::make($request->password);
            }
            $employee->user?->update($userUpdates);

            ActivityLogService::log(
                'update',
                "Updated employee: {$employee->first_name} {$employee->last_name}",
                'employees',
                $employee->employee_id
            );
        });

        return redirect()->route('admin.employees.show', $employee->employee_id)
            ->with('success', 'Employee record updated.');
    }

    // ── Soft Delete ───────────────────────────────────────────────────────────
    public function destroy(Employee $employee)
    {
        abort_if($employee->is_deleted, 404);

        DB::transaction(function () use ($employee) {
            $employee->update(['is_deleted' => true, 'status' => 'Inactive']);
            $employee->user?->update(['is_deleted' => true, 'status' => 'inactive']);

            ActivityLogService::log(
                'delete',
                "Deactivated employee: {$employee->first_name} {$employee->last_name}",
                'employees',
                $employee->employee_id
            );
        });

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee account deactivated.');
    }
}

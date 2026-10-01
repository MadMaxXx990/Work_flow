<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['employees' => fn($q) => $q->where('is_deleted', false)])
            ->where('is_deleted', false)
            ->latest()
            ->get();

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name',
            'description'     => 'nullable|string|max:500',
        ]);

        $dept = Department::create($data);

        ActivityLogService::log('create', "Created department: {$dept->department_name}", 'departments', $dept->department_id);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department \"{$dept->department_name}\" created.");
    }

    public function edit(Department $department)
    {
        abort_if($department->is_deleted, 404);
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        abort_if($department->is_deleted, 404);

        $data = $request->validate([
            'department_name' => "required|string|max:255|unique:departments,department_name,{$department->department_id},department_id",
            'description'     => 'nullable|string|max:500',
        ]);

        $department->update($data);

        ActivityLogService::log('update', "Updated department: {$department->department_name}", 'departments', $department->department_id);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department \"{$department->department_name}\" updated.");
    }

    public function destroy(Department $department)
    {
        abort_if($department->is_deleted, 404);

        $department->update(['is_deleted' => true]);

        ActivityLogService::log('delete', "Soft-deleted department: {$department->department_name}", 'departments', $department->department_id);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department \"{$department->department_name}\" removed.");
    }
}

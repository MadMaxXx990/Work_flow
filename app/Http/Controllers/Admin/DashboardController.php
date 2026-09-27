<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeCount = Employee::count();
        $activeTasks = Task::where('status', '!=', 'completed')->count();

        return view('admin.dashboard', compact('employeeCount', 'activeTasks'));
    }
}
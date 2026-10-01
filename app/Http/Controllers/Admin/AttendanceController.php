<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::where('is_deleted', false)->orderBy('first_name')->get();

        $query = Attendance::with('employee.department')
            ->orderByDesc('date');

        if ($empId = $request->get('employee_id')) $query->where('employee_id', $empId);
        if ($from  = $request->get('from'))        $query->whereDate('date', '>=', $from);
        if ($to    = $request->get('to'))          $query->whereDate('date', '<=', $to);
        if ($stat  = $request->get('attendance_status')) $query->where('attendance_status', $stat);

        $records = $query->paginate(25)->withQueryString();

        // Summary for today
        $todayPresent = Attendance::whereDate('date', today())->where('attendance_status', 'Present')->count();
        $todayAbsent  = Attendance::whereDate('date', today())->where('attendance_status', 'Absent')->count();

        return view('admin.attendance.index', compact('records', 'employees', 'todayPresent', 'todayAbsent'));
    }

    public function create()
    {
        $employees = Employee::where('is_deleted', false)->where('status', 'Active')->orderBy('first_name')->get();
        return view('admin.attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'       => 'required|exists:employees,employee_id',
            'date'              => 'required|date',
            'time_in'           => 'nullable|date_format:H:i',
            'time_out'          => 'nullable|date_format:H:i|after:time_in',
            'attendance_status' => 'required|in:Present,Absent,On Leave',
        ]);

        // Prevent duplicate entries for same employee + date
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('date', $request->date)->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'An attendance record already exists for this employee on that date.'])->withInput();
        }

        $record = Attendance::create($request->only('employee_id','date','time_in','time_out','attendance_status'));

        ActivityLogService::log('create', "Logged attendance for employee {$record->employee_id}", 'attendance', $record->attendance_id);

        return redirect()->route('admin.attendance.index')->with('success', 'Attendance record saved.');
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::where('is_deleted', false)->orderBy('first_name')->get();
        return view('admin.attendance.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'time_in'           => 'nullable|date_format:H:i',
            'time_out'          => 'nullable|date_format:H:i',
            'attendance_status' => 'required|in:Present,Absent,On Leave',
        ]);

        $attendance->update($request->only('time_in','time_out','attendance_status'));

        ActivityLogService::log('update', "Updated attendance record {$attendance->attendance_id}", 'attendance', $attendance->attendance_id);

        return redirect()->route('admin.attendance.index')->with('success', 'Record updated.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        ActivityLogService::log('delete', "Deleted attendance record {$attendance->attendance_id}", 'attendance', $attendance->attendance_id);
        return redirect()->route('admin.attendance.index')->with('success', 'Record deleted.');
    }
}

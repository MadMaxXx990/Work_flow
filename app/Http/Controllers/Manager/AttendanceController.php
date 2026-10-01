<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::where('is_deleted', false)->orderBy('first_name')->get();

        $query = Attendance::with('employee')->orderByDesc('date');

        if ($empId = $request->get('employee_id')) $query->where('employee_id', $empId);
        if ($from  = $request->get('from'))        $query->whereDate('date', '>=', $from);
        if ($to    = $request->get('to'))          $query->whereDate('date', '<=', $to);

        $records      = $query->paginate(25)->withQueryString();
        $todayPresent = Attendance::whereDate('date', today())->where('attendance_status', 'Present')->count();
        $todayAbsent  = Attendance::whereDate('date', today())->where('attendance_status', 'Absent')->count();

        return view('manager.attendance.index', compact('records', 'employees', 'todayPresent', 'todayAbsent'));
    }

    // Manager can create/edit attendance records too (read-write access)
    public function create()
    {
        $employees = Employee::where('is_deleted', false)->where('status', 'Active')->orderBy('first_name')->get();
        return view('manager.attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'       => 'required|exists:employees,employee_id',
            'date'              => 'required|date',
            'time_in'           => 'nullable|date_format:H:i',
            'time_out'          => 'nullable|date_format:H:i',
            'attendance_status' => 'required|in:Present,Absent,On Leave',
        ]);

        $exists = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('date', $request->date)->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'Record already exists for this employee on that date.'])->withInput();
        }

        Attendance::create($request->only('employee_id','date','time_in','time_out','attendance_status'));

        return redirect()->route('manager.attendance.index')->with('success', 'Attendance record saved.');
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::where('is_deleted', false)->orderBy('first_name')->get();
        return view('manager.attendance.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'time_in'           => 'nullable|date_format:H:i',
            'time_out'          => 'nullable|date_format:H:i',
            'attendance_status' => 'required|in:Present,Absent,On Leave',
        ]);
        $attendance->update($request->only('time_in','time_out','attendance_status'));
        return redirect()->route('manager.attendance.index')->with('success', 'Record updated.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('manager.attendance.index')->with('success', 'Record deleted.');
    }
}

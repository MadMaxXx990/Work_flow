<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    private function employee()
    {
        return Auth::user()->employee;
    }

    public function index()
    {
        $emp = $this->employee();
        if (!$emp) return view('employee.attendance.index', ['records' => collect(), 'todayRecord' => null]);

        $records     = Attendance::where('employee_id', $emp->employee_id)
            ->orderByDesc('date')->paginate(20);
        $todayRecord = Attendance::where('employee_id', $emp->employee_id)
            ->whereDate('date', today())->first();

        return view('employee.attendance.index', compact('records', 'todayRecord'));
    }

    public function timeIn(Request $request)
    {
        $emp = $this->employee();
        abort_unless($emp, 403);

        $existing = Attendance::where('employee_id', $emp->employee_id)->whereDate('date', today())->first();

        if ($existing) {
            return back()->with('error', 'You have already timed in today.');
        }

        Attendance::create([
            'employee_id'       => $emp->employee_id,
            'date'              => today(),
            'time_in'           => now()->format('H:i'),
            'attendance_status' => 'Present',
        ]);

        return back()->with('success', 'Time-in recorded at ' . now()->format('g:i a') . '.');
    }

    public function timeOut(Request $request)
    {
        $emp = $this->employee();
        abort_unless($emp, 403);

        $record = Attendance::where('employee_id', $emp->employee_id)->whereDate('date', today())->first();

        if (!$record) {
            return back()->with('error', 'No time-in record found for today.');
        }

        if ($record->time_out) {
            return back()->with('error', 'You have already timed out today.');
        }

        $record->update(['time_out' => now()->format('H:i')]);

        return back()->with('success', 'Time-out recorded at ' . now()->format('g:i a') . '.');
    }
}

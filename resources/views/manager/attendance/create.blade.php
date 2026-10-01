@extends('layouts.app')
@section('title', 'Log Attendance')
@section('page-title', 'Log Attendance')

@section('content')
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card-fl p-4">
    <h5 class="fw-bold mb-4">New Attendance Record</h5>
    <form action="{{ route('manager.attendance.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-semibold">Employee <span class="text-danger">*</span></label>
            <select name="employee_id" class="form-select" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->employee_id }}">{{ $emp->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-semibold">Date <span class="text-danger">*</span></label>
            <input type="date" name="date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Time In</label>
                <input type="time" name="time_in" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Time Out</label>
                <input type="time" name="time_out" class="form-control">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold">Status</label>
            <select name="attendance_status" class="form-select">
                @foreach(['Present','Absent','On Leave'] as $s)
                    <option value="{{ $s }}" {{ $s === 'Present' ? 'selected':'' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('manager.attendance.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Record</button>
        </div>
    </form>
</div>
</div>
</div>
@endsection

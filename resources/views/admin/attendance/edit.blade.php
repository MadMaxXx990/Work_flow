@extends('layouts.app')
@section('title', 'Edit Attendance')
@section('page-title', 'Edit Attendance')

@section('content')
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card-fl p-4">
    <h5 class="fw-bold mb-4">Edit Attendance — {{ $attendance->employee?->full_name }} ({{ $attendance->date?->format('M j, Y') }})</h5>
    <form action="{{ route('admin.attendance.update', $attendance->attendance_id) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Time In</label>
                <input type="time" name="time_in" class="form-control" value="{{ old('time_in', $attendance->time_in) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Time Out</label>
                <input type="time" name="time_out" class="form-control" value="{{ old('time_out', $attendance->time_out) }}">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold">Status</label>
            <select name="attendance_status" class="form-select">
                @foreach(['Present','Absent','On Leave'] as $s)
                    <option value="{{ $s }}" {{ old('attendance_status', $attendance->attendance_status) === $s ? 'selected':'' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.attendance.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Record</button>
        </div>
    </form>
</div>
</div>
</div>
@endsection

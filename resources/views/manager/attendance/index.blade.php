@extends('layouts.app')
@section('title', 'Attendance')
@section('page-title', 'Attendance Records')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-fl p-3 d-flex align-items-center gap-3">
            <div class="rounded-3" style="background:#d1fae5;width:44px;height:44px;place-items:center;display:grid;">
                <i class="bi bi-check-circle-fill fs-5" style="color:#10b981;"></i>
            </div>
            <div><div class="fw-bold fs-5 lh-1">{{ $todayPresent }}</div><div class="text-muted" style="font-size:.75rem;">Present Today</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-fl p-3 d-flex align-items-center gap-3">
            <div class="rounded-3" style="background:#fee2e2;width:44px;height:44px;place-items:center;display:grid;">
                <i class="bi bi-x-circle-fill fs-5" style="color:#ef4444;"></i>
            </div>
            <div><div class="fw-bold fs-5 lh-1">{{ $todayAbsent }}</div><div class="text-muted" style="font-size:.75rem;">Absent Today</div></div>
        </div>
    </div>
</div>

<form class="d-flex flex-wrap gap-2 mb-4" method="GET">
    <select name="employee_id" class="form-select form-select-sm" style="width:180px;">
        <option value="">All Employees</option>
        @foreach($employees as $emp)
            <option value="{{ $emp->employee_id }}" {{ request('employee_id') == $emp->employee_id ? 'selected':'' }}>{{ $emp->full_name }}</option>
        @endforeach
    </select>
    <input type="date" name="from" class="form-control form-control-sm" style="width:140px;" value="{{ request('from') }}">
    <input type="date" name="to"   class="form-control form-control-sm" style="width:140px;" value="{{ request('to') }}">
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    <a href="{{ route('manager.attendance.create') }}" class="btn btn-sm btn-primary ms-auto">
        <i class="bi bi-plus-circle me-1"></i> Log Attendance
    </a>
</form>

<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Employee</th><th>Date</th><th>Time In</th><th>Time Out</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                <tr>
                    <td class="fw-semibold">{{ $rec->employee?->full_name }}</td>
                    <td>{{ $rec->date?->format('M j, Y') }}</td>
                    <td>{{ $rec->time_in ? \Carbon\Carbon::createFromFormat('H:i',$rec->time_in)->format('g:i a') : '—' }}</td>
                    <td>{{ $rec->time_out ? \Carbon\Carbon::createFromFormat('H:i',$rec->time_out)->format('g:i a') : '—' }}</td>
                    <td>
                        @php $b = match($rec->attendance_status){'Present'=>'success','Absent'=>'danger',default=>'warning'}; @endphp
                        <span class="badge bg-{{ $b }}-subtle text-{{ $b }}">{{ $rec->attendance_status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('manager.attendance.edit', $rec->attendance_id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="px-3 py-2 border-top">{{ $records->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection

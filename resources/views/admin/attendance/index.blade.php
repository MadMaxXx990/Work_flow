@extends('layouts.app')
@section('title', 'Attendance')
@section('page-title', 'Attendance Records')

@section('content')
{{-- ── Summary ─────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Present Today', $todayPresent, '#10b981', '#d1fae5', 'bi-check-circle-fill'],
        ['Absent Today',  $todayAbsent,  '#ef4444', '#fee2e2', 'bi-x-circle-fill'],
    ] as [$lbl, $val, $color, $bg, $icon])
    <div class="col-6 col-md-3">
        <div class="card-fl p-3 d-flex align-items-center gap-3">
            <div class="rounded-3" style="background:{{ $bg }};width:44px;height:44px;place-items:center;display:grid;">
                <i class="bi {{ $icon }} fs-5" style="color:{{ $color }};"></i>
            </div>
            <div>
                <div class="fw-bold fs-5 lh-1">{{ $val }}</div>
                <div class="text-muted" style="font-size:.75rem;">{{ $lbl }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────────── --}}
<form class="d-flex flex-wrap gap-2 mb-4" method="GET">
    <select name="employee_id" class="form-select form-select-sm" style="width:180px;">
        <option value="">All Employees</option>
        @foreach($employees as $emp)
            <option value="{{ $emp->employee_id }}" {{ request('employee_id') == $emp->employee_id ? 'selected' : '' }}>
                {{ $emp->full_name }}
            </option>
        @endforeach
    </select>
    <input type="date" name="from" class="form-control form-control-sm" style="width:140px;" value="{{ request('from') }}" placeholder="From">
    <input type="date" name="to"   class="form-control form-control-sm" style="width:140px;" value="{{ request('to') }}"   placeholder="To">
    <select name="attendance_status" class="form-select form-select-sm" style="width:140px;">
        <option value="">All Statuses</option>
        @foreach(['Present','Absent','On Leave'] as $s)
            <option value="{{ $s }}" {{ request('attendance_status') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    @if(request()->hasAny(['employee_id','from','to','attendance_status']))
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-light">Clear</a>
    @endif
    <a href="{{ route('admin.attendance.create') }}" class="btn btn-sm btn-primary ms-auto">
        <i class="bi bi-plus-circle me-1"></i> Log Attendance
    </a>
</form>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                <tr>
                    <td class="fw-semibold">{{ $rec->employee?->full_name ?? '—' }}</td>
                    <td class="text-muted">{{ $rec->employee?->department?->department_name ?? '—' }}</td>
                    <td>{{ $rec->date?->format('M j, Y') }}</td>
                    <td>{{ $rec->time_in ? \Carbon\Carbon::createFromFormat('H:i', $rec->time_in)->format('g:i a') : '—' }}</td>
                    <td>{{ $rec->time_out ? \Carbon\Carbon::createFromFormat('H:i', $rec->time_out)->format('g:i a') : '—' }}</td>
                    <td class="text-muted">
                        @if($rec->time_in && $rec->time_out)
                            @php
                                $in  = \Carbon\Carbon::createFromFormat('H:i', $rec->time_in);
                                $out = \Carbon\Carbon::createFromFormat('H:i', $rec->time_out);
                                $hrs = $in->diffInMinutes($out) / 60;
                            @endphp
                            {{ number_format($hrs, 1) }} hrs
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @php $badge = match($rec->attendance_status) { 'Present'=>'success', 'Absent'=>'danger', default=>'warning' }; @endphp
                        <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}">{{ $rec->attendance_status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.attendance.edit', $rec->attendance_id) }}"
                           class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.attendance.destroy', $rec->attendance_id) }}"
                              method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="px-3 py-2 border-top d-flex justify-content-between small text-muted">
        <span>Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }}</span>
        {{ $records->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

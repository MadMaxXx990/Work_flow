@extends('layouts.app')
@section('title', 'My Attendance')
@section('page-title', 'My Attendance')

@section('content')
{{-- ── Today's card ────────────────────────────────────────────────────────── --}}
<div class="card-fl p-4 mb-4">
    <h6 class="fw-semibold mb-3">Today — {{ today()->format('l, F j, Y') }}</h6>

    @if($todayRecord)
    <div class="row g-3 small mb-3">
        <div class="col-4">
            <div class="text-muted">Status</div>
            @php $b = match($todayRecord->attendance_status){'Present'=>'success','Absent'=>'danger',default=>'warning'}; @endphp
            <span class="badge bg-{{ $b }}-subtle text-{{ $b }} fs-6">{{ $todayRecord->attendance_status }}</span>
        </div>
        <div class="col-4">
            <div class="text-muted">Time In</div>
            <div class="fw-bold">{{ $todayRecord->time_in ? \Carbon\Carbon::createFromFormat('H:i',$todayRecord->time_in)->format('g:i a') : '—' }}</div>
        </div>
        <div class="col-4">
            <div class="text-muted">Time Out</div>
            <div class="fw-bold">{{ $todayRecord->time_out ? \Carbon\Carbon::createFromFormat('H:i',$todayRecord->time_out)->format('g:i a') : '—' }}</div>
        </div>
    </div>
    @endif

    <div class="d-flex gap-3 flex-wrap">
        @if(!$todayRecord)
        <form action="{{ route('employee.attendance.time-in') }}" method="POST">
            @csrf
            <button class="btn btn-success">
                <i class="bi bi-box-arrow-in-right me-1"></i> Time In
            </button>
        </form>
        @elseif(!$todayRecord->time_out)
        <form action="{{ route('employee.attendance.time-out') }}" method="POST">
            @csrf
            <button class="btn btn-danger">
                <i class="bi bi-box-arrow-right me-1"></i> Time Out
            </button>
        </form>
        @else
        <div class="alert alert-success py-2 small mb-0">
            <i class="bi bi-check-circle-fill me-1"></i>
            Attendance complete for today.
            Hours: {{ number_format(\Carbon\Carbon::createFromFormat('H:i',$todayRecord->time_in)->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i',$todayRecord->time_out)) / 60, 1) }} hrs
        </div>
        @endif
    </div>
</div>

{{-- ── History ──────────────────────────────────────────────────────────────── --}}
<div class="card-fl">
    <div class="px-3 pt-3 pb-2 border-bottom fw-semibold small">My Attendance History</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Hours</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                <tr>
                    <td>{{ $rec->date?->format('M j, Y') }}</td>
                    <td>{{ $rec->time_in  ? \Carbon\Carbon::createFromFormat('H:i',$rec->time_in)->format('g:i a')  : '—' }}</td>
                    <td>{{ $rec->time_out ? \Carbon\Carbon::createFromFormat('H:i',$rec->time_out)->format('g:i a') : '—' }}</td>
                    <td class="text-muted">
                        @if($rec->time_in && $rec->time_out)
                            {{ number_format(\Carbon\Carbon::createFromFormat('H:i',$rec->time_in)->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i',$rec->time_out))/60, 1) }} hrs
                        @else — @endif
                    </td>
                    <td>
                        @php $b = match($rec->attendance_status){'Present'=>'success','Absent'=>'danger',default=>'warning'}; @endphp
                        <span class="badge bg-{{ $b }}-subtle text-{{ $b }}">{{ $rec->attendance_status }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">No records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="px-3 py-2 border-top">{{ $records->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection

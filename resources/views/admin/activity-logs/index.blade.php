@extends('layouts.app')
@section('title','Activity Log')
@section('page-title','Activity Audit Log')

@section('content')
<form class="d-flex flex-wrap gap-2 mb-4" method="GET">
    <input type="text" name="search" class="form-control form-control-sm" style="width:220px;"
           placeholder="Search description…" value="{{ request('search') }}">
    <select name="user_id" class="form-select form-select-sm" style="width:180px;">
        <option value="">All Users</option>
        @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected':'' }}>
                {{ $u->employee?->full_name ?? $u->username }}
            </option>
        @endforeach
    </select>
    <select name="action_type" class="form-select form-select-sm" style="width:150px;">
        <option value="">All Actions</option>
        @foreach($actionTypes as $a)
            <option value="{{ $a }}" {{ request('action_type') === $a ? 'selected':'' }}>{{ ucfirst($a) }}</option>
        @endforeach
    </select>
    <input type="date" name="from" class="form-control form-control-sm" style="width:135px;" value="{{ request('from') }}">
    <input type="date" name="to"   class="form-control form-control-sm" style="width:135px;" value="{{ request('to') }}">
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    @if(request()->hasAny(['search','user_id','action_type','from','to']))
        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-light">Clear</a>
    @endif
</form>

<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Timestamp</th><th>User</th><th>Action</th><th>Table</th><th>Record ID</th><th>Description</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-muted text-nowrap">{{ $log->created_at?->format('M j, Y g:i a') }}</td>
                    <td class="fw-semibold">{{ $log->user?->employee?->full_name ?? $log->user?->username ?? '—' }}</td>
                    <td>
                        @php
                            $actionColor = match($log->action_type) {
                                'login','logout'  => 'primary',
                                'create','assignment' => 'success',
                                'update','comment'    => 'info',
                                'delete'          => 'danger',
                                'approval'        => 'warning',
                                default           => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $actionColor }}-subtle text-{{ $actionColor }} border">
                            {{ ucfirst($log->action_type) }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $log->table_affected ?? '—' }}</td>
                    <td class="text-muted">{{ $log->record_id ?? '—' }}</td>
                    <td class="text-muted">{{ Str::limit($log->description, 70) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-journal-text fs-2 d-block mb-2 opacity-50"></i>No activity logged yet.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-3 py-2 border-top d-flex justify-content-between small text-muted">
        <span>Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</span>
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

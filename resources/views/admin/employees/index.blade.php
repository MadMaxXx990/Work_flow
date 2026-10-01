@extends('layouts.app')
@section('title', 'Employees')
@section('page-title', 'Employee Management')

@section('content')
{{-- ── Toolbar ────────────────────────────────────────────────────────────── --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-4">
    <form class="d-flex flex-wrap gap-2" method="GET" action="{{ route('admin.employees.index') }}">
        <input type="text" name="search" class="form-control form-control-sm" style="width:220px;"
               placeholder="Search name or email…" value="{{ request('search') }}">

        <select name="department_id" class="form-select form-select-sm" style="width:180px;">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                    {{ $dept->department_name }}
                </option>
            @endforeach
        </select>

        <select name="status" class="form-select form-select-sm" style="width:140px;">
            <option value="">All Statuses</option>
            <option value="Active"    {{ request('status') === 'Active'    ? 'selected' : '' }}>Active</option>
            <option value="On Leave"  {{ request('status') === 'On Leave'  ? 'selected' : '' }}>On Leave</option>
            <option value="Inactive"  {{ request('status') === 'Inactive'  ? 'selected' : '' }}>Inactive</option>
        </select>

        <button type="submit" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-search"></i>
        </button>
        @if(request()->hasAny(['search','department_id','status']))
            <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-light">Clear</a>
        @endif
    </form>

    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Add Employee
    </a>
</div>

{{-- ── Table ───────────────────────────────────────────────────────────────── --}}
<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Role</th>
                    <th>Hire Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($emp->profile_photo_url)
                                <img src="{{ asset('storage/'.$emp->profile_photo_url) }}"
                                     class="rounded-circle" width="34" height="34" style="object-fit:cover;">
                            @else
                                <div class="rounded-circle bg-primary d-grid fw-bold text-white"
                                     style="width:34px;height:34px;place-items:center;font-size:.8rem;display:grid;">
                                    {{ strtoupper(substr($emp->first_name,0,1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $emp->full_name }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $emp->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $emp->department?->department_name ?? '—' }}</td>
                    <td>{{ $emp->position?->position_name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary border">
                            {{ $emp->user?->role?->role_name ?? 'None' }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $emp->hire_date?->format('M j, Y') ?? '—' }}</td>
                    <td>
                        @php
                            $badge = match($emp->status) {
                                'Active'   => 'success',
                                'On Leave' => 'warning',
                                default    => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }} border">{{ $emp->status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.employees.show', $emp->employee_id) }}"
                           class="btn btn-sm btn-outline-secondary py-0 px-2 me-1" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.employees.edit', $emp->employee_id) }}"
                           class="btn btn-sm btn-outline-secondary py-0 px-2 me-1" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.employees.destroy', $emp->employee_id) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Deactivate {{ $emp->full_name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Deactivate">
                                <i class="bi bi-person-dash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
                        No employees found.
                        @if(!request()->hasAny(['search','department_id','status']))
                            <a href="{{ route('admin.employees.create') }}">Add the first one</a>.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($employees->hasPages())
    <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center small text-muted">
        <span>Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}</span>
        {{ $employees->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

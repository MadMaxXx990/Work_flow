@extends('layouts.app')
@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted small mb-0">{{ $departments->count() }} department(s) configured</p>
    </div>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Department
    </a>
</div>

<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Department Name</th>
                    <th>Description</th>
                    <th class="text-center">Employees</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                <tr>
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $dept->department_name }}</td>
                    <td class="text-muted">{{ $dept->description ?: '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary">{{ $dept->employees_count }}</span>
                    </td>
                    <td class="text-muted">{{ $dept->created_at?->format('M j, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.departments.edit', $dept->department_id) }}"
                           class="btn btn-sm btn-outline-secondary py-0 px-2 me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.departments.destroy', $dept->department_id) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Remove this department?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-building fs-2 d-block mb-2 text-muted opacity-50"></i>
                        No departments yet. <a href="{{ route('admin.departments.create') }}">Add one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

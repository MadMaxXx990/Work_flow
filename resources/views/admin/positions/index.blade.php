@extends('layouts.app')
@section('title', 'Positions')
@section('page-title', 'Positions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted small mb-0">{{ $positions->count() }} position(s) defined</p>
    <a href="{{ route('admin.positions.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Position
    </a>
</div>

<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Position Name</th><th>Description</th>
                    <th class="text-center">Employees</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $pos)
                <tr>
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $pos->position_name }}</td>
                    <td class="text-muted">{{ $pos->description ?: '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary">{{ $pos->employees_count }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.positions.edit', $pos->position_id) }}"
                           class="btn btn-sm btn-outline-secondary py-0 px-2 me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.positions.destroy', $pos->position_id) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Remove this position?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-briefcase fs-2 d-block mb-2 opacity-50"></i>
                        No positions yet. <a href="{{ route('admin.positions.create') }}">Add one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title','File Repository')
@section('page-title','File Repository')

@section('content')
<form class="d-flex flex-wrap gap-2 mb-4" method="GET">
    <input type="text" name="search" class="form-control form-control-sm" style="width:220px;"
           placeholder="Search filename…" value="{{ request('search') }}">
    <select name="file_type" class="form-select form-select-sm" style="width:140px;">
        <option value="">All Types</option>
        @foreach($fileTypes as $t)
            <option value="{{ $t }}" {{ request('file_type') === $t ? 'selected':'' }}>{{ strtoupper($t) }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    @if(request()->hasAny(['search','file_type']))
        <a href="{{ route('admin.files.index') }}" class="btn btn-sm btn-light">Clear</a>
    @endif
</form>

<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>File Name</th><th>Type</th><th>Task</th><th>Uploaded By</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($files as $f)
                <tr>
                    <td>
                        <i class="bi bi-file-earmark me-1 text-muted"></i>
                        <span class="fw-semibold">{{ $f->file_name }}</span>
                    </td>
                    <td><span class="badge bg-secondary-subtle text-secondary">{{ strtoupper($f->file_type ?? '?') }}</span></td>
                    <td>
                        @if($f->task)
                            <a href="{{ route('admin.tasks.show', $f->task->task_id) }}" class="text-decoration-none small">
                                {{ Str::limit($f->task->task_title, 30) }}
                            </a>
                        @else —
                        @endif
                    </td>
                    <td class="text-muted">{{ $f->uploadedBy?->employee?->full_name ?? $f->uploadedBy?->username ?? '—' }}</td>
                    <td class="text-muted">{{ $f->uploaded_at?->format('M j, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.files.download', $f->attachment_id) }}"
                           class="btn btn-sm btn-outline-primary py-0 px-2 me-1">
                            <i class="bi bi-download"></i>
                        </a>
                        <form action="{{ route('admin.files.destroy', $f->attachment_id) }}"
                              method="POST" class="d-inline" onsubmit="return confirm('Delete this file?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-folder2-open fs-2 d-block mb-2 opacity-50"></i>No files yet.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($files->hasPages())
    <div class="px-3 py-2 border-top d-flex justify-content-between small text-muted">
        <span>Showing {{ $files->firstItem() }}–{{ $files->lastItem() }} of {{ $files->total() }}</span>
        {{ $files->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

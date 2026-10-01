@extends('layouts.app')
@section('title','My Files')
@section('page-title','My Task Files')

@section('content')
<div class="card-fl">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>File Name</th><th>Type</th><th>Task</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($files as $f)
                <tr>
                    <td><i class="bi bi-file-earmark me-1 text-muted"></i><span class="fw-semibold">{{ $f->file_name }}</span></td>
                    <td><span class="badge bg-secondary-subtle text-secondary">{{ strtoupper($f->file_type ?? '?') }}</span></td>
                    <td>
                        @if($f->task)
                            <a href="{{ route('employee.tasks.show', $f->task->task_id) }}" class="text-decoration-none small">{{ Str::limit($f->task->task_title, 30) }}</a>
                        @else — @endif
                    </td>
                    <td class="text-muted">{{ $f->uploaded_at?->format('M j, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('employee.files.download', $f->attachment_id) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="bi bi-download"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-folder2-open fs-2 d-block mb-2 opacity-50"></i>No files yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($files->hasPages())
    <div class="px-3 py-2 border-top">{{ $files->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection

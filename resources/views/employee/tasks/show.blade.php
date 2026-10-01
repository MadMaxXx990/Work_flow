@extends('layouts.app')
@section('title', $task->task_title)
@section('page-title', 'Task Detail')

@section('content')
<div class="row g-4">
<div class="col-lg-8">

    {{-- Header --}}
    <div class="card-fl p-4 mb-4">
        <h5 class="fw-bold mb-2">{{ $task->task_title }}</h5>
        <div class="d-flex flex-wrap gap-2 mb-3">
            @php $s = strtolower(str_replace(' ','_',$task->stage?->stage_name??'')); @endphp
            <span class="badge badge-stage-{{ $s }}">{{ $task->stage?->stage_name }}</span>
            <span class="priority-{{ strtolower($task->priority?->priority_name??'low') }} fw-semibold small">{{ $task->priority?->priority_name }} Priority</span>
            @if($task->due_date < now() && $task->stage?->stage_name !== 'Completed')
                <span class="badge bg-danger-subtle text-danger">Overdue</span>
            @endif
        </div>
        <p class="text-muted small">{{ $task->task_description ?: 'No description provided.' }}</p>
        <div class="row g-3 small">
            <div class="col-6 col-md-3"><div class="text-muted">Due Date</div><div class="fw-semibold {{ $task->due_date < now() ? 'text-danger':'' }}">{{ $task->due_date?->format('M j, Y') }}</div></div>
            <div class="col-6 col-md-3"><div class="text-muted">Assigned By</div><div class="fw-semibold">{{ $task->creator?->employee?->full_name ?? '—' }}</div></div>
            <div class="col-6 col-md-3"><div class="text-muted">Progress</div><div class="fw-semibold">{{ $task->latestProgress() }}%</div></div>
        </div>
    </div>

    @php $progress = $task->latestProgress(); @endphp
    <div class="card-fl p-3 mb-4">
        <div class="d-flex justify-content-between small mb-1"><span class="fw-semibold">My Progress</span><span>{{ $progress }}%</span></div>
        <div class="progress" style="height:8px;"><div class="progress-bar bg-primary" style="width:{{ $progress }}%"></div></div>
    </div>

    {{-- Approval result --}}
    @if($latestApproval)
    <div class="alert {{ $latestApproval->approval_status === 'Approved' ? 'alert-success' : 'alert-warning' }} d-flex gap-2 align-items-start py-2 small mb-4">
        <i class="bi {{ $latestApproval->approval_status === 'Approved' ? 'bi-check-circle-fill' : 'bi-arrow-counterclockwise' }} mt-1"></i>
        <div>
            <strong>{{ $latestApproval->approval_status }}</strong>
            @if($latestApproval->remarks) — {{ $latestApproval->remarks }} @endif
        </div>
    </div>
    @endif

    {{-- Post Progress Update --}}
    @if(!in_array($task->stage?->stage_name, ['Completed','For Review']))
    <div class="card-fl p-4 mb-4">
        <p class="fw-semibold small mb-3">Post a Progress Update</p>
        <form action="{{ route('employee.tasks.update', $task->task_id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Progress %</label>
                <input type="range" name="progress_percent" class="form-range" min="0" max="100" step="5"
                       value="{{ $task->latestProgress() }}" oninput="this.nextElementSibling.textContent = this.value + '%'">
                <span class="small text-muted">{{ $task->latestProgress() }}%</span>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Update Message <span class="text-danger">*</span></label>
                <textarea name="update_message" rows="3" class="form-control" required placeholder="Describe what you've done…"></textarea>
            </div>
            <button class="btn btn-primary btn-sm">Save Update</button>
        </form>
    </div>

    {{-- Submit For Review --}}
    <div class="card-fl p-3 mb-4">
        <p class="fw-semibold small mb-2">Ready to submit?</p>
        <form action="{{ route('employee.tasks.submit-review', $task->task_id) }}" method="POST"
              onsubmit="return confirm('Submit this task for manager review?')">
            @csrf
            <button class="btn btn-warning btn-sm">
                <i class="bi bi-send me-1"></i> Submit for Review
            </button>
        </form>
    </div>
    @endif

    {{-- Upload file --}}
    <div class="card-fl p-3 mb-4">
        <p class="fw-semibold small mb-2">Upload File</p>
        <form action="{{ route('employee.tasks.attachments.store', $task->task_id) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
            @csrf
            <input type="file" name="file" class="form-control form-control-sm" required>
            <button class="btn btn-sm btn-outline-primary flex-shrink-0">Upload</button>
        </form>
    </div>

    {{-- Progress history --}}
    <div class="card-fl mb-4">
        <div class="px-3 pt-3 pb-2 border-bottom fw-semibold small">My Updates</div>
        @forelse($task->updates as $u)
        <div class="px-3 py-2 border-bottom small">
            <div class="d-flex justify-content-between">
                <span class="fw-semibold">{{ $u->progress_percent }}% complete</span>
                <span class="text-muted">{{ $u->update_date?->format('M j, g:i a') }}</span>
            </div>
            <p class="mb-0 mt-1">{{ $u->update_message }}</p>
        </div>
        @empty
        <div class="p-3 text-center text-muted small">No updates posted.</div>
        @endforelse
    </div>

    {{-- Comments --}}
    <div class="card-fl mb-4">
        <div class="px-3 pt-3 pb-2 border-bottom fw-semibold small">Comments</div>
        @forelse($task->comments as $c)
        <div class="px-3 py-2 border-bottom small">
            <div class="d-flex justify-content-between">
                <span class="fw-semibold">{{ $c->user?->employee?->full_name ?? $c->user?->username }}</span>
                <span class="text-muted">{{ $c->created_at?->diffForHumans() }}</span>
            </div>
            <p class="mb-0 mt-1">{{ $c->comment_text }}</p>
        </div>
        @empty
        <div class="p-3 text-center text-muted small">No comments.</div>
        @endforelse
        <div class="p-3">
            <form action="{{ route('employee.tasks.comment', $task->task_id) }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="text" name="comment_text" class="form-control form-control-sm" placeholder="Add a comment…" required>
                <button class="btn btn-sm btn-primary">Post</button>
            </form>
        </div>
    </div>
</div>

<div class="col-lg-4">
    <div class="card-fl p-3 mb-4">
        <p class="fw-semibold small mb-2">Attachments</p>
        @foreach($task->attachments as $att)
        <div class="d-flex align-items-center justify-content-between py-1 border-bottom small">
            <div class="d-flex gap-2"><i class="bi bi-file-earmark text-muted"></i><span>{{ Str::limit($att->file_name, 26) }}</span></div>
            <a href="{{ route('employee.files.download', $att->attachment_id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-download"></i></a>
        </div>
        @endforeach
        @if($task->attachments->isEmpty())<div class="text-muted small">No files yet.</div>@endif
    </div>

    <div class="card-fl p-3">
        <p class="fw-semibold small mb-2">Stage History</p>
        @foreach($task->stageHistory->sortByDesc('changed_at') as $h)
        <div class="d-flex gap-2 mb-2 small">
            <div class="text-muted mt-1"><i class="bi bi-circle-fill" style="font-size:.45rem;"></i></div>
            <div>
                <div>@if($h->oldStage)<span class="text-muted">{{ $h->oldStage->stage_name }}</span><i class="bi bi-arrow-right mx-1 text-muted"></i>@endif<span class="fw-semibold">{{ $h->newStage?->stage_name }}</span></div>
                <div class="text-muted" style="font-size:.7rem;">{{ $h->changed_at?->format('M j, g:i a') }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
</div>
@endsection

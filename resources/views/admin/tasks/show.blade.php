@extends('layouts.app')
@section('title', $task->task_title)
@section('page-title', 'Task Detail')

@section('content')
<div class="row g-4">

{{-- ── LEFT: Main detail ────────────────────────────────────────────────────── --}}
<div class="col-lg-8">

    {{-- Header --}}
    <div class="card-fl p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h5 class="fw-bold mb-1">{{ $task->task_title }}</h5>
                <div class="d-flex flex-wrap gap-2">
                    @php $s = strtolower(str_replace(' ','_',$task->stage?->stage_name??'')); @endphp
                    <span class="badge badge-stage-{{ $s }}">{{ $task->stage?->stage_name }}</span>
                    <span class="priority-{{ strtolower($task->priority?->priority_name??'low') }} fw-semibold small">
                        {{ $task->priority?->priority_name }} Priority
                    </span>
                    @if($task->due_date < now() && $task->stage?->stage_name !== 'Completed')
                        <span class="badge bg-danger-subtle text-danger">Overdue</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.tasks.edit', $task->task_id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="{{ route('admin.tasks.destroy', $task->task_id) }}" method="POST"
                      onsubmit="return confirm('Delete this task?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>

        <p class="text-muted small mb-4">{{ $task->task_description ?: 'No description provided.' }}</p>

        <div class="row g-3 small">
            <div class="col-6 col-md-3">
                <div class="text-muted">Start Date</div>
                <div class="fw-semibold">{{ $task->start_date?->format('M j, Y') ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted">Due Date</div>
                <div class="fw-semibold {{ $task->due_date < now() ? 'text-danger' : '' }}">
                    {{ $task->due_date?->format('M j, Y') }}
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted">Created By</div>
                <div class="fw-semibold">{{ $task->creator?->employee?->full_name ?? $task->creator?->username }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted">Progress</div>
                <div class="fw-semibold">{{ $task->latestProgress() }}%</div>
            </div>
        </div>
    </div>

    {{-- Progress bar --}}
    @php $progress = $task->latestProgress(); @endphp
    <div class="card-fl p-3 mb-4">
        <div class="d-flex justify-content-between small mb-1">
            <span class="fw-semibold">Overall Progress</span>
            <span>{{ $progress }}%</span>
        </div>
        <div class="progress" style="height:8px;">
            <div class="progress-bar bg-primary" style="width:{{ $progress }}%;"></div>
        </div>
    </div>

    {{-- Stage change --}}
    <div class="card-fl p-3 mb-4">
        <p class="fw-semibold small mb-2">Change Stage</p>
        <form action="{{ route('admin.tasks.change-stage', $task->task_id) }}" method="POST" class="d-flex flex-wrap gap-2">
            @csrf
            <select name="stage_id" class="form-select form-select-sm" style="width:180px;">
                @foreach($stages as $stage)
                    <option value="{{ $stage->stage_id }}" {{ $task->stage_id == $stage->stage_id ? 'selected':'' }}>
                        {{ $stage->stage_name }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="remarks" class="form-control form-control-sm" style="flex:1;min-width:150px;" placeholder="Reason (optional)">
            <button class="btn btn-sm btn-outline-primary">Update Stage</button>
        </form>
    </div>

    {{-- Approval Panel --}}
    @if($task->stage?->stage_name === 'For Review')
    <div class="card-fl p-4 mb-4 border-warning">
        <p class="fw-semibold mb-3"><i class="bi bi-eye-fill text-warning me-2"></i>Approval Required</p>
        <form action="{{ route('admin.tasks.approve', $task->task_id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Decision</label>
                <select name="approval_status" class="form-select" required>
                    <option value="Approved">✅ Approve — Mark as Completed</option>
                    <option value="Revision Requested">🔁 Request Revision — Return to In Progress</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Remarks</label>
                <textarea name="remarks" rows="2" class="form-control" placeholder="Optional feedback…"></textarea>
            </div>
            <button class="btn btn-warning">Submit Decision</button>
        </form>
    </div>
    @endif

    @if($latestApproval)
    <div class="card-fl p-3 mb-4 small">
        <span class="fw-semibold">Last Review:</span>
        <span class="badge {{ $latestApproval->approval_status === 'Approved' ? 'bg-success' : 'bg-warning text-dark' }} ms-1">
            {{ $latestApproval->approval_status }}
        </span>
        @if($latestApproval->remarks)
            <span class="text-muted ms-2">— {{ $latestApproval->remarks }}</span>
        @endif
        <span class="text-muted ms-2">by {{ $latestApproval->approver?->employee?->full_name ?? $latestApproval->approver?->username }}</span>
    </div>
    @endif

    {{-- Progress Updates --}}
    <div class="card-fl mb-4">
        <div class="px-3 pt-3 pb-2 border-bottom fw-semibold small">
            Progress Updates ({{ $task->updates->count() }})
        </div>
        @forelse($task->updates as $update)
        <div class="px-3 py-2 border-bottom small">
            <div class="d-flex justify-content-between">
                <span class="fw-semibold">{{ $update->employee?->full_name }}</span>
                <span class="text-muted">{{ $update->update_date?->format('M j, Y g:i a') }}</span>
            </div>
            <p class="mb-1 mt-1">{{ $update->update_message }}</p>
            <div class="progress" style="height:4px;">
                <div class="progress-bar bg-primary" style="width:{{ $update->progress_percent }}%"></div>
            </div>
            <span class="text-muted">{{ $update->progress_percent }}% complete</span>
        </div>
        @empty
        <div class="p-3 text-center text-muted small">No progress updates yet.</div>
        @endforelse
    </div>

    {{-- Comments --}}
    <div class="card-fl mb-4">
        <div class="px-3 pt-3 pb-2 border-bottom fw-semibold small">Comments</div>
        @forelse($task->comments as $comment)
        <div class="px-3 py-2 border-bottom small">
            <div class="d-flex justify-content-between">
                <span class="fw-semibold">{{ $comment->user?->employee?->full_name ?? $comment->user?->username }}</span>
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted">{{ $comment->created_at?->diffForHumans() }}</span>
                    <form action="{{ route('admin.tasks.comments.destroy', [$task->task_id, $comment->comment_id]) }}"
                          method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm p-0 text-danger border-0 bg-transparent"><i class="bi bi-x"></i></button>
                    </form>
                </div>
            </div>
            <p class="mb-0 mt-1">{{ $comment->comment_text }}</p>
        </div>
        @empty
        <div class="p-3 text-center text-muted small">No comments yet.</div>
        @endforelse

        <div class="p-3">
            <form action="{{ route('admin.tasks.comment', $task->task_id) }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="text" name="comment_text" class="form-control form-control-sm" placeholder="Add a comment…" required>
                <button class="btn btn-sm btn-primary">Post</button>
            </form>
        </div>
    </div>

</div>

{{-- ── RIGHT: Sidebar ──────────────────────────────────────────────────────── --}}
<div class="col-lg-4">

    {{-- Assigned employees --}}
    <div class="card-fl p-3 mb-4">
        <p class="fw-semibold small mb-2">Assigned To</p>
        @foreach($task->assignments as $a)
        <div class="d-flex align-items-center gap-2 py-1">
            <div class="rounded-circle bg-primary text-white d-grid fw-bold"
                 style="width:30px;height:30px;place-items:center;font-size:.75rem;display:grid;flex-shrink:0">
                {{ strtoupper(substr($a->employee?->first_name??'?',0,1)) }}
            </div>
            <div class="small">
                <div class="fw-semibold">{{ $a->employee?->full_name ?? '—' }}</div>
                <div class="text-muted" style="font-size:.7rem;">{{ $a->employee?->position?->position_name }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Stage History --}}
    <div class="card-fl p-3 mb-4">
        <p class="fw-semibold small mb-2">Stage History</p>
        @foreach($task->stageHistory->sortByDesc('changed_at') as $h)
        <div class="d-flex gap-2 mb-2 small">
            <div class="text-muted mt-1"><i class="bi bi-circle-fill" style="font-size:.45rem;"></i></div>
            <div>
                <div>
                    @if($h->oldStage)
                        <span class="text-muted">{{ $h->oldStage->stage_name }}</span>
                        <i class="bi bi-arrow-right mx-1 text-muted"></i>
                    @endif
                    <span class="fw-semibold">{{ $h->newStage?->stage_name }}</span>
                </div>
                <div class="text-muted" style="font-size:.7rem;">
                    {{ $h->changedBy?->employee?->full_name ?? $h->changedBy?->username }}
                    · {{ $h->changed_at?->format('M j, g:i a') }}
                </div>
                @if($h->remarks) <div class="text-muted fst-italic">{{ $h->remarks }}</div> @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Attachments --}}
    <div class="card-fl p-3">
        <p class="fw-semibold small mb-2">Attachments ({{ $task->attachments->count() }})</p>
        @foreach($task->attachments as $att)
        <div class="d-flex align-items-center justify-content-between py-1 border-bottom small">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark text-muted"></i>
                <span>{{ Str::limit($att->file_name, 30) }}</span>
            </div>
            <a href="{{ route('admin.files.download', $att->attachment_id) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2">
                <i class="bi bi-download"></i>
            </a>
        </div>
        @endforeach
        @if($task->attachments->isEmpty())
        <div class="text-muted small">No files attached.</div>
        @endif
    </div>

</div>
</div>
@endsection

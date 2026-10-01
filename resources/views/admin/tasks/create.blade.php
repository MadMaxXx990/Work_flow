@extends('layouts.app')
@section('title','Assign Task')
@section('page-title','Assign Task')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card-fl p-4">

    <h5 class="fw-bold mb-1">New Task Assignment</h5>
    <p class="text-muted small mb-4">Fill in task details, select assignees, and set a deadline.</p>

    <form action="{{ route('admin.tasks.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Step 1: Task Details --}}
        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.7rem;letter-spacing:.1em;">
            Step 1 — Task Details
        </h6>
        <div class="mb-3">
            <label class="form-label small fw-semibold">Task Title <span class="text-danger">*</span></label>
            <input type="text" name="task_title" class="form-control @error('task_title') is-invalid @enderror"
                   value="{{ old('task_title') }}" required placeholder="e.g. Prepare Q4 Report">
            @error('task_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold">Description</label>
            <textarea name="task_description" rows="4" class="form-control"
                      placeholder="Detailed task instructions…">{{ old('task_description') }}</textarea>
        </div>

        <hr class="my-4">

        {{-- Step 2: Assign Employees --}}
        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.7rem;letter-spacing:.1em;">
            Step 2 — Assign Employees
        </h6>
        <div class="mb-4">
            <label class="form-label small fw-semibold">Select Employee(s) <span class="text-danger">*</span></label>
            @error('employee_ids') <div class="text-danger small mb-1">{{ $message }}</div> @enderror
            <div class="row g-2" style="max-height:280px;overflow-y:auto;">
                @foreach($employees as $emp)
                <div class="col-md-6">
                    <label class="d-flex align-items-center gap-2 p-2 border rounded cursor-pointer
                                  {{ in_array($emp->employee_id, (array)old('employee_ids',[])) ? 'border-primary bg-primary-subtle' : '' }}"
                           style="cursor:pointer;">
                        <input type="checkbox" name="employee_ids[]" value="{{ $emp->employee_id }}"
                               class="form-check-input mt-0"
                               {{ in_array($emp->employee_id, (array)old('employee_ids',[])) ? 'checked' : '' }}>
                        <div>
                            <div class="fw-semibold small">{{ $emp->full_name }}</div>
                            <div class="text-muted" style="font-size:.7rem;">{{ $emp->department?->department_name }}</div>
                        </div>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <hr class="my-4">

        {{-- Step 3: Priority, Deadline, Attachments --}}
        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.7rem;letter-spacing:.1em;">
            Step 3 — Priority, Deadline & Files
        </h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Priority <span class="text-danger">*</span></label>
                <select name="priority_id" class="form-select @error('priority_id') is-invalid @enderror" required>
                    @foreach($priorities as $p)
                        <option value="{{ $p->priority_id }}" {{ old('priority_id') == $p->priority_id ? 'selected' : '' }}>
                            {{ $p->priority_name }}
                        </option>
                    @endforeach
                </select>
                @error('priority_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', today()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Due Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                       value="{{ old('due_date') }}" required>
                @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Attachments</label>
                <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xlsx,.png,.jpg,.zip"
                       class="form-control">
                <div class="form-text">Multiple files allowed. Max 10MB each.</div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send-fill me-1"></i> Assign Task
            </button>
        </div>
    </form>
</div>
</div>
</div>
@endsection

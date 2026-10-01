@extends('layouts.app')
@section('title','Edit Task')
@section('page-title','Edit Task')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card-fl p-4">
    <h5 class="fw-bold mb-4">Edit Task</h5>
    <form action="{{ route('manager.tasks.update', $task->task_id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label small fw-semibold">Task Title <span class="text-danger">*</span></label>
            <input type="text" name="task_title" class="form-control" value="{{ old('task_title', $task->task_title) }}" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold">Description</label>
            <textarea name="task_description" rows="4" class="form-control">{{ old('task_description', $task->task_description) }}</textarea>
        </div>
        <hr class="my-4">

        <div class="mb-4">
            <label class="form-label small fw-semibold">Assign Employees</label>
            <div class="row g-2" style="max-height:240px;overflow-y:auto;">
                @foreach($employees as $emp)
                <div class="col-md-6">
                    <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer;">
                        <input type="checkbox" name="employee_ids[]" value="{{ $emp->employee_id }}"
                               class="form-check-input mt-0"
                               {{ in_array($emp->employee_id, $assignedIds) ? 'checked':'' }}>
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

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Priority</label>
                <select name="priority_id" class="form-select">
                    @foreach($priorities as $p)
                        <option value="{{ $p->priority_id }}" {{ old('priority_id', $task->priority_id) == $p->priority_id ? 'selected':'' }}>{{ $p->priority_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Due Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('manager.tasks.show', $task->task_id) }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
</div>
</div>
@endsection

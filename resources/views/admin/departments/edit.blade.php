@extends('layouts.app')
@section('title', 'Edit Department')
@section('page-title', 'Edit Department')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card-fl p-4">
            <h5 class="fw-bold mb-4">Edit: {{ $department->department_name }}</h5>
            <form action="{{ route('admin.departments.update', $department->department_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Department Name <span class="text-danger">*</span></label>
                    <input type="text" name="department_name" class="form-control @error('department_name') is-invalid @enderror"
                           value="{{ old('department_name', $department->department_name) }}" required>
                    @error('department_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $department->description) }}</textarea>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Add Position')
@section('page-title', 'Add Position')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card-fl p-4">
            <h5 class="fw-bold mb-4">New Position</h5>
            <form action="{{ route('admin.positions.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Position Name <span class="text-danger">*</span></label>
                    <input type="text" name="position_name" class="form-control @error('position_name') is-invalid @enderror"
                           value="{{ old('position_name') }}" required>
                    @error('position_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.positions.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Position</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

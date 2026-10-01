@extends('layouts.app')
@section('title', 'Add Employee')
@section('page-title', 'Add Employee')

@push('styles')
<style>
    .step-indicator { display: flex; gap: .5rem; margin-bottom: 1.75rem; }
    .step { flex: 1; height: 4px; border-radius: 99px; background: #e5e7eb; }
    .step.done { background: #4f46e5; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card-fl p-4">

    <h5 class="fw-bold mb-1">New Employee Account</h5>
    <p class="text-muted small mb-4">Fill in the employee profile and system account details.</p>

    <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- ── SECTION 1: Personal Info ──────────────────────────────────── --}}
        <h6 class="fw-semibold text-uppercase text-muted mb-3" style="font-size:.7rem;letter-spacing:.1em;">
            Personal Information
        </h6>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                       value="{{ old('first_name') }}" required>
                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                       value="{{ old('last_name') }}" required>
                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Employment Type</label>
                <select name="employment_type" class="form-select">
                    <option value="">Select…</option>
                    @foreach(['Regular','Contractual','Part-time','Probationary'] as $t)
                        <option value="{{ $t }}" {{ old('employment_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Hire Date</label>
                <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date') }}">
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-semibold">Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*" class="form-control @error('profile_photo') is-invalid @enderror">
                @error('profile_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <hr class="my-4">

        {{-- ── SECTION 2: Organizational Info ───────────────────────────── --}}
        <h6 class="fw-semibold text-uppercase text-muted mb-3" style="font-size:.7rem;letter-spacing:.1em;">
            Organizational Assignment
        </h6>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Department <span class="text-danger">*</span></label>
                <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Position <span class="text-danger">*</span></label>
                <select name="position_id" class="form-select @error('position_id') is-invalid @enderror" required>
                    <option value="">Select Position</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->position_id }}" {{ old('position_id') == $pos->position_id ? 'selected' : '' }}>
                            {{ $pos->position_name }}
                        </option>
                    @endforeach
                </select>
                @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Direct Supervisor</label>
                <select name="supervisor_id" class="form-select">
                    <option value="">None</option>
                    @foreach($supervisors as $sup)
                        <option value="{{ $sup->employee_id }}" {{ old('supervisor_id') == $sup->employee_id ? 'selected' : '' }}>
                            {{ $sup->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="my-4">

        {{-- ── SECTION 3: Account Credentials ───────────────────────────── --}}
        <h6 class="fw-semibold text-uppercase text-muted mb-3" style="font-size:.7rem;letter-spacing:.1em;">
            System Account
        </h6>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">System Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->role_id }}" {{ old('role_id') == $role->role_id ? 'selected' : '' }}>
                            {{ $role->role_name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}" autocomplete="off" required>
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                <div class="form-text">Minimum 6 characters.</div>
            </div>
        </div>

        {{-- ── Footer ─────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-end gap-2 pt-2">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-person-check-fill me-1"></i> Create Employee
            </button>
        </div>
    </form>
</div>
</div>
</div>
@endsection

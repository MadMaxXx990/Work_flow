@extends('layouts.app')
@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card-fl p-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        @if($employee->profile_photo_url)
            <img src="{{ asset('storage/'.$employee->profile_photo_url) }}"
                 class="rounded-circle" width="50" height="50" style="object-fit:cover;">
        @else
            <div class="rounded-circle bg-primary d-grid text-white fw-bold"
                 style="width:50px;height:50px;place-items:center;font-size:1.1rem;display:grid;">
                {{ strtoupper(substr($employee->first_name,0,1)) }}
            </div>
        @endif
        <div>
            <h5 class="fw-bold mb-0">{{ $employee->full_name }}</h5>
            <span class="text-muted small">{{ $employee->email }}</span>
        </div>
    </div>

    <form action="{{ route('admin.employees.update', $employee->employee_id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- Personal Info --}}
        <h6 class="fw-semibold text-uppercase text-muted mb-3" style="font-size:.7rem;letter-spacing:.1em;">Personal Information</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                       value="{{ old('first_name', $employee->first_name) }}" required>
                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                       value="{{ old('last_name', $employee->last_name) }}" required>
                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $employee->email) }}" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Employment Type</label>
                <select name="employment_type" class="form-select">
                    <option value="">Select…</option>
                    @foreach(['Regular','Contractual','Part-time','Probationary'] as $t)
                        <option value="{{ $t }}" {{ old('employment_type', $employee->employment_type) === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Hire Date</label>
                <input type="date" name="hire_date" class="form-control"
                       value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    @foreach(['Active','On Leave','Inactive'] as $st)
                        <option value="{{ $st }}" {{ old('status', $employee->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-semibold">Update Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*" class="form-control">
                @if($employee->profile_photo_url)
                    <div class="form-text">Current photo on file. Upload a new one to replace it.</div>
                @endif
            </div>
        </div>

        <hr class="my-4">

        {{-- Organizational Info --}}
        <h6 class="fw-semibold text-uppercase text-muted mb-3" style="font-size:.7rem;letter-spacing:.1em;">Organizational Assignment</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Department <span class="text-danger">*</span></label>
                <select name="department_id" class="form-select" required>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ old('department_id', $employee->department_id) == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Position <span class="text-danger">*</span></label>
                <select name="position_id" class="form-select" required>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->position_id }}" {{ old('position_id', $employee->position_id) == $pos->position_id ? 'selected' : '' }}>
                            {{ $pos->position_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Direct Supervisor</label>
                <select name="supervisor_id" class="form-select">
                    <option value="">None</option>
                    @foreach($supervisors as $sup)
                        <option value="{{ $sup->employee_id }}" {{ old('supervisor_id', $employee->supervisor_id) == $sup->employee_id ? 'selected' : '' }}>
                            {{ $sup->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="my-4">

        {{-- Account --}}
        <h6 class="fw-semibold text-uppercase text-muted mb-3" style="font-size:.7rem;letter-spacing:.1em;">System Account</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->role_id }}" {{ old('role_id', $employee->user?->role_id) == $role->role_id ? 'selected' : '' }}>
                            {{ $role->role_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username', $employee->user?->username) }}" required autocomplete="off">
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">New Password</label>
                <input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="Leave blank to keep current">
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-2">
            <a href="{{ route('admin.employees.show', $employee->employee_id) }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
</div>
</div>
@endsection

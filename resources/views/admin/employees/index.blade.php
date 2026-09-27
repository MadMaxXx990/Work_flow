<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flowline - Employee Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Employees</h2>
                <p class="text-muted small mb-0">{{ $employees->count() }} active staff accounts</p>
            </div>
            <div>
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">+ Add employee</a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-danger ms-2">Logout</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>NAME</th>
                            <th>DEPARTMENT</th>
                            <th>POSITION</th>
                            <th>ROLE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td class="fw-semibold">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                <td>{{ $emp->department->department_name ?? 'N/A' }}</td>
                                <td>{{ $emp->position->position_name ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary">{{ $emp->user->role->role_name ?? 'None' }}</span></td>
                                <td><span class="badge bg-success-subtle text-success">{{ $emp->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No employee records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
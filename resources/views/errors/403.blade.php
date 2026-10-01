<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Forbidden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        <h1 class="display-1 fw-bold text-danger">403</h1>
        <h4 class="mb-2">Access Denied</h4>
        <p class="text-muted mb-4">You don't have permission to view this page.</p>
        <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">Go back</a>
        @auth
            @php
                $home = match(Auth::user()->role?->role_name) {
                    'Administrator' => route('admin.dashboard'),
                    'Manager'       => route('manager.dashboard'),
                    default         => route('employee.dashboard'),
                };
            @endphp
            <a href="{{ $home }}" class="btn btn-primary">Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary">Log in</a>
        @endauth
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Workflow System')</title>
    <!-- Add Tailwind, Bootstrap, or custom CSS links here -->
</head>
<body>
    <nav>
        <div class="logo">Workflow System</div>
        @auth
            <ul>
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('admin.employees.index') }}">Employees</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </li>
            </ul>
        @endauth
    </nav>

    <main class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
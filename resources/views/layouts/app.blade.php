<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Flowline') — Flowline</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Chart.js (loaded here, used in dashboard/reports views) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>

    <style>
        :root {
            --brand:        #4f46e5;
            --brand-dark:   #3730a3;
            --brand-light:  #eef2ff;
            --sidebar-w:    250px;
            --topbar-h:     60px;
        }

        /* ── Layout shell ──────────────────────────────────────────────── */
        body { margin: 0; background: #f5f6fa; font-family: 'Segoe UI', system-ui, sans-serif; }

        /* ── Sidebar ───────────────────────────────────────────────────── */
        #sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: #1e1b4b;
            display: flex; flex-direction: column;
            z-index: 1040;
            transition: transform .25s ease;
        }
        #sidebar .sidebar-brand {
            display: flex; align-items: center; gap: .6rem;
            padding: 1.1rem 1.25rem;
            color: #fff; font-weight: 800; font-size: 1.1rem;
            text-decoration: none; border-bottom: 1px solid rgba(255,255,255,.08);
        }
        #sidebar .sidebar-brand .icon-wrap {
            background: var(--brand); border-radius: 8px;
            padding: .3rem .45rem; line-height: 1;
        }
        #sidebar .nav-section {
            font-size: .68rem; font-weight: 700; letter-spacing: .1em;
            color: rgba(255,255,255,.35); padding: 1.25rem 1.25rem .4rem;
            text-transform: uppercase;
        }
        #sidebar .nav-link {
            display: flex; align-items: center; gap: .7rem;
            color: rgba(255,255,255,.65); font-size: .875rem;
            padding: .55rem 1.25rem; border-radius: 0;
            transition: background .15s, color .15s;
            text-decoration: none;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255,255,255,.09);
            color: #fff;
        }
        #sidebar .nav-link.active { border-left: 3px solid var(--brand); }
        #sidebar .nav-link i { font-size: 1rem; width: 1.1rem; text-align: center; }
        #sidebar .sidebar-footer {
            margin-top: auto; padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        #sidebar .user-pill {
            display: flex; align-items: center; gap: .65rem; color: #fff; text-decoration: none;
        }
        #sidebar .user-pill .avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--brand); display: grid; place-items: center;
            font-weight: 700; font-size: .8rem; color: #fff; flex-shrink: 0;
        }
        #sidebar .user-pill .user-info { min-width: 0; }
        #sidebar .user-pill .user-info .name { font-size: .82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        #sidebar .user-pill .user-info .role { font-size: .7rem; color: rgba(255,255,255,.45); }

        /* ── Topbar ────────────────────────────────────────────────────── */
        #topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0;
            height: var(--topbar-h); background: #fff;
            border-bottom: 1px solid #e9ecef;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem; z-index: 1030;
        }
        #topbar .page-heading { font-weight: 700; font-size: 1rem; color: #1e1b4b; }
        #topbar .topbar-right { display: flex; align-items: center; gap: 1rem; }
        .notif-btn { position: relative; background: none; border: none; font-size: 1.25rem; color: #6b7280; padding: 0; }
        .notif-badge {
            position: absolute; top: -4px; right: -4px;
            background: #ef4444; color: #fff;
            font-size: .55rem; font-weight: 700;
            width: 16px; height: 16px; border-radius: 50%;
            display: grid; place-items: center;
        }

        /* ── Main content ──────────────────────────────────────────────── */
        #main-content {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }
        .content-body { padding: 1.75rem 2rem; }

        /* ── Cards / helpers ───────────────────────────────────────────── */
        .card-fl {
            background: #fff; border: 1px solid #e9ecef;
            border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .badge-stage-pending     { background: #f3f4f6; color: #374151; }
        .badge-stage-in_progress { background: #dbeafe; color: #1d4ed8; }
        .badge-stage-for_review  { background: #fef3c7; color: #92400e; }
        .badge-stage-completed   { background: #d1fae5; color: #065f46; }
        .badge-stage-overdue     { background: #fee2e2; color: #991b1b; }

        .priority-low    { color: #16a34a; }
        .priority-medium { color: #d97706; }
        .priority-high   { color: #dc2626; }

        /* ── Alerts (flash) ────────────────────────────────────────────── */
        .flash-container { padding: 0 2rem; padding-top: .75rem; }

        /* ── Responsive: collapse sidebar on small screens ─────────────── */
        @media (max-width: 767.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #topbar  { left: 0; }
            #main-content { margin-left: 0; }
            #hamburger { display: block !important; }
        }
        #hamburger { display: none; background: none; border: none; font-size: 1.4rem; color: #374151; }
    </style>
    @stack('styles')
</head>
<body>

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<nav id="sidebar">
    <a class="sidebar-brand" href="#">
        <span class="icon-wrap"><i class="bi bi-diagram-3-fill text-white"></i></span>
        Flowline
    </a>

    @auth
        @php $role = Auth::user()->role?->role_name; @endphp

        {{-- ── ADMINISTRATOR ────────────────────────────────── --}}
        @if($role === 'Administrator')
            <span class="nav-section">Main</span>
            <a href="{{ route('admin.dashboard') }}"   class="nav-link @active('admin/dashboard')"><i class="bi bi-speedometer2"></i> Dashboard</a>

            <span class="nav-section">Organization</span>
            <a href="{{ route('admin.employees.index') }}" class="nav-link @active('admin/employees*')"><i class="bi bi-people-fill"></i> Employees</a>
            <a href="{{ route('admin.departments.index') }}" class="nav-link @active('admin/departments*')"><i class="bi bi-building"></i> Departments</a>
            <a href="{{ route('admin.positions.index') }}"   class="nav-link @active('admin/positions*')"><i class="bi bi-briefcase-fill"></i> Positions</a>

            <span class="nav-section">Workflow</span>
            <a href="{{ route('admin.tasks.index') }}"        class="nav-link @active('admin/tasks*')"><i class="bi bi-kanban-fill"></i> Task Board</a>
            <a href="{{ route('admin.attendance.index') }}"   class="nav-link @active('admin/attendance*')"><i class="bi bi-calendar-check-fill"></i> Attendance</a>
            <a href="{{ route('admin.files.index') }}"        class="nav-link @active('admin/files*')"><i class="bi bi-folder2-open"></i> Files</a>

            <span class="nav-section">Insights</span>
            <a href="{{ route('admin.reports.index') }}"      class="nav-link @active('admin/reports*')"><i class="bi bi-bar-chart-fill"></i> Reports</a>
            <a href="{{ route('admin.activity-logs.index') }}" class="nav-link @active('admin/activity-logs*')"><i class="bi bi-journal-text"></i> Audit Log</a>
            <a href="{{ route('admin.notifications.index') }}" class="nav-link @active('admin/notifications*')">
                <i class="bi bi-bell-fill"></i> Notifications
                @if(Auth::user()->unreadNotificationCount() > 0)
                    <span class="badge bg-danger ms-auto">{{ Auth::user()->unreadNotificationCount() }}</span>
                @endif
            </a>

        {{-- ── MANAGER ──────────────────────────────────────── --}}
        @elseif($role === 'Manager')
            <span class="nav-section">Main</span>
            <a href="{{ route('manager.dashboard') }}"  class="nav-link @active('manager/dashboard')"><i class="bi bi-speedometer2"></i> Dashboard</a>

            <span class="nav-section">Workflow</span>
            <a href="{{ route('manager.tasks.index') }}"      class="nav-link @active('manager/tasks*')"><i class="bi bi-kanban-fill"></i> Task Board</a>
            <a href="{{ route('manager.tasks.create') }}"     class="nav-link @active('manager/tasks/create')"><i class="bi bi-plus-circle-fill"></i> Assign Task</a>
            <a href="{{ route('manager.attendance.index') }}" class="nav-link @active('manager/attendance*')"><i class="bi bi-calendar-check-fill"></i> Attendance</a>
            <a href="{{ route('manager.files.index') }}"      class="nav-link @active('manager/files*')"><i class="bi bi-folder2-open"></i> Files</a>

            <span class="nav-section">Insights</span>
            <a href="{{ route('manager.reports.index') }}"    class="nav-link @active('manager/reports*')"><i class="bi bi-bar-chart-fill"></i> Reports</a>
            <a href="{{ route('manager.notifications.index') }}" class="nav-link @active('manager/notifications*')">
                <i class="bi bi-bell-fill"></i> Notifications
                @if(Auth::user()->unreadNotificationCount() > 0)
                    <span class="badge bg-danger ms-auto">{{ Auth::user()->unreadNotificationCount() }}</span>
                @endif
            </a>

        {{-- ── EMPLOYEE ─────────────────────────────────────── --}}
        @elseif($role === 'Employee')
            <span class="nav-section">Main</span>
            <a href="{{ route('employee.dashboard') }}"    class="nav-link @active('employee/dashboard')"><i class="bi bi-speedometer2"></i> Dashboard</a>

            <span class="nav-section">My Work</span>
            <a href="{{ route('employee.tasks.index') }}"  class="nav-link @active('employee/tasks*')"><i class="bi bi-kanban-fill"></i> My Tasks</a>
            <a href="{{ route('employee.attendance.index') }}" class="nav-link @active('employee/attendance*')"><i class="bi bi-calendar-check-fill"></i> Attendance</a>
            <a href="{{ route('employee.files.index') }}"  class="nav-link @active('employee/files*')"><i class="bi bi-folder2-open"></i> Files</a>
            <a href="{{ route('employee.notifications.index') }}" class="nav-link @active('employee/notifications*')">
                <i class="bi bi-bell-fill"></i> Notifications
                @if(Auth::user()->unreadNotificationCount() > 0)
                    <span class="badge bg-danger ms-auto">{{ Auth::user()->unreadNotificationCount() }}</span>
                @endif
            </a>
        @endif

        {{-- ── Shared logout ──────────────────────────────────── --}}
        <div class="sidebar-footer">
            <div class="user-pill">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->employee?->first_name ?? Auth::user()->username, 0, 1)) }}</div>
                <div class="user-info">
                    <div class="name">{{ Auth::user()->employee?->full_name ?? Auth::user()->username }}</div>
                    <div class="role">{{ $role ?? 'User' }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-sm p-0 ms-1" title="Logout" style="color:rgba(255,255,255,.45);">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    @endauth
</nav>

{{-- ═══════════════ TOPBAR ═══════════════ --}}
<header id="topbar">
    <div class="d-flex align-items-center gap-3">
        <button id="hamburger" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
        <span class="page-heading">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="topbar-right">
        @auth
        @php
            $notifRoute = match(Auth::user()->role?->role_name) {
                'Administrator' => 'admin.notifications.index',
                'Manager'       => 'manager.notifications.index',
                'Employee'      => 'employee.notifications.index',
                default         => null,
            };
        @endphp
        @if($notifRoute)
        <a href="{{ route($notifRoute) }}" class="notif-btn" title="Notifications">
            <i class="bi bi-bell"></i>
            @if(Auth::user()->unreadNotificationCount() > 0)
                <span class="notif-badge">{{ min(Auth::user()->unreadNotificationCount(), 9) }}</span>
            @endif
        </a>
        @endif
        @endauth
        <span class="text-muted small d-none d-md-inline">{{ now()->format('D, M j Y') }}</span>
    </div>
</header>

{{-- ═══════════════ MAIN CONTENT ═══════════════ --}}
<main id="main-content">
    {{-- Flash messages --}}
    <div class="flash-container">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 py-2 small alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ session('error') }}
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 small alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="content-body">
        @yield('content')
    </div>
</main>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mobile sidebar toggle
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    if (hamburger) {
        hamburger.addEventListener('click', () => sidebar.classList.toggle('open'));
    }
</script>
@stack('scripts')
</body>
</html>

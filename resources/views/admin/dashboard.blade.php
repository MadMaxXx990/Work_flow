@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="dashboard-header">
    <h1>Welcome, {{ Auth::user()->name ?? 'Admin' }}</h1>
</div>

<div class="stats-grid" style="display: flex; gap: 20px; margin-top: 20px;">
    <div class="stat-card" style="padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
        <h3>Total Employees</h3>
        <p style="font-size: 24px; font-weight: bold;">{{ $employeeCount ?? 0 }}</p>
    </div>
    <div class="stat-card" style="padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
        <h3>Active Tasks</h3>
        <p style="font-size: 24px; font-weight: bold;">{{ $activeTasks ?? 0 }}</p>
    </div>
</div>
@endsection

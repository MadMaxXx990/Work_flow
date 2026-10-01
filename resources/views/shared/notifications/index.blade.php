@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')

@php
    $rolePrefix = match(Auth::user()->role?->role_name) {
        'Administrator' => 'admin',
        'Manager'       => 'manager',
        default         => 'employee',
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted small mb-0">{{ $notifications->total() }} notification(s)</p>
    <form action="{{ route($rolePrefix . '.notifications.read-all') }}" method="POST">
        @csrf
        <button class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-check2-all me-1"></i> Mark all as read
        </button>
    </form>
</div>

<div class="card-fl">
    @forelse($notifications as $notif)
    @php
        // Use a plain array with a string 'default' key — no match() inside array literals
        $iconMap = [
            'assignment' => ['bi-person-plus-fill',       'primary'],
            'status'     => ['bi-arrow-repeat',            'info'],
            'approval'   => ['bi-check-circle-fill',       'success'],
            'deadline'   => ['bi-exclamation-circle-fill', 'danger'],
            'default'    => ['bi-bell-fill',               'secondary'],
        ];
        $iconKey        = array_key_exists($notif->notification_type, $iconMap)
                            ? $notif->notification_type
                            : 'default';
        [$icon, $color] = $iconMap[$iconKey];
    @endphp
    <div class="d-flex align-items-start gap-3 p-3 border-bottom {{ $notif->is_read ? '' : 'bg-primary-subtle' }}">
        <div class="rounded-circle d-grid flex-shrink-0"
             style="width:38px;height:38px;place-items:center;display:grid;background:var(--bs-{{ $color }}-bg-subtle,#eef2ff);">
            <i class="bi {{ $icon }} text-{{ $color }}"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-semibold small">{{ $notif->title }}</div>
            <div class="text-muted small">{{ $notif->message }}</div>
            <div class="text-muted mt-1" style="font-size:.7rem;">
                {{ $notif->created_at?->diffForHumans() }}
                @if(!$notif->is_read)
                    <span class="badge bg-primary ms-1">New</span>
                @endif
            </div>
        </div>
        @if($notif->reference_id)
        <a href="{{ route($rolePrefix . '.tasks.show', $notif->reference_id) }}"
           class="btn btn-sm btn-outline-secondary py-0 px-2 flex-shrink-0">
            View
        </a>
        @endif
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-bell-slash fs-2 d-block mb-2 opacity-50"></i>
        No notifications yet.
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="mt-3 d-flex justify-content-center">
    {{ $notifications->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection

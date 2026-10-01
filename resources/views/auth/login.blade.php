<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flowline — Log in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #4f46e5;
            --brand-dark: #3730a3;
        }
        body {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8ecff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            border: 1px solid rgba(79,70,229,.12);
            box-shadow: 0 8px 32px rgba(79,70,229,.10);
            background: #fff;
            padding: 2.5rem 2.5rem 2rem;
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: var(--brand);
            color: #fff;
            border-radius: 10px;
            padding: .35rem .75rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: .5px;
            margin-bottom: 1.5rem;
        }
        .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 .2rem rgba(79,70,229,.2);
        }
        .btn-brand {
            background: var(--brand);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .65rem;
            border-radius: 8px;
            transition: background .2s;
        }
        .btn-brand:hover { background: var(--brand-dark); color: #fff; }
        .input-group-text { background: #f8f9ff; border-right: none; }
        .form-control { border-left: none; }
        .form-control:not(:focus) { border-color: #dee2e6; }
        label.form-label { font-size: .82rem; font-weight: 600; color: #374151; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-badge">
            <i class="bi bi-diagram-3-fill"></i> Flowline
        </div>

        <h5 class="fw-bold mb-1">Welcome back</h5>
        <p class="text-muted small mb-4">Sign in to your account to continue.</p>

        {{-- Error banner --}}
        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small" role="alert">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        <form action="{{ url('/login') }}" method="POST" novalidate>
            @csrf

            {{-- Username --}}
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                    <input
                        id="username"
                        type="text"
                        name="username"
                        class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        autofocus
                        required
                        placeholder="your.username"
                    >
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control"
                        autocomplete="current-password"
                        required
                        placeholder="••••••••"
                    >
                </div>
            </div>

            {{-- Remember me --}}
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label small text-muted" for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn btn-brand w-100">
                Sign in <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>
    </div>
</body>
</html>

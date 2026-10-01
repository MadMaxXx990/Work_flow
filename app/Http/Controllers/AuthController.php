<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Reject soft-deleted accounts before attempting auth
        $user = \App\Models\User::where('username', $credentials['username'])
            ->where('is_deleted', false)
            ->first();

        if (!$user) {
            return back()->withErrors(['username' => 'These credentials do not match our records.'])->withInput();
        }

        if ($user->status === 'inactive') {
            return back()->withErrors(['username' => 'Your account has been deactivated. Contact an administrator.'])->withInput();
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login & reset failed attempts
            $user = Auth::user();
            $user->update(['last_login' => now(), 'failed_login_attempts' => 0]);

            ActivityLogService::log('login', 'User logged in.', 'users', $user->id, $user->id);

            return $this->redirectByRole($user);
        }

        // Increment failed login counter
        if ($user) {
            $user->increment('failed_login_attempts');
        }

        return back()->withErrors(['username' => 'These credentials do not match our records.'])->withInput();
    }

    public function logout(Request $request)
    {
        ActivityLogService::log('logout', 'User logged out.', 'users', Auth::id());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function redirectByRole(\App\Models\User $user)
    {
        return match ($user->role?->role_name) {
            'Administrator' => redirect()->route('admin.dashboard'),
            'Manager'       => redirect()->route('manager.dashboard'),
            'Employee'      => redirect()->route('employee.dashboard'),
            default         => redirect()->route('login'),
        };
    }
}

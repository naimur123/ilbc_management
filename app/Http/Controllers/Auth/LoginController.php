<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request, AuditLogService $auditLog)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Your account has been deactivated. Contact your administrator.']);
        }

        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);
        $auditLog->record('Auth', 'User logged in');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request, AuditLogService $auditLog)
    {
        $auditLog->record('Auth', 'User logged out');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

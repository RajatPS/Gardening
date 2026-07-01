<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($validated)) {
            $user = Auth::user();
            
            // Check if user is admin or staff
            if ($user->role !== 'admin' && $user->role !== 'staff') {
                Auth::logout();
                return redirect()->route('admin.login')->withErrors('Unauthorized access');
            }

            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login')->withErrors('Invalid credentials');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }

    public function forgotPasswordForm()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users'
        ]);

        // TODO: Implement password reset email
        
        return redirect()->back()->with('status', 'Password reset link sent to your email');
    }
}

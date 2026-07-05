<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
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

    public function registerForm()
    {
        return view('admin.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'password' => $validated['password'],
            'role' => 'admin',
            'status' => 'active',
            'user_type' => 'admin',
        ]);

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }

    public function forgotPasswordForm()
    {
        return view('admin.login');
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

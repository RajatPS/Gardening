<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class CustomerAuthController extends Controller
{
    public function googleRedirect(Request $request)
    {
        $request->session()->put('oauth_redirect', $request->query('redirect', route('customer.profile')));

        $redirectResponse = Socialite::driver('google')->redirect();
        $redirectUrl = method_exists($redirectResponse, 'getTargetUrl') ? $redirectResponse->getTargetUrl() : null;

        Log::info('Google OAuth redirect URL', [
            'configured_redirect' => config('services.google.redirect'),
            'app_url' => config('app.url'),
            'redirect_url' => $redirectUrl,
            'env_redirect' => env('GOOGLE_REDIRECT_URI'),
        ]);

        return $redirectResponse;
    }

    public function googleCallback(Request $request)
    {
        Log::info('Google OAuth callback received', [
            'request_url' => $request->fullUrl(),
            'configured_redirect' => config('services.google.redirect'),
            'app_url' => config('app.url'),
            'env_redirect' => env('GOOGLE_REDIRECT_URI'),
            'query' => $request->query(),
        ]);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('customer.login')->with('error', 'Google sign-in failed. Please try again or use email sign-in.');
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            Log::error('Google OAuth callback missing email', [
                'user' => $googleUser->toArray(),
            ]);

            return redirect()->route('customer.login')->with('error', 'Google did not return your email address.');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickName() ?? 'Google User',
                'email' => $email,
                'password' => Hash::make(str()->random(24)),
                'role' => 'customer',
                'status' => 'active',
                'user_type' => 'customer',
            ]);
        }

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        return redirect($request->session()->get('oauth_redirect', route('customer.profile')));
    }

    public function loginForm(Request $request)
    {
        return view('customer.auth.login', [
            'redirect' => $request->query('redirect', route('customer.profile')),
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($validated, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect($request->input('redirect', route('customer.profile')));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function registerForm(Request $request)
    {
        return view('customer.auth.register', [
            'redirect' => $request->query('redirect', route('customer.profile')),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'status' => 'active',
            'user_type' => 'customer',
        ]);

        Auth::login($user);

        return redirect($request->input('redirect', route('customer.profile')));
    }
}

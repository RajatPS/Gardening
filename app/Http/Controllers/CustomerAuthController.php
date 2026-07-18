<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
                'must_change_password' => false,
            ]);
        } else {
            $user->forceFill([
                'name' => $googleUser->getName() ?? $user->name ?? 'Google User',
                'email' => $email,
                'status' => $user->status ?? 'active',
                'role' => $user->role ?? 'customer',
                'user_type' => $user->user_type ?? 'customer',
            ])->save();
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

            $user = Auth::user();

            if ($user && $user->shouldRequirePasswordChange()) {
                $user->forceFill(['must_change_password' => true])->save();

                return redirect()->route('customer.change-password')->with('status', 'Please choose a new password before continuing.');
            }

            return redirect($request->input('redirect', route('customer.profile')));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
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
            'password' => $validated['password'],
            'role' => 'customer',
            'status' => 'active',
            'user_type' => 'customer',
            'must_change_password' => false,
        ]);

        Auth::login($user);

        return redirect($request->input('redirect', route('customer.profile')));
    }

    public function forgotPasswordForm()
    {
        return view('customer.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        if ($request->filled('otp') && $request->filled('password')) {
            return $this->completePasswordReset($request);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', 'max:6'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No account was found for that email address.']);
        }

        $phone = $validated['phone'] ?: $user->phone;

        if (empty($phone)) {
            return back()->withErrors(['phone' => 'Please provide a phone number so we can send the verification code.']);
        }

        $normalizedPhone = $this->normalizePhoneNumber($phone, $validated['country_code'] ?? null);
        $user->forceFill(['phone' => $normalizedPhone])->save();

        $otp = random_int(100000, 999999);
        Cache::put('password_reset_otp_' . $normalizedPhone, $otp, now()->addMinutes(10));
        Cache::put('password_reset_user_' . $normalizedPhone, $user->id, now()->addMinutes(10));

        $this->sendTwilioOtp($normalizedPhone, $otp);

        session()->put('password_reset_pending', ['email' => $user->email, 'phone' => $normalizedPhone]);

        return back()->with('status', 'A verification code has been sent to your phone number.');
    }

    public function changePasswordForm()
    {
        return view('customer.auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('customer.login');
        }

        $user->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        return redirect()->route('customer.profile')->with('status', 'Password updated successfully.');
    }

    public function resetPasswordForm()
    {
        if (! session('password_reset_pending')) {
            return redirect()->route('customer.forgot-password');
        }

        return view('customer.auth.reset-password', [
            'email' => session('password_reset_pending.email'),
            'phone' => session('password_reset_pending.phone'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        return $this->completePasswordReset($request);
    }

    private function completePasswordReset(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $pending = session('password_reset_pending');
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! $pending || ($pending['email'] ?? null) !== $validated['email']) {
            return back()->withErrors(['email' => 'The reset session is invalid. Please request a new code.']);
        }

        $phone = $pending['phone'] ?? $user->phone;
        $storedOtp = Cache::get('password_reset_otp_' . $phone);

        if ((string) $storedOtp !== (string) $validated['otp']) {
            return back()->withErrors(['otp' => 'Invalid or expired verification code.']);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        Cache::forget('password_reset_otp_' . $phone);
        Cache::forget('password_reset_user_' . $phone);
        session()->forget('password_reset_pending');

        return redirect()->route('customer.login')->with('status', 'Password reset successfully. Please sign in with your new password.');
    }

    private function sendTwilioOtp(string $phone, int $otp): void
    {
        $sid = config('services.twilio.sid', '');
        $token = config('services.twilio.token', '');
        $from = config('services.twilio.from', '');

        if ($sid === '' || $token === '' || $from === '') {
            throw new \RuntimeException('Twilio credentials are missing. Please check TWILIO_SID, TWILIO_AUTH_TOKEN, and TWILIO_FROM in your .env file.');
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $phone,
                'Body' => 'Your GardenHub password reset OTP is ' . $otp,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Twilio rejected the SMS request.');
        }
    }

    private function normalizePhoneNumber(string $phone, ?string $countryCode = null): string
    {
        $rawPhone = preg_replace('/\D+/', '', $phone) ?? '';

        if ($rawPhone === '') {
            throw new \InvalidArgumentException('Please enter a valid phone number.');
        }

        if ($countryCode !== null && $countryCode !== '') {
            $countryCode = ltrim($countryCode, '+');
            return '+' . $countryCode . $rawPhone;
        }

        return '+' . $rawPhone;
    }
}

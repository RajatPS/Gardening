<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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
            return $this->handleSuccessfulAdminLogin($request);
        }

        $user = User::where('email', $validated['email'])->where('role', 'admin')->first();

        if ($user && $this->canLoginWithDefaultPassword($user, $validated['password'])) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return $this->handleSuccessfulAdminLogin($request);
        }

        return redirect()->route('admin.login')->withErrors('Invalid credentials');
    }

    private function handleSuccessfulAdminLogin(Request $request)
    {
        $user = Auth::user();

        if (! $user || ($user->role !== 'admin' && $user->role !== 'staff')) {
            Auth::logout();
            return redirect()->route('admin.login')->withErrors('Unauthorized access');
        }

        if ($user->shouldRequirePasswordChange()) {
            $user->forceFill(['must_change_password' => true])->save();

            return redirect()->route('admin.change-password')->with('status', 'Please choose a new password before continuing.');
        }

        return redirect()->route('admin.dashboard');
    }

    private function canLoginWithDefaultPassword(User $user, string $password): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        if (Hash::check($password, $user->password)) {
            return true;
        }

        return $password === User::DEFAULT_PASSWORD && ($user->password === User::DEFAULT_PASSWORD || Hash::check(User::DEFAULT_PASSWORD, $user->password));
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
        return view('admin.forgot-password');
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
        return view('admin.change-password');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        $user->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        return redirect()->route('admin.dashboard')->with('status', 'Password updated successfully.');
    }

    public function resetPasswordForm()
    {
        if (! session('password_reset_pending')) {
            return redirect()->route('admin.forgot-password');
        }

        return view('admin.reset-password', [
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

        return redirect()->route('admin.login')->with('status', 'Password reset successfully. Please sign in with your new password.');
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

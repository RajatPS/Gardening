<?php

namespace App\Http\Controllers\Staff;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;

class StaffAuthController extends Controller
{
    public function loginForm()
    {
        return view('staff.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($validated)) {
            $user = Auth::user();

            if ($user->role !== 'staff') {
                Auth::logout();
                return redirect()->route('staff.login')->withErrors('Only staff accounts can access this panel');
            }

            if ($user->shouldRequirePasswordChange()) {
                $user->forceFill(['must_change_password' => true])->save();

                return redirect()->route('staff.change-password')->with('status', 'Please choose a new password before continuing.');
            }

            return redirect()->route('staff.dashboard');
        }

        return redirect()->route('staff.login')->withErrors('Invalid credentials');
    }

    public function registerForm()
    {
        return view('staff.register');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|max:6',
        ]);

        $otp = random_int(100000, 999999);
        $phone = $this->normalizePhoneNumber($request->input('phone'), $request->input('country_code'));

        Cache::put('staff_otp_' . $phone, $otp, now()->addMinutes(10));

        try {
            $this->sendTwilioOtp($phone, $otp);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'phone' => $phone,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|digits:6',
        ]);

        $phone = $request->input('phone');
        $otp = $request->input('otp');
        $storedOtp = Cache::get('staff_otp_' . $phone);

        if ((string) $storedOtp !== (string) $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }

        Cache::forget('staff_otp_' . $phone);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|max:6',
            'address' => 'nullable|string|max:500',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $phone = $this->normalizePhoneNumber($validated['phone'], $validated['country_code']);
        $storedOtp = Cache::get('staff_otp_' . $phone);
        if ((string) $storedOtp !== (string) $validated['otp']) {
            return redirect()->back()->withErrors('Invalid or expired OTP')->withInput();
        }

        Cache::forget('staff_otp_' . $phone);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'address' => $validated['address'] ?? null,
            'password' => $validated['password'],
            'role' => 'staff',
            'status' => 'active',
            'user_type' => 'staff',
            'must_change_password' => false,
        ]);

        Auth::login($user);

        return redirect()->route('staff.dashboard');
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
                'Body' => 'Your GardenHub staff OTP is ' . $otp,
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $message = $body['message'] ?? $response->body();

            throw new \RuntimeException($message ?: 'Twilio rejected the SMS request.');
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

    public function forgotPasswordForm()
    {
        return view('staff.forgot-password');
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
        return view('staff.change-password');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('staff.login');
        }

        $user->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        return redirect()->route('staff.dashboard')->with('status', 'Password updated successfully.');
    }

    public function resetPasswordForm()
    {
        if (! session('password_reset_pending')) {
            return redirect()->route('staff.forgot-password');
        }

        return view('staff.reset-password', [
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

        return redirect()->route('staff.login')->with('status', 'Password reset successfully. Please sign in with your new password.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('staff.login');
    }
}

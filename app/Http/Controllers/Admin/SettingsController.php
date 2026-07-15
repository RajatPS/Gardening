<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function generalSettings()
    {
        return view('admin.settings.general');
    }

    public function updateGeneralSettings(Request $request)
    {
        $validated = $request->validate([
            'website_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:20',
            'logo' => 'nullable|image|max:2048',
        ]);

        // TODO: Update settings in database or config

        return redirect()->back()->with('success', 'General settings updated successfully');
    }

    public function paymentSettings()
    {
        return view('admin.settings.payment');
    }

    public function updatePaymentSettings(Request $request)
    {
        $validated = $request->validate([
            'razorpay_key' => 'nullable|string',
            'razorpay_secret' => 'nullable|string',
            'phonepe_key' => 'nullable|string',
            'phonepe_secret' => 'nullable|string',
        ]);

        // TODO: Update payment settings

        return redirect()->back()->with('success', 'Payment settings updated successfully');
    }

    public function notificationSettings()
    {
        return view('admin.settings.notification');
    }

    public function updateNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'push_enabled' => 'boolean',
        ]);

        // TODO: Update notification settings

        return redirect()->back()->with('success', 'Notification settings updated successfully');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.settings.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()->withErrors('Current password is incorrect');
        }

        $user->update(['password' => bcrypt($validated['new_password'])]);

        return redirect()->back()->with('success', 'Password changed successfully');
    }
}

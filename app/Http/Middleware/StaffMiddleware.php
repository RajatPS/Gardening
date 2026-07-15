<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('staff.login');
        }

        $user = auth()->user();

        if ($user->role !== 'staff') {
            abort(403, 'Unauthorized access to staff panel');
        }

        return $next($request);
    }
}

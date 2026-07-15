<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = auth()->user();
        
        // Check if user has admin role
        if ($user->role !== 'admin' && $user->role !== 'staff') {
            abort(403, 'Unauthorized access to admin panel');
        }

        return $next($request);
    }
}

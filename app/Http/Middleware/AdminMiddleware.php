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
        
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized access to admin panel');
        }

        return $next($request);
    }
}

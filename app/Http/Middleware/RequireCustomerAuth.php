<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireCustomerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->guest(route('customer.login', ['redirect' => $request->fullUrl()]))
                ->with('error', 'Please log in or create an account to continue.');
        }

        if (Auth::user()?->role !== 'customer') {
            abort(403, 'Unauthorized access to customer area');
        }

        return $next($request);
    }
}

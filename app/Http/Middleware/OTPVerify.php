<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OTPVerify
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip OTP verification for login and verify-otp routes
        if ($request->is('login') || $request->is('verify-otp')) {
            return $next($request);
        }

        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // If OTP verification is required and user is not on verify page
        if (session()->get('requires_otp', false)) {
            return redirect()->route('verify');
        }

        return $next($request);
    }
}

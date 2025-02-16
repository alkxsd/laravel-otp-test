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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $requiresOTP = session()->get('requires_otp', false);
        $isVerifyRoute = $request->routeIs('verify');

        // If OTP is required and user is not on verify page
        if ($requiresOTP && !$isVerifyRoute) {
            return redirect()->route('verify');
        }

        // If OTP is not required and user tries to access verify page
        if (!$requiresOTP && $isVerifyRoute) {
            return redirect()->route('welcome');
        }

        return $next($request);
    }
}

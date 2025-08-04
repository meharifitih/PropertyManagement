<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class Verify2FA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Not authenticated => no need to check
        if (!Auth::check()) {
            return $next($request);
        }

        // 2FA not enabled => no need to check
        if (is_null(Auth::user()->twofa_secret)) {
            return $next($request);
        }

        // 2FA is already checked
        if (session("2fa_checked", false)) {
            return $next($request);
        }

        // Define routes that should be accessible without 2FA
        $allowedRoutes = [
            'logout',
            'otp.show',
            'otp.check',
            '2fa.disable',
            'dashboard',
            'home',
            'account.review'
        ];

        $currentRoute = $request->route() ? $request->route()->getName() : null;
        $currentPath = $request->path();

        // Check if current route is allowed without 2FA
        if (in_array($currentRoute, $allowedRoutes) || 
            $currentPath === 'login/otp' ||
            $request->is('login/otp*') ||
            $request->is('otp*') ||
            $request->is('2fa*')) {
            return $next($request);
        }

        // Prevent redirect loop by checking if we're already on OTP page
        if ($request->is('login/otp*') || $currentRoute === 'otp.show') {
            return $next($request);
        }

        // at this point user must provide a valid OTP
        return redirect()->route('otp.show');
    }
}

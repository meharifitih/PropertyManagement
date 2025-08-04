<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $excludedRoutes = [
            'login',
            'register', 
            'password.request',
            'password.reset',
            'password.email',
            'install',
            'logout',
            'home',
            'dashboard',
            'account.review',
            'otp.show',
            'otp.check',
            '2fa.disable',
            'subscriptions.index',
            'subscriptions.show',
            'subscriptions.store',
            'subscriptions.subscribe',
            'subscription.bank.transfer',
            'subscription.stripe.payment',
            'subscription.paypal',
            'subscription.flutterwave',
            'payment.verification.upload',
            'subscription.transaction',
            'setting.index',
            'setting.account',
            'setting.password',
            'setting.general',
            'setting.smtp',
            'setting.payment',
            'setting.site.seo',
            'setting.google.recaptcha',
            'setting.company',
            'setting.twofa.enable',
            'setting.tutorial_videos',
            'setting.footer',
            'theme.settings',
            'setting.smtp.test',
            'setting.smtp.testing',
            'language.change',
            'footerSetting',
            '/'
        ];

        $currentRoute = $request->route() ? $request->route()->getName() : null;
        $currentPath = $request->path();
        
        // Check if current route or path is excluded
        if (in_array($currentRoute, $excludedRoutes) || 
            in_array($currentPath, $excludedRoutes) || 
            $request->is('/') ||
            $request->is('login*') ||
            $request->is('register*') ||
            $request->is('password*') ||
            $request->is('install*') ||
            $request->is('otp*') ||
            $request->is('2fa*')) {
            return $next($request);
        }

        if (!Auth::check()) {
            Log::info('SessionTimeout: User not authenticated, redirecting to login');
            
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Session expired. Please login again.',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        }

        $user = Auth::user();
        
        if (!$user) {
            Log::info('SessionTimeout: Invalid user object, redirecting to login');
            Auth::logout();
            
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Session expired. Please login again.',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        }

        if (!$user->is_active) {
            Log::info('SessionTimeout: User is inactive, redirecting to login');
            Auth::logout();
            
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Your account has been deactivated. Please contact administrator.',
                    'redirect' => route('login')
                ], 403);
            }
            
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact administrator.');
        }

        return $next($request);
    }
}

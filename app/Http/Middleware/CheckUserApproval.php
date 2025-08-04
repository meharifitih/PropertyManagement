<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckUserApproval
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Allow super admin to access everything
            if ($user->type === 'super admin') {
                return $next($request);
            }
            
            // Allow non-owner users (tenants, maintainers) to access everything
            if (!in_array($user->type, ['owner', 'super admin'])) {
                return $next($request);
            }
            
            // For owner users, check approval status
            if ($user->approval_status !== 'approved') {
                // Allow access to essential routes
                $allowedRoutes = [
                    // Core pages
                    'dashboard',
                    'home',
                    
                    // Authentication & Profile
                    'logout',
                    'profile.edit',
                    'profile.update',
                    'password.update',
                    
                    // Account Review & Approval
                    'account.review',
                    
                    // Subscription & Payment
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
                    
                    // Settings (all settings routes)
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
                    
                    // Language
                    'language.change',
                    
                    // Footer
                    'footerSetting',
                    
                    // OTP/2FA
                    'otp.show',
                    'otp.check',
                    '2fa.disable',
                ];
                
                $currentRoute = $request->route() ? $request->route()->getName() : null;
                $currentPath = $request->path();
                
                // Log for debugging
                Log::info('CheckUserApproval middleware', [
                    'user_id' => $user->id,
                    'user_type' => $user->type,
                    'approval_status' => $user->approval_status,
                    'current_route' => $currentRoute,
                    'current_path' => $currentPath,
                    'url' => $request->fullUrl(),
                    'is_allowed' => in_array($currentRoute, $allowedRoutes)
                ]);
                
                // Check if current route is allowed or if we're already on account.review
                if (in_array($currentRoute, $allowedRoutes) || 
                    $currentPath === 'account/review' ||
                    $request->is('account/review*')) {
                    return $next($request);
                }
                
                // Prevent redirect loop by checking if we're already redirecting
                if ($request->is('account/review*') || $currentRoute === 'account.review') {
                    return $next($request);
                }
                
                // Redirect to review page for all other routes
                return redirect()->route('account.review');
            }
        }
        
        return $next($request);
    }
} 
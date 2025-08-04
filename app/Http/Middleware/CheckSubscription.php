<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Allow super admin to access everything
            if ($user->type === 'super admin') {
                return $next($request);
            }

            // Skip subscription check for tenants and maintainers
            if (!in_array($user->type, ['owner', 'super admin'])) {
                return $next($request);
            }

            // Define allowed routes for unapproved users
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
            Log::info('CheckSubscription middleware', [
                'user_id' => $user->id,
                'user_type' => $user->type,
                'approval_status' => $user->approval_status,
                'current_route' => $currentRoute,
                'current_path' => $currentPath,
                'url' => $request->fullUrl(),
                'is_allowed' => in_array($currentRoute, $allowedRoutes)
            ]);

            // Only block access to non-allowed routes if not approved
            if ($user->approval_status !== 'approved') {
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
                
                return redirect()->route('account.review')
                    ->with('error', __('Your account is pending approval. Please select and pay for a subscription.'));
            }
        }

        return $next($request);
    }
} 
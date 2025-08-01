<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckOwnerDocuments
{
    public function handle(Request $request, Closure $next)
    {
        if (
            Auth::check() &&
            Auth::user()->type === 'owner' &&
            empty(Auth::user()->business_license)
        ) {
            // Allow access to the settings/profile page so user can upload
            $allowedSettingsRoutes = [
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
                'footerSetting',
                'language.change',
            ];
            
            $currentRoute = $request->route() ? $request->route()->getName() : null;
            
            // Log for debugging
            Log::info('CheckOwnerDocuments middleware', [
                'user_id' => Auth::user()->id,
                'user_type' => Auth::user()->type,
                'business_license' => Auth::user()->business_license,
                'current_route' => $currentRoute,
                'url' => $request->fullUrl(),
                'is_allowed' => in_array($currentRoute, $allowedSettingsRoutes)
            ]);
            
            if (!in_array($currentRoute, $allowedSettingsRoutes)) {
                return redirect()->route('setting.index', ['tab' => 'user_profile_settings'])
                    ->with('error', __('Please upload your business license to continue using the system.'));
            }
        }

        return $next($request);
    }
} 
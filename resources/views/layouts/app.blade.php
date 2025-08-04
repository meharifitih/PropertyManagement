<!doctype html>
@php
    $settings = settings();

@endphp
<html lang="en">
<!-- [Head] start -->
@include('admin.head')

<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="{{ !empty($settings['color_type']) && $settings['color_type'] == 'custom' ? 'custom' : $settings['accent_color'] }}" data-pc-sidebar-theme="light"
    data-pc-sidebar-caption="{{ $settings['sidebar_caption'] }}" data-pc-direction="{{ $settings['theme_layout'] }}"
    data-pc-theme="{{ $settings['theme_mode'] }}">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ Sidebar Menu ] start -->
    @include('admin.menu')
    <!-- [ Sidebar Menu ] end -->
    <!-- [ Header Topbar ] start -->
    @include('admin.header')
    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            @php
                $showBanner = !session('user_type_banner_shown');
                if ($showBanner) session(['user_type_banner_shown' => true]);
            @endphp
            @if($showBanner)
                @php
                    $userType = strtolower(Auth::user()->type ?? '');
                    $typeLabel = $userType === 'owner' ? 'OWNER PAGE' : ($userType === 'tenant' ? 'TENANT PAGE' : ($userType === 'super admin' ? 'SUPER ADMIN PAGE' : strtoupper($userType) . ' PAGE'));
                @endphp
                <div id="user-type-banner" style="text-align:center; margin: 30px 0 20px 0; transition: opacity 0.7s;">
                    <span style="font-size:2.2rem; font-weight:900; letter-spacing:2px; color:#2d3748; text-transform:uppercase; display:inline-block; padding:12px 32px; border-radius:8px; background:#f1f5f9; box-shadow:0 2px 8px #e2e8f0;">
                        {{ $typeLabel }}
                    </span>
                </div>
                <script>
                    setTimeout(function() {
                        var banner = document.getElementById('user-type-banner');
                        if (banner) {
                            banner.style.opacity = 0;
                            setTimeout(function() { banner.remove(); }, 700);
                        }
                    }, 30000); // 30 seconds
                </script>
            @endif
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="page-header-title">
                                <h5 class="m-b-10"> @yield('page-title')</h5>
                            </div>
                        </div>
                        <div class="col-auto">
                            <ul class="breadcrumb">
                                @yield('breadcrumb')
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->


            <!-- [ Main Content ] start -->
            @include('admin.content')

            <!-- [ Main Content ] end -->
        </div>
    </div>

    <!-- [ Main Content ] end -->
    @include('admin.footer')

    @stack('script-page')

    <div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Session Timeout Auto Logout -->
    <script>
        (function() {
            // Session timeout based on SESSION_LIFETIME config
            const sessionLifetime = {{ config('session.lifetime', 120) }} * 60 * 1000; // Convert to milliseconds
            
            // Auto logout when session expires
            setTimeout(function() {
                window.location.href = '{{ route("login") }}?expired=1';
            }, sessionLifetime);
        })();
    </script>
    @stack('scripts')
</body>
<!-- [Body] end -->

</html>

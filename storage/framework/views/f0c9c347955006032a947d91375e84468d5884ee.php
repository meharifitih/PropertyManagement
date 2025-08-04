<!doctype html>
<?php
    $settings = settings();

?>
<html lang="en">
<!-- [Head] start -->
<?php echo $__env->make('admin.head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="<?php echo e(!empty($settings['color_type']) && $settings['color_type'] == 'custom' ? 'custom' : $settings['accent_color']); ?>" data-pc-sidebar-theme="light"
    data-pc-sidebar-caption="<?php echo e($settings['sidebar_caption']); ?>" data-pc-direction="<?php echo e($settings['theme_layout']); ?>"
    data-pc-theme="<?php echo e($settings['theme_mode']); ?>">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ Sidebar Menu ] start -->
    <?php echo $__env->make('admin.menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- [ Sidebar Menu ] end -->
    <!-- [ Header Topbar ] start -->
    <?php echo $__env->make('admin.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <?php
                $showBanner = !session('user_type_banner_shown');
                if ($showBanner) session(['user_type_banner_shown' => true]);
            ?>
            <?php if($showBanner): ?>
                <?php
                    $userType = strtolower(Auth::user()->type ?? '');
                    $typeLabel = $userType === 'owner' ? 'OWNER PAGE' : ($userType === 'tenant' ? 'TENANT PAGE' : ($userType === 'super admin' ? 'SUPER ADMIN PAGE' : strtoupper($userType) . ' PAGE'));
                ?>
                <div id="user-type-banner" style="text-align:center; margin: 30px 0 20px 0; transition: opacity 0.7s;">
                    <span style="font-size:2.2rem; font-weight:900; letter-spacing:2px; color:#2d3748; text-transform:uppercase; display:inline-block; padding:12px 32px; border-radius:8px; background:#f1f5f9; box-shadow:0 2px 8px #e2e8f0;">
                        <?php echo e($typeLabel); ?>

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
            <?php endif; ?>
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="page-header-title">
                                <h5 class="m-b-10"> <?php echo $__env->yieldContent('page-title'); ?></h5>
                            </div>
                        </div>
                        <div class="col-auto">
                            <ul class="breadcrumb">
                                <?php echo $__env->yieldContent('breadcrumb'); ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->


            <!-- [ Main Content ] start -->
            <?php echo $__env->make('admin.content', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- [ Main Content ] end -->
        </div>
    </div>

    <!-- [ Main Content ] end -->
    <?php echo $__env->make('admin.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->yieldPushContent('script-page'); ?>

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
            const sessionLifetime = <?php echo e(config('session.lifetime', 120)); ?> * 60 * 1000; // Convert to milliseconds
            
            // Auto logout when session expires
            setTimeout(function() {
                window.location.href = '<?php echo e(route("login")); ?>?expired=1';
            }, sessionLifetime);
        })();
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
<!-- [Body] end -->

</html>
<?php /**PATH /Users/chipchip/Downloads/codecanyon-ytuZNl0y-smart-tenant-property-management-system-saas/main_file/resources/views/layouts/app.blade.php ENDPATH**/ ?>
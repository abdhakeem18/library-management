</div>
<!-- / Layout page -->
</div>

<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout wrapper -->

<!-- Core JS -->
<!-- build:js vendor/js/core.js -->
<script src="<?= asset('vendor/libs/popper/popper.js') ?>"></script>
<script src="<?= asset('vendor/js/bootstrap.js') ?>"></script>
<script src="<?= asset('vendor/libs/perfect-scrollbar/perfect-scrollbar.js') ?>"></script>

<script src="<?= asset('vendor/js/menu.js') ?>"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="<?= asset('vendor/libs/apex-charts/apexcharts.js') ?>"></script>

<!-- Main JS -->
<script src="<?= asset('js/main.js') ?>"></script>

<!-- Page JS -->
<script src="<?= asset('js/dashboards-analytics.js') ?>"></script>

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

<!-- Toastr -->
<script src="<?= asset('plugins/toastr/toastr.min.js') ?>"></script>

<script>
    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    <?php
    if (!empty($session->getAttribute("ts-status"))) {
        $status = $session->getAttribute("ts-status");
        $message = $session->getAttribute("ts-message");
    ?>
        toastr['<?= $status ?>']('<?= $message ?>');
    <?php
        $session->removeAttribute("ts-status");
    }
    ?>
</script>
</body>

</html>
<?php

if (!defined('ENTRY_POINT')) {
    http_response_code(403);
    exit('Forbidden: Direct access is not allowed.');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include BASE_PATH . "/views/layouts/header.php"; ?>
    <script src="<?= asset('vendor/libs/jquery/jquery.js') ?>"></script>

</head>

<body class="hold-transition sidebar-mini layout-fixed">

    <?php content(); ?>

    <?php include BASE_PATH . "/views/layouts/footer.php"; ?>

</body>

</html>
<?php
$sm = AppManager::getSM();

$userId = $sm->getAttribute("userId");
$username = $sm->getAttribute("username");
$permission = $sm->getAttribute("permission");

if (!isset($username)) {
    // print_r($path/index.php);die;

    header("location: /login");
}

// Extract the last filename from the URL
$currentUrl = $_SERVER['SCRIPT_NAME'];
$currentFilename = basename($currentUrl);  // e.g., "dashboard.php"

?>

<!DOCTYPE html>
<!-- beautify ignore:start -->
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path=""
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Digital_Library</title>

    <meta name="description" content="" />
    <meta name="domain" content="<?= current_domain() ?>" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('img/favicon/favicon.ico') ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="<?= asset('vendor/fonts/boxicons.css') ?>" />
    <link rel="stylesheet" href="<?= asset('plugins/toastr/toastr.min.css') ?>" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= asset('vendor/css/core.css') ?>" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?= asset('vendor/css/theme-default.css') ?>" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?= asset('css/demo.css') ?>" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?= asset('vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>" />

    <link rel="stylesheet" href="<?= asset('vendor/libs/apex-charts/apex-charts.css') ?>" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="<?= asset('vendor/js/helpers.js') ?>"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="<?= asset('js/config.js') ?>"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="index.html" class="app-brand-link">
                        <span class="app-brand-logo demo">

                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2 text-capitalize">Library</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">

                    <li class="menu-item <?= $currentFilename === "dashboard.php" ? 'active' : '' ?> ">
                        <a href="<?= url('') ?>" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="appointments">Dashboard</div>
                        </a>
                    </li>


                    <?php if ($permission == 'operator') : ?>
                        <li class="menu-item <?= $currentFilename === "available_channelings.php" ? 'active' : '' ?>">
                            <a href="<?= url('borrowed_books') ?>" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-run"></i>
                                <div data-i18n="Analytics">borrowed_books</div>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="menu-item <?= $currentFilename === "borrowing.php" ? 'active' : '' ?> ">
                        <a href="<?= url('borrowing') ?>" class="menu-link">
                            <i class="menu-icon tf-icons  bx bx-collection"></i>
                            <div data-i18n="Analytics">borrowing</div>
                        </a>
                    </li>

                    <li class="menu-item <?= $currentFilename === "reservations.php" ? 'active' : '' ?> ">
                        <a href="<?= url('reservations') ?>" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                            <div data-i18n="Analytics">Reservations</div>
                        </a>
                    </li>

                    <?php if ($permission == 'admin') : ?>
                        <li class="menu-item <?= $currentFilename === "fine_fee.php" ? 'active' : '' ?> ">
                            <a href="<?= url('fine') ?>" class="menu-link">
                                <i class="menu-icon tf-icons  bx bx-plus-medical"></i>
                                <div data-i18n="Analytics">fine_fee</div>
                            </a>
                        </li>

                        <li class="menu-item <?= $currentFilename === "book.php" ? 'active' : '' ?> ">
                            <a href="<?= url('books') ?>" class="menu-link">
                                <i class="menu-icon tf-icons  bx bx-dna"></i>
                                <div data-i18n="Analytics">book</div>
                            </a>
                        </li>
                        <li class="menu-item <?= $currentFilename === "payment.php" ? 'active' : '' ?> ">
                            <a href="<?= url('payment') ?>" class="menu-link">
                                <i class="menu-icon tf-icons  bx bx-wallet"></i>
                                <div data-i18n="Analytics">Payment</div>
                            </a>
                        </li>

                        <li class="menu-item <?= $currentFilename === "notifications.php" ? 'active' : '' ?> ">
                            <a href="<?= url('notifications') ?>" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-bell"></i>
                                <div data-i18n="Analytics">Notifications</div>
                            </a>
                        </li>

                        <li class="menu-item <?= $currentFilename === "reports.php" ? 'active' : '' ?> ">
                            <a href="<?= url('reports') ?>" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                                <div data-i18n="Analytics">Reports</div>
                            </a>
                        </li>

                        <li class="menu-item <?= $currentFilename === "users.php" ? 'active' : '' ?> ">
                            <a href="<?= url('users') ?>" class="menu-link">
                                <i class="menu-icon tf-icons  bx bx-user"></i>
                                <div data-i18n="Analytics">Users</div>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <nav
                    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">


                        <ul class="navbar-nav flex-row align-items-center ms-auto">


                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="<?= asset('img/avatars/1.png') ?>" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="<?= asset('img/avatars/1.png') ?>" alt class="w-px-40 h-auto rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="">
                                                    <span class="fw-semibold d-block"><?= $username ?></span>
                                                    <small class="text-muted text-capitalize"><?= $permission ?></small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>


                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>

                                    <li>
                                        <a class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="bx bx-power-off me-2"></i>
                                            <span class="align-middle">
                                                Logout
                                            </span>
                                            <form id="logout-form" action="<?= url('services/logout.php') ?>" method="POST" class="d-none">

                                            </form>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                            <!--/ User -->
                        </ul>
                    </div>
                </nav>

                <!-- / Navbar -->
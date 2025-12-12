<?php

return [
    'GET' => [
        '/' => function () {
            include BASE_PATH . "/views/admin/dashboard.php";
        },
        '/login' => function () {
            include BASE_PATH . "/views/auth/login.php";
        },
        '/books' => function () {
            include BASE_PATH . "/views/admin/book.php";
        },
        '/borrowing' => function () {
            include BASE_PATH . "/views/admin/borrowing.php";
        },
        '/users' => function () {
            include BASE_PATH . "/views/admin/users.php";
        },
        '/fine' => function () {
            include BASE_PATH . "/views/admin/fine.php";
        },
        '/payment' => function () {
            include BASE_PATH . "/views/admin/payment.php";
        },
        '/reservations' => function () {
            include BASE_PATH . "/views/admin/reservations.php";
        },
        '/notifications' => function () {
            include BASE_PATH . "/views/admin/notifications.php";
        },
        '/reports' => function () {
            include BASE_PATH . "/views/admin/reports.php";
        },
        '/logs' => function () {
            include BASE_PATH . "/view/admin/logs.php";
        },
        '/delete-task' => function () {
            include BASE_PATH . "/services/Task.php";
        },
        '/cancel-task' => function () {
            include BASE_PATH . "/services/Task.php";
        },
        '/execute-task' => function () {
            include BASE_PATH . "/services/Task.php";
        },
        '/download' => function () {
            include BASE_PATH . "/services/Task.php";
        },
        '/logout' => function () {
            include BASE_PATH . "/services/logout.php";
        },  
        '/ebay-cron' => function () {
            include BASE_PATH . "/services/Task.php";
        },
        '/update_book' => function () {
            include BASE_PATH . "/services/ajax_functions.php";
        }
    ],
    'POST' => [
        '/create-task' => function () {
            include BASE_PATH . "/services/Task.php";
        },
        '/login' => function () {
            include BASE_PATH . "/services/auth.php";
        },
        '/save_book' => function () {
            include BASE_PATH . "/services/ajax_functions.php";
        },
         '/update_book' => function () {
            include BASE_PATH . "/services/ajax_functions.php";
        },
         '/reservations' => function () {
            include BASE_PATH . "/services/ajax_functions.php";
        },
    ]
];

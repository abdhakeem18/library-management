<?php
define('ENTRY_POINT', true);

// Load the routes
$routes = require './web/route.php';
include "./helpers/appManager.php";

$app = new AppManager();


$sm = $app->getSM();
$username = $sm->getAttribute("username");

if (isset($username)) {
    if ($_SERVER['REQUEST_URI'] === "/login" && $_SERVER['REQUEST_METHOD'] === "GET") {
        header("Location: /");
    }
} else {
    if ($_SERVER['REQUEST_URI'] != "/login" && $_SERVER['REQUEST_URI'] != "/register") {
        header("Location: /login");
    }
}

// dd($url);    

// Get the current URI and HTTP method
define("REQUESTURI", $_SERVER['REQUEST_URI']);
define("HTTP_NETHOD", $_SERVER['REQUEST_METHOD']);

$requestUri = str_replace("/library_managment_system", "", REQUESTURI);
// Normalize URI by removing query string
// print_r($requestUri);
if (($pos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}

// Match the request against defined routes
if (isset($routes[HTTP_NETHOD][$requestUri])) {
    $handler = $routes[HTTP_NETHOD][$requestUri];
    if (is_callable($handler)) {
        call_user_func($handler); // Execute the callback
    } else {
        echo 'Route handler is not callable.';
    }
} else {
    http_response_code(404);
    echo '404 Not Found';
}

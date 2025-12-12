<?php

if (!defined('ENTRY_POINT')) {
    http_response_code(403);
    exit('Forbidden');
}

$pm = AppManager::getPM();
$sm = AppManager::getSM();

$email = $_POST['email'];
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    $sm->setAttribute("ts-status", "error");
    $sm->setAttribute("ts-message", 'Please fill all required fields!');
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    $param = array(':email' => $email);
    $user = $pm->run("SELECT * FROM users WHERE email = :email", $param, true);
    if ($user != null) {
        $correct = password_verify($password, $user['password']);
        if ($correct) {

            $sm->setAttribute("userId", $user['id']);
            $sm->setAttribute("username", $user['username']);
            $sm->setAttribute("permission", $user['permission']);

            header('location: /');
            exit;
        } else {
            $sm->setAttribute("ts-status", "error");
            $sm->setAttribute("ts-message", 'Invalid username or password!');
        }
    } else {
        $sm->setAttribute("ts-status", "error");
        $sm->setAttribute("ts-message", 'Invalid username or password!');
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
}
exit;

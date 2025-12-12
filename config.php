<?php
 if (!defined('ENTRY_POINT')) {
    http_response_code(403);
    exit('Forbidden');
}

define('DB_HOST', 'localhost'); // localhost
define('DB_USERNAME', 'root'); // kilxpjte_eBaySystemAdmin
define('DB_PASSWORD', ''); // 6A^HTq2HT&D*
define('DB_NAME', 'library_system'); // kilxpjte_ebay_api_integration

define('BASE_PATH', __DIR__);
define('CURRENT_DOMAIN', current_domain());
define('SLOT_DURATION', 'PT60M');
date_default_timezone_set("UTC");


function current_domain()
{
    return protocol() . $_SERVER['HTTP_HOST'];
}

function currentUrl()
{
    return current_domain() . $_SERVER['REQUEST_URI'];
}

function protocol()
{
    return stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
}

function asset($src)
{
    $domain = trim(CURRENT_DOMAIN, '/ ');
    $src = $domain . '/assets/' . trim($src, '/ ');
    return $src;
}

function url($url)
{
    $domain = trim(CURRENT_DOMAIN, '/ ');
    $url = $domain . '/' . trim($url, '/ ');
    return $url;
}

//For debug
function dd($data, $comment = '')
{
    print('<pre>');
    print($comment);
    print('<br>');

    print_r($data);
    print('</pre>');

    die;
}

//For debug print
function pr($data, $comment = '')
{
    print('<pre>');
    print($comment);
    print('<br>');

    print_r($data);
    print('</pre>');
}

function getDays()
{
    return [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ];
}

// Function to generate a random string
function generateRandomString($length = 4)
{
    return bin2hex(random_bytes($length / 2));
}

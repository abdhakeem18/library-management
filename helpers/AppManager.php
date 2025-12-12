<?php

 if (!defined('ENTRY_POINT')) {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__) . '/config.php';
require_once BASE_PATH . '/db/migration.php';
require_once BASE_PATH . '/helpers/SessionManager.php';

// Application manager
class AppManager
{

    private static $pm; // Migration
    private static $sm; // Session manager

    public function __construct(){
        $this->getPM();
    }

    // get Migration
    public static function getPM()
    {
        if (self::$pm === null) {
            self::$pm = new DbMigration();
        }
        return self::$pm;
    }

    // get session manager
    public static function getSM()
    {
        if (self::$sm === null) {
            self::$sm = new SessionManager();
        }
        return self::$sm;
    }
}

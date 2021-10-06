<?php
/**
 * SocialEngine
 *
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Engine_Env
{
    private static $env;

    public static function get($key, $default = null)
    {
        self::load();

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        if (!isset(self::$env[$key])) {
            return $default;
        }

        return self::$env[$key];
    }

    private static function load()
    {
        if (self::$env !== null) {
            return;
        }

        self::$env = array();
        $baseEnv = APPLICATION_PATH . '/.env.php';
        if (file_exists($baseEnv)) {
            self::$env = require($baseEnv);
        }
    }
}

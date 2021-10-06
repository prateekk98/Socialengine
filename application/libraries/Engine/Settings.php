<?php
/**
 * SocialEngine
 *
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Engine_Settings
{
    private static $settings = array();

    public static function get($key, $default = null)
    {
        self::load();

        if (strpos($key, '.')) {
            list ($namespace, $actualKey) = explode('.', $key);
            $objects = self::get($namespace, $default);
            if (is_array($objects)) {
                return $objects[$actualKey];
            }

            return $default;
        }

        if (!isset(self::$settings[$key])) {
            return $default;
        }

        return self::$settings[$key];
    }

    private static function load()
    {
        if (self::$settings) {
            return;
        }
        $settings = APPLICATION_PATH . '/application/settings';
        $dir = scandir($settings);
        foreach ($dir as $file) {
            if (substr($file, -4) == '.php' && substr($file, -11) != '.sample.php') {
                $name = str_replace('.php', '', $file);
                self::$settings[$name] = require($settings . '/' . $file);
            }
        }
    }
}

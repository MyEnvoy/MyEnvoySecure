<?php

namespace MyEnvoySecure;

/**
 * Application config.
 */
class Config {

    /** @var array config data */
    private static $data = NULL;

    const INI_FILE = 'config.ini';

    /**
     * @return array
     * @throws Exception
     */
    public static function getConfig($section = NULL) {
        if ($section === null) {
            return self::getData();
        }
        $data = self::getData();
        if (!array_key_exists($section, $data)) {
            throw new Exception('Unknown config section: ' . $section);
        }
        return $data[$section];
    }

    /**
     * @return array
     */
    private static function getData() {
        if (self::$data !== NULL) {
            return self::$data;
        }
        self::$data = parse_ini_file(self::INI_FILE, true);
        return self::$data;
    }

}

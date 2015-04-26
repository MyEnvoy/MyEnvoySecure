<?php

namespace MyEnvoySecure;

/**
 * Description of Firewall
 *
 * @author Fabian
 */
class Firewall {

    private static $has_temp_folder = NULL;
    private static $filepath = NULL;

    const TEMP_FILE = 'firewalltemp.tmp';

    public static function canCreateChat() {
        $data = self::getData();
        if ($data === NULL) {
            return TRUE;
        } elseif (isset($data->stamp) && isset($data->count)) {
            $today = (int) date('Ymd');
            $then = (int) $data->stamp;
            $allowedAmount = (int) Config::getConfig('server')['max_new_chatsPD'];
            if ($today !== $then || $allowedAmount === -1 || $data->count < $allowedAmount) {
                return TRUE;
            }
        } else {
            throw new Exception('Temp file has wrong format.');
        }
        return FALSE;
    }

    public static function recordNewChat() {
        $data = self::getData();
        if ($data === NULL) {
            $data = array('stamp' => ((int) date('Ymd')), 'count' => 1);
        } elseif (isset($data->stamp) && isset($data->count)) {
            $today = (int) date('Ymd');
            $then = (int) $data->stamp;
            if ($today === $then) {
                $data->count ++;
            } else {
                $data->count = 0;
                $data->stamp = $today;
            }
        } else {
            throw new Exception('Temp file has wrong format.');
        }
        self::setData($data);
    }

    private static function initTempFolder() {
        $path = Config::getConfig('server')['temp_folder'];
        if (self::$has_temp_folder === NULL || self::$has_temp_folder === FALSE) {
            if (!file_exists($path) && !is_dir($path)) {
                self::$has_temp_folder = mkdir($path, 0777, TRUE);
            } else {
                self::$has_temp_folder = TRUE;
            }
        }
        if (self::$has_temp_folder !== TRUE) {
            throw new Exception('Error while creating temp folder.');
        }
    }

    private static function getData() {
        if (!self::hasTempFile()) {
            return NULL;
        }
        $data = file_get_contents(self::$filepath);
        return json_decode($data);
    }

    private static function hasTempFile() {
        self::initTempFolder();
        if (self::$filepath === NULL) {
            self::$filepath = self::joinPath(Config::getConfig('server')['temp_folder'], self::TEMP_FILE);
        }
        if (!file_exists(self::$filepath)) {
            return FALSE;
        }
        return TRUE;
    }

    private static function setData($data) {
        if (!self::$has_temp_folder) {
            self::initTempFolder();
        }
        if (self::$filepath === NULL) {
            self::$filepath = self::joinPath(Config::getConfig('server')['temp_folder'], self::TEMP_FILE);
        }
        $data = json_encode($data);
        $temp = fopen(self::$filepath, 'w');
        fwrite($temp, $data);
        fclose($temp);
    }

    private static function joinPath() {
        $path = '';
        $arguments = func_get_args();
        $args = array();
        foreach ($arguments as $a) {
            if ($a !== '') {
                // Removes the empty elements
                $args[] = $a;
            }
        }
        $arg_count = count($args);
        for ($i = 0; $i < $arg_count; $i++) {
            $folder = $args[$i];
            if ($i !== 0 && $folder[0] === DIRECTORY_SEPARATOR) {
                // Remove the first char if it is a '/' - and its not in the first argument
                $folder = \substr($folder, 1);
            }
            if ($i !== $arg_count - 1 && \substr($folder, -1) === DIRECTORY_SEPARATOR) {
                $folder = \substr($folder, 0, -1); //Remove the last char - if its not in the last argument
            }
            $path .= $folder;
            if ($i !== $arg_count - 1) {
                // Add the '/' if its not the last element.
                $path .= DIRECTORY_SEPARATOR;
            }
        }
        return $path;
    }

}

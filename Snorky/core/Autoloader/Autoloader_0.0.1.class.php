<?php

/* 
 *  author: David Buchta <david.buchta@hotmail.com>
 *  created: 13.03.2014
 *  copyright: prorybare.eu
 *  
 *  version: 0.1.1
 *  last modification: 13.03.2014
 * 
 */
namespace Snorky;

define("__ROOT", $_SERVER ["HTTP_HOST"]."/" );

class Autoloader {
    
    public static $baseDir = '';
    public static $classCoreDir = ''; 
    public static $classLibDir = ''; 
    
    /**
     * Load core class files
     * @param  string $className class to use
     * @return bool            success of searching of class
     */
    static public function coreLoader($className) {

        if ($lastNsPos = strrpos($className, '\\'))
            $className = substr($className,  $lastNsPos + 1);

        if (!is_dir($path = self::$baseDir . "/" . self::$classCoreDir . "/" . $className)) {
            return false;
        }

        $latest_version = self::getLatestVersion($path);

        $filename = $path . "/" . $className . "_" . $latest_version . ".class.php"; 

        if (file_exists($filename)) {
            require_once($filename);
            if (class_exists($className)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load lib class files
     * @param  string $className class to use
     * @return bool            success of searching of class
     */
    static public function libLoader($className) {

        $className = substr($className,  strrpos($className, '\\'));
        
        if (!is_dir($path = self::$baseDir . "/" . self::$classLibDir . "/" . $className))
            return false;

        $latest_version = self::getLatestVersion($path);

        $filename = $path . "/" . $latest_version . "/" . $className .  ".php";

        if (file_exists($filename)) {
            require_once($filename);
            if (class_exists($className)) {
                return true;
            }
        }
        return false;

    }

    /**
     * Get latest version of folder/file
     * @param  string $path folder destination
     * @return string       lastest version (in format e.g. 2.2.2)
     */
    private static function getLatestVersion($path) {
        $return_array = array();
        if ($handle = opendir($path)) {
            while (($entry = readdir($handle)) !== false) {
                if ($entry != "." && $entry != "..") {
                    $return_array[] = self::parseVersion($path, $entry);
                }
            }
            closedir($handle);
        }
        usort($return_array, array("self", "compareVersions"));

        return $return_array[0];
    }

    /**
     * Parse version from string entry
     * @param  string $entry entry name
     * @return string        parsed version number
     */
    private static function parseVersion($path, $entry) {
        if (!is_dir($path . "/" . $entry)) {
            $versionPrefixPos = strpos($entry, "_");
            $versionPostfixPos = strpos($entry, ".class.php");
            return substr($entry, $versionPrefixPos + 1, $versionPostfixPos - $versionPrefixPos - 1);
        }
        else
            return $entry;
    }

    /** Version comparing
     * @param string version
     * @param string version
     * @return int higher version
     */
    private static function compareVersions($a, $b){
        return version_compare($b, $a);
    }
}

// Register folders
spl_autoload_register('\Snorky\Autoloader::coreLoader');
spl_autoload_register('\Snorky\Autoloader::libLoader');
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
    
    static public function coreLoader($className) {

        if (!is_dir($path = self::$baseDir . "/" . self::$classCoreDir . "/" . $className))
            return false;

        $latest_version = self::getLatestVersion($path);

        $filename = $path . "/" . $className . "_" . $latest_version . ".class.php"; 

        if (file_exists($filename)) {
            include($filename);
            if (class_exists($className)) {
                return true;
            }
        }
        return false;
    }

    static public function libLoader($className) {
        
        if (!is_dir($path = self::$baseDir . "/" . self::$classLibDir . "/" . $className))
            return false;

        $latest_version = self::getLatestVersion($path);

        $filename = $path . "/" . $latest_version . "/" . $className .  ".php";

        if (file_exists($filename)) {
            include($filename);
            if (class_exists($className)) {
                return true;
            }
        }
        return false;

    }

    private static function getLatestVersion($path) {
        $return_array = array();
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (!is_dir($path . "/" . $entry)) {
                        $versionPrefixPos = strpos($entry, "_");
                        $versionPostfixPos = strpos($entry, ".class.php");
                        $entry = substr($entry, $versionPrefixPos + 1, $versionPostfixPos - $versionPrefixPos - 1);
                    }
                    $return_array[] = $entry;
                }
            }
            closedir($handle);
        }
        usort($return_array, array("self", "compareVersions"));

        return $return_array[0];
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


spl_autoload_register('\Snorky\Autoloader::coreLoader');
spl_autoload_register('\Snorky\Autoloader::libLoader');
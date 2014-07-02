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
    public static $classLibDir = ''; 
    
    static public function libLoader($className) {
        
        //$filename = self::$basedir . self::$classLibDir .'/'.substr($className,  strrpos($className, '\\')+1). ".class.php";
        
        $path = self::$baseDir . "/" . self::$classLibDir . "/" . $className . "/";

        $latest_version = self::getLatestVersion($path);

        $filename = self::$baseDir . "/" . self::$classLibDir . "/" . $className . "/" . $latest_version; 

        if (file_exists($filename)) {
            include($filename);
            if (class_exists($className)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private static function getLatestVersion($path) {
        $return_array = array();
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
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


spl_autoload_register('\Snorky\Autoloader::loader');
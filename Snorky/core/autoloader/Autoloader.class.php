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
    
    public static $basedir = '';
    public static $classLibDir = ''; 
    public static $pluginDir = '';
    
    
    static public function loader($className) {
        
        $filename = self::$basedir . self::$classLibDir .'/'.substr($className,  strrpos($className, '\\')+1). ".class.php";
        
        if (file_exists($filename)) {
            include($filename);
            if (class_exists($className)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    static public function pluginLloader($className){
        $filename = self::$basedir . self::$pluginDir .'/'.substr($className,  strrpos($className, '\\')+1). ".class.php";
      
        if (file_exists($filename)) {
            include($filename);
            if (class_exists($className)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    static public function corePluginLloader($className){
        $filename = self::$basedir . 'corePlugins/'.substr($className,  strrpos($className, '\\')). ".class.php";
       
        if (file_exists($filename)) {
            include($filename);
            if (class_exists($className)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
spl_autoload_register('\Snorky\Autoloader::loader');
spl_autoload_register('\Cougar\Autoloader::pluginLloader');
spl_autoload_register('\Cougar\Autoloader::corePluginLloader');

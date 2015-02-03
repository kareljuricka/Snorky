<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReplacementRegiseter_0
 *
 * @author David
 */
class RR {
    private static $replacementRegister = null; 
    private static $scope = "global";


    /**
     * Add new elemenet to replacement register.
     * @param string $phpName 
     * @param string $templateName - if it isn't specified method will use same value as phpName is
     */
    public static function Add($value, $templateName){
        $trace = debug_backtrace();
        $callName = $trace[1]['class'];
        
        if($callName != ""){
            $scope = crc32($callName);
        }
        else{
            $scope = "global";
        }
        
        //because you can store variables which shouldnt have dollar sign at begining, it is neccessary to do some normalization, here it means if is dollar at begining - remove it!
        if($templateName[0] == "$"){$templateName = substr($templateName, 1);}
        //echo "rr: ".self::$scope." $templateName $value";
        //finaly store data in register        
        self::$replacementRegister[$scope][$templateName] = $value;
            
    }
    
    /**
     * Returns php variable name for given key
     * @param string $templateName key to replacement register
     * @return string name of variable
     */
    public static function Get($templateName){ 
         //because you can store variables which shouldnt have dollar sign at begining, it is neccessary to do some normalization, here it means if is dollar at begining - remove it!
        if($templateName[0] == "$"){$templateName = substr($templateName, 1);}
        
        return  self::$replacementRegister[self::$scope][$templateName];
    }
    
    /**
     * Setting scope for replacement register
     * @param type $scope
     */
    public function SetScope($scope){self::$scope = $scope;}
    
    
}

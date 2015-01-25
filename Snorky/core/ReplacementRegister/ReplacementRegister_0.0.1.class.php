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
    public static function Add($phpName, $templateName = "defaut"){
        
        //if we want same name for phpName and templateName we can call this method without specified teplateName, then is neccessary replace placeholder "default" with correct value  
        if($templateName == "default"){$templateName = $phpName;}
        
        //because you can store variables which shouldnt have dollar sign at begining, it is neccessary to do some normalization, here it means if is dollar at begining - remove it!
        if($templateName[0] == "$"){$templateName = substr($templateName, 1);}
        if($phpName[0] == "$"){$templateNa = substr($templateName, 1);}
        
        //finaly store data in register        
        self::$replacementRegister[self::$scope][$templateName] = $phpName;
            
    }
    
    /**
     * Returns php variable name for given key
     * @param string $templateName key to replacement register
     * @return string name of variable
     */
    public static function Get($templateName){           
        return  self::$replacementRegister[self::$scope][$templateName];
    }
    
    /**
     * Setting scope for replacement register
     * @param type $scope
     */
    public function SetScope($scope){self::$scope = $scope;}
    
    
}

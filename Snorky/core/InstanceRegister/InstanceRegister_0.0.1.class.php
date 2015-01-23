<?php
// php version => 5.1.0
/**
 * Instance register is register for sharing objects between more classes. There are implemented basic access constrains. Instance register
 * is created as singleton. 
 * Before accessing any object from register it has to be register there by register function. Then for accesing object is used
 * function, because register as such is implemented as privat variable.
 */

namespace Snorky;

class InstanceRegister{
    private static $instance = null;
    private $register = null;
    
    /**
     * Public function for getting register.
     * @return instance of InstanceRegister
     */
    public static function Instance(){
        if (InstanceRegister::$instance == null ){
            InstanceRegister::$instance = new InstanceRegister();
        }
        return InstanceRegister::$instance;
    }
    
    /**
     * Function to add object to InstanceReegister
     * @param object $value - object to register 
     * @param string $name - name for accesing object in array
     * @param string $access - define constrains for accesing object. Public everybody can get this object, Proteceted - only same class
     * and chlidren, Private only spedified class. This value is case insensitive.
     */
    public function RegisterObject($value, $name, $access= "public"){
        //for getting class name of object which called this method. It is used to constrain access.
        $trace = debug_backtrace();

        $pole = array(
            "Value" => $value,
            "Key" => $name,
            "Owner" => $trace[1]['class'],
            "Access" => strtolower($access),
        );
        
        $this->register[$name] = $pole;        
    }
    
    
    /**
     * 
     * @param sting $name - key to acces objects. It is name which was used in RegisterObject method.
     * @return object
     * @throws Exception if access constrains diassalowes to get this or if elemetnt in array doesn't exists
     */  
    public function GetObject($name){
        
        //object with metainfo from register
        $element = $this->register[$name];
        if ($element == null){
            //todo: throw not exist error
        }
        
        /* Check if access policy allow to return this object and if it is possible then return object*/        
        //everybody can get this object
        if ($element["Access"] =="public"){
            return $element["Values"];
        }      
        
        // owner of this element is same class as the one which want to access it now. This also fits to private access
        $trace = debug_backtrace();
        $whoWants = $trace[1]['class'];
        
        if ($whoWants == $element["Owner"]){
            return $element["Value"];
        } 
        
        //protected constraint class 
        if ($element["Access"] =="protected"){
            $parrents = class_parents($whoWants);
            
            if(in_array($element, $parrents)){
               return $element["Values"]; 
            } 
        }      
        
        //Caller which want to get this object is not to allowed to get it
        // todo: throw Exception
        
    }
    
}
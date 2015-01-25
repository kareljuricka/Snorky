<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Parser
 *
 * @author David & Karel
 */
class Plugin {
    protected $replacementRegister = null;
    
    public final function SetVariable($name, $value){
        $this->replacementRegister[$name]= array("Name" => $name, "Value" => $value);
    }
    
    public final function GetVarible($name){
        $var = $this->replacementRegister[$name]["Value"];
        
        if($var != null) {return "\$$var";}
        else {return "";}
    }
}

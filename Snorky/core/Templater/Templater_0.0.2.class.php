<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Snorky;
/**
 * Templater is class which is used to start proces of generation cache file for templates. This class is also used as error 404 handler, in the way that
 * firt try generate cache file if it is possible, if not than include an Error 404 page.
 *
 * @author David
 */
class Templater{
    /**
     * Register for holding instance for objects created and registered in anz plugin or page code for current page template.
     * 
     * 
     */
    private $InstanceRegister = null;
    protected $templateFrame = null;
   
     /**
      * This constructor is used in classic way when templater is called from core function.
      */
    public function __construct($page) {
        // creating new register for holding objects
        $this->InstanceRegister = InstanceRegister::Instance();               
      
        
        $parser = new Parser();
        $result = $parser->Run($page);
        // adding necessary code need for plugin to work 
        $code = $this->templaterFrame.$result;
        
        //creating cache file for current page template
        if (!file_put_contents ($this->CFG->GetCacheDir()->$page."_cache_".time().".php",$code)){
            //todo: exception throw 
        }
    }
}

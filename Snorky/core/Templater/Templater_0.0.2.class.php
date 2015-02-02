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
        $this->templateFrame ="<?php require_once(\"{Configurator::Autoloader()}\") ?>";
         
        /**********************************************/
        //  autoloader kod, jestli nebude stranka spoustena samostatne je to zbytecne pridavat do sablony 
        //autoloadaer
        $this->templateFrame= "<?php require_once '".$_SERVER['DOCUMENT_ROOT']."/domains/digiast.com/core/Autoloader/Autoloader_0.0.1.class.php';";
        $this->templateFrame.= '\Snorky\Autoloader::$baseDir = \''.$_SERVER['DOCUMENT_ROOT']."/domains/digiast.com';";
        $this->templateFrame.= '\Snorky\Autoloader::$classCoreDir = "core";';
        $this->templateFrame.= '\Snorky\Autoloader::$classLibDir = "lib"; ?>';

        /**********************************************/
        
        
        //creating cache file for current page template
        $cacheDir = Configurator::GetTemplateCacheDir();
         if (!file_exists($cacheDir."/$page._cache.php") || filemtime(Configurator::GetTemplate($page) > filemtime($cacheDir."/$page._cache.php"))){ 
             
            $parser = new Parser();
            $result = $parser->Run($page);
            // adding necessary code need for plugin to work 
            $code = $this->templateFrame.$result;
          
            //if cache directory doesnt exist create new one
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            file_put_contents($cacheDir."/$page._cache.php", $code);            
        }
        
       
    }
}

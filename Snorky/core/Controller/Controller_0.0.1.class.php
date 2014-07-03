<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Controller
 *
 * @author David & Karel
 */

namespace Snorky;

class Controller {
    
    private $instanceRegister = null;
    	
    public function __construct($dir, $configFile) {

    	$configurator = new Configurator($dir, $configFile);

    	// Init register of instance
    	$this->instanceRegister = Register::getRegistr("instance");

    	// Init configurations
    	$this->instanceRegister->put("configurator", $configurator);

    	// Init templates
    	$this->instanceRegister->put("template", new Templater());

        $this->establishDBConnection();


    }

    private function establishDBConnection() {

    	$configurationDatabase = $this->instanceRegister->get("configurator")->getDatabaseData();

    	\dibi::connect(array(
		    "driver"   => $configurationDatabase->Driver,
		    "host"     => $configurationDatabase->Server,
		    "username" => $configurationDatabase->Admin->Login,
		    "password" => $configurationDatabase->Admin->Password,
		    "database" => $configurationDatabase->Database,
		    "charset"  => $configurationDatabase->Charset
		));
    }
}

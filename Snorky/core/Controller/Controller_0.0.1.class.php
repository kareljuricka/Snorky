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
class Controller {
    
    private $instanceRegister = null;
    	
    public function __construct($configFilePath) {

    	$configurator = new Configurator($configFilePath);

    	// Init register of instance
    	$this->instanceRegister = Register::getRegistry("instance");

    	// Init configurations
    	$this->instanceRegister->put("configurator", $configurator);

    	// Init templates
    	$this->instanceRegister->put("template", new Templater());

        $this->establishDBConnection();


    }

    private function establishDBConnection() {

    	$configurationDatabase = $this->instanceRegister->get("configurator")->getDatabaseData();

    	dibi::connect(array(
		    "driver"   => $configurationDatabase->Driver,
		    "host"     => $configurationDatabase->Server,
		    "username" => $configurationDatabase->Admin->Login,
		    "password" => $configurationDatabase->Admin->Password,
		    "database" => $configurationDatabase->Database,
		    "charset"  => $configurationDatabase->Charset
		));
    }
}

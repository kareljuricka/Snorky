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
    	
    public function __construct() {

    	$configurator = new Configurator($this->paths, $this->configFile);

    	// Init register of instance
    	$this->instanceRegister = Register::getRegistry("instance");

    	// Init configurations
    	$this->instanceRegister->put("configurator", $configurator);

    	// Init templates
    	$this->instanceRegister->put("template", new Template());

    }

    private function establishDBConnection() {

    	$configuration = $this->instanceRegister->get("configurator");

    	dibi::connect(array(
		    'driver'   => 'mysql',
		    'host'     => 'localhost',
		    'username' => 'root',
		    'password' => '***',
		    'database' => 'test',
		    'charset'  => 'utf8',
		));
    }
}

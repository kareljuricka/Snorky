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
class Controller {
    
    private $instance_register;
    	
    public function __construct() {

    	// Init register of instance
    	$this->instance_register = Register::getRegistry("instance");

    	// Init configurations
    	$this->instance_register->put("configurator", new Configurator());

    	// Init templates
    	$this->instance_register->put("template", new Template());

    }

    private function establishDBConnection() {

    	$configuration = $this->instance_register->get("configurator");

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

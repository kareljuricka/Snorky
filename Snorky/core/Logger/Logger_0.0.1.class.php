<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Templater
 *
 * @author David
 */

namespace Snorky;

class Logger {

	private $instanceRegister = null;

	private $logfile = null;
    
    public function __construct($logFile) {

    	$this->logFile = $logFile;

    	// Init register of instance
    	$this->instanceRegister = Register::getRegistr("instance");

    }

    public function putLog($logMessage) {

    	$dir = $this->instanceRegister->get("configurator")->getDir();


    }

}

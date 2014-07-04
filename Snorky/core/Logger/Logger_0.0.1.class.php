<?php
/* 
 *  author: Karel Juřička <kapa@loveart.cz>
 *  created: 04.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 04.07.2014
 * 
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

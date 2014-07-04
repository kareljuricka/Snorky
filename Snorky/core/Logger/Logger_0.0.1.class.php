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

    private $logTypes = Array(
        1 => "E_ERROR",
        2 => "W_WARNING",
        3 => "I_INFO"
    );

	private $instanceRegister = null;

	private $logfile = null;
    
    public function __construct($logFile) {

    	$this->logFile = $logFile;

    	// Init register of instance
    	$this->instanceRegister = Register::getRegistr("instance");
    }

    /**
     * Store log in file
     * @param  int $logNumber  log identifer
     * @param  string $logMessage describing message
     * @param  string $loggedFile file in which log appeared
     * @param  int $logLine    line of file to describe in log
     */
    public function putLog($logNumber, $logMessage, $loggedFile, $logLine) {

    	$dir = $this->instanceRegister->get("configurator")->getDir();

        $date =  date("[Y-m-d h:i:s]",time());
        $client_ip = "[".$_SERVER['REMOTE_ADDR']."]";
        
        // Get PHP error name
        if(isset($this->logTypes[$logNumber])) {
            $logName = $this->logTypes[$logNumber];
        } else {
            $logName = $logNumber;
        }
        
        $text = "Error: $logName - $logMessage; Line: $logLine; File: $loggedFile\n";
        
        file_put_contents($dir . "/" . $this->logFile, $date.$client_ip." ".$text, FILE_APPEND);

    }

}

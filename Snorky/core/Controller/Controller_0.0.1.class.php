<?php
/* 
 *  author: Karel Juřička <kapa@loveart.cz>
 *  created: 02.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 04.07.2014
 * 
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
        $this->instanceRegister->put("multilanguage", new Multilanguage("cz"));

    	// Init templates
    	$this->instanceRegister->put("template", new Templater());

        $this->establishDBConnection();


    }

    private function establishDBConnection() {

    	$adminDatabaseData = $this->instanceRegister->get("configurator")->getAdminDatabaseData();

    	\dibi::connect(array(
		    "driver"   => $adminDatabaseData["driver"],
		    "host"     => $adminDatabaseData["server"],
		    "username" => $adminDatabaseData["login"],
		    "password" => $adminDatabaseData["password"],
		    "database" => $adminDatabaseData["database"],
		    "charset"  => $adminDatabaseData["charset"]
		));

        \dibi::getSubstitutes()->prefix = $adminDatabaseData["prefix"];

        $this->instanceRegister->get("multilanguage")->getContextVariableValue("default", "a");
    }
}

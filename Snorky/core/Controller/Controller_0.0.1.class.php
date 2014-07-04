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
    
    private $instanceRegistry = null;

    private $pageRegister = null;
    	
    public function __construct($dir, $configFile, $page, $logFile) {

    	$configurator = new Configurator($dir, $configFile);

    	// Init register of instance
    	$this->instanceRegistry = Registry::getRegistry("instance");

        // Init register of page
        $this->pageRegistry = Registry::getRegistry("page");

    	// Init configurations
    	$this->instanceRegistry->put("configurator", $configurator);

        $this->establishDBConnection();

        $this->getPageData($page);

        $this->instanceRegistry->put("logger", new Logger($logFile));

        // Init templates
        $this->instanceRegistry->put("multilanguage", new Multilanguage("cz"));

        // Init templates
        $this->instanceRegistry->put("template", new Templater());

        


    }

    private function establishDBConnection() {

    	$adminDatabaseData = $this->instanceRegistry->get("configurator")->getAdminDatabaseData();

    	\dibi::connect(array(
		    "driver"   => $adminDatabaseData["driver"],
		    "host"     => $adminDatabaseData["server"],
		    "username" => $adminDatabaseData["login"],
		    "password" => $adminDatabaseData["password"],
		    "database" => $adminDatabaseData["database"],
		    "charset"  => $adminDatabaseData["charset"]
		));

        \dibi::getSubstitutes()->prefix = $adminDatabaseData["prefix"];
    }

    private function getPageData($page) {

        $result = \dibi::query("SELECT name, title, tpl FROM [:prefix:page] WHERE name = %s", $page);
        globals::addArrayToRegistry($result->fetch(), $this->pageRegistry);

    }
}

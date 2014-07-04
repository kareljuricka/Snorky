<?php
/* 
 *  author: Karel Juřička <kapa@loveart.cz>, David Buchta <david.buchta@hotmail.com>
 *  created: 02.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 03.07.2014
 * 
 */
	// Error reporting on
	ini_set('display_errors',1); 
 	error_reporting(E_ALL);

	$paths["baseDir"] = DIRNAME(__FILE__);
   	$paths["classCoreDir"] = "core";
   	$paths["classLibDir"] = "lib";

   	$configFile = "config.cfg";
   	$logFile = "log/logs.txt";

   	$page = (isset($_GET["page"])) ? $_GET["page"] : "homepage";

	require_once($paths["baseDir"] . "/" . $paths["classCoreDir"] ."/Autoloader/Autoloader_0.0.1.class.php");

	\Snorky\Autoloader::$baseDir = $paths["baseDir"];
	\Snorky\Autoloader::$classCoreDir = $paths["classCoreDir"]; 
	\Snorky\Autoloader::$classLibDir = $paths["classLibDir"]; 

	$controller = new \Snorky\Controller($paths["baseDir"], $configFile, $page, $logFile);	

?>
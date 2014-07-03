<?php

	// Error reporting on
	ini_set('display_errors',1); 
 	error_reporting(E_ALL);

	$paths["baseDir"] = DIRNAME(__FILE__);
   	$paths["classCoreDir"] = "core";
   	$paths["classLibDir"] = "lib";

   	$configFilePath = $paths["baseDir"] . "/" . "config.cfg";

	require_once($paths["baseDir"] . "/" . $paths["classCoreDir"] ."/Autoloader/Autoloader_0.0.1.class.php");

	\Snorky\Autoloader::$baseDir = $paths["baseDir"];
	\Snorky\Autoloader::$classCoreDir = $paths["classCoreDir"]; 
	\Snorky\Autoloader::$classLibDir = $paths["classLibDir"]; 

	$controller = new Controller($configFilePath);	


?>
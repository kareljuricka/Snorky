<?php

	// Error reporting on
	ini_set('display_errors',1); 
 	error_reporting(E_ALL);

	$paths["baseDir"] = DIRNAME(__FILE__);
   	$paths["libDir"] = "core";

	require_once($paths["baseDir"] . "/" . $paths["libDir"] ."/Autoloader/Autoloader_0.0.1.class.php");

	\Snorky\Autoloader::$baseDir = $paths["baseDir"];
	\Snorky\Autoloader::$classLibDir = $paths["libDir"]; 

	$controller = new Controller();	


?>
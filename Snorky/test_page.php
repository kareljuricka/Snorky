<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//autoloadaer
require_once './core/Autoloader/Autoloader_0.0.1.class.php';

$paths["baseDir"] = DIRNAME(__FILE__);
$paths["classCoreDir"] = "core";
$paths["classLibDir"] = "lib";

\Snorky\Autoloader::$baseDir = $paths["baseDir"];
\Snorky\Autoloader::$classCoreDir = $paths["classCoreDir"]; 
\Snorky\Autoloader::$classLibDir = $paths["classLibDir"];

//zavolame templater ten udela sablonu

$templater = new \Snorky\Templater("template");



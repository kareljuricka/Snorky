<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of configurator
 *
 * @author David & Karel
 */
class Configurator {

	private $config = null;

	public function __construct($paths, $config_file) {
		$this->config = loadConfig($config_file);
		var_dump($this->config);
	}

	private function loadConfig($config_file) {
		return simplexml_load_file($config_file);
	}
   
	
}

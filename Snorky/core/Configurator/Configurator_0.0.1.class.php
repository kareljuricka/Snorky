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

	public function __construct() {
		$this->config = $this->loadConfig();
	}

	private function loadConfig() {
		//return simplexml_load_file();
	}
   
	
}

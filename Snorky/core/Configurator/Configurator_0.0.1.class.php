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

	public function __construct($configFilePath) {
		$this->config = $this->loadConfig($configFilePath);
	}

	private function loadConfig($configFilePath) {
		return simplexml_load_file($configFilePath);
	}

	public function getDatabaseData() {
		return $this->config->Database;
	}
}

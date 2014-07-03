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

namespace Snorky;

class Configurator {

	private $config = null;

	private $dir = null;

	public function __construct($dir, $configFile) {
		$this->dir = $dir;
		$this->config = $this->loadConfig($configFile);
	}

	private function loadConfig($configFile) {
		return simplexml_load_file($this->dir . "/" . $configFile);
	}

	public function getDatabaseData() {
		return $this->config->Database;
	}

	public function getTemplateDir() {
		return  $this->dir .  "/" . $this->config->Template->Dir;
	}

	public function getTemplate($templateName) {
		return  $this->getTemplateDir() . "/" . $templateName . "." . $this->config->Template->Extension;
	}
}

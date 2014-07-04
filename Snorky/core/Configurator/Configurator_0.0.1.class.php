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

	public function getUserDatabaseData() {
		$dbData["driver"] = $this->config->Database->Driver;
		$dbData["database"] = $this->config->Database->Database;
		$dbData["server"] = $this->config->Database->Server;
		$dbData["charset"] = $this->config->Database->Charset;
		$dbData["login"] = $this->config->Database->User->Login;
		$dbData["password"] = $this->config->Database->User->Password;

		return $dbData;
	}

	public function getAdminDatabaseData() {
		$dbData["driver"] = $this->config->Database->Driver;
		$dbData["database"] = $this->config->Database->Database;
		$dbData["server"] = $this->config->Database->Server;
		$dbData["charset"] = $this->config->Database->Charset;
		$dbData["login"] = $this->config->Database->Admin->Login;
		$dbData["password"] = $this->config->Database->Admin->Password;

		return $dbData;
	}

	public function getTemplateDir() {
		return $this->dir .  "/" . $this->config->Template->Dir;
	}

	public function getTemplate($templateName) {
		return $this->getTemplateDir() . "/" . $templateName . "." . $this->config->Template->Extension;
	}
}

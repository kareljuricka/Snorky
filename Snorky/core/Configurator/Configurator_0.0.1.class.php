<?php

/* 
 *  author: Karel Juřička <kapa@loveart.cz>
 *  created: 03.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 04.07.2014
 * 
 */

namespace Snorky;

class Configurator {

	private static $config = null;

	private static $dir = null;

	public static function SetConfigurator($dir, $configFile) {
            Configurator::$dir = $dir;
            Configurator::$config = Configurator::loadConfig($configFile);
	}

	private static function loadConfig($configFile) {
		return simplexml_load_file(Configurator::$dir . "/" . $configFile);
	}

	public function getDir() {
		return Configurator::$dir;
	}

	public function getUserDatabaseData() {
		$dbData["driver"] = $this->config->Database->Driver;
		$dbData["database"] = $this->config->Database->Database;
		$dbData["server"] = $this->config->Database->Server;
		$dbData["charset"] = $this->config->Database->Charset;
		$dbData["prefix"] = $this->config->Database->Prefix;
		$dbData["login"] = $this->config->Database->User->Login;
		$dbData["password"] = $this->config->Database->User->Password;

		return $dbData;
	}

	public function getAdminDatabaseData() {
		$dbData["driver"] = $this->config->Database->Driver;
		$dbData["database"] = $this->config->Database->Database;
		$dbData["server"] = $this->config->Database->Server;
		$dbData["charset"] = $this->config->Database->Charset;
		$dbData["prefix"] = $this->config->Database->Prefix;
		$dbData["login"] = $this->config->Database->Admin->Login;
		$dbData["password"] = $this->config->Database->Admin->Password;

		return $dbData;
	}

	public function getDatabasePrefix() {
		return $this->config->Database->Prefix;
	}

	public static function GetTemplateDir() {
                if(Configurator::$dir == null || Configurator::$config == null){
                    //todo: throw uninitialized exception
                }
		return Configurator::$dir .  "/" . Configurator::$config->Template->Dir;
	}

	public static function getTemplate($templateName) {
		return $this->getTemplateDir() . "/" . $templateName . "." . $this->config->Template->Extension;
	}
}

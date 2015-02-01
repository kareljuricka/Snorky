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

abstract class Configurator {

	private static $config = null;

	private static $dir = null;

	public static function SetConfigurator($dir, $configFile) {
            Configurator::$dir = $dir;
            Configurator::$config = self::loadConfig($configFile);
	}

	private static function loadConfig($configFile) {
		return simplexml_load_file(self::$dir . "/" . $configFile);
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

	public static function getAdminDatabaseData() {
		$dbData["driver"] = self::$config->Database->Driver;
		$dbData["database"] = self::$config->Database->Database;
		$dbData["server"] = self::$config->Database->Server;
		$dbData["charset"] = self::$config->Database->Charset;
		$dbData["prefix"] = self::$config->Database->Prefix;
		$dbData["login"] = self::$config->Database->Admin->Login;
		$dbData["password"] = self::$config->Database->Admin->Password;

		return $dbData;
	}

	public function getDatabasePrefix() {
		return self::$config->Database->Prefix;
	}
        
    public function Autoloader(){
            
    }
        
	public static function GetTemplateDir() {
        if(Configurator::$dir == null || Configurator::$config == null){
        	//todo: throw uninitialized exception
        }
		return self::$dir .  "/" . self::$config->Template->TplDir;
	}

	public static function getTemplatePhpDir() {
        if(Configurator::$dir == null || Configurator::$config == null){
        	//todo: throw uninitialized exception
        }
		return self::$dir .  "/" . self::$config->Template->PhpDir;
	}

	public static function GetTemplate($templateName) {
            //todo: uninitilaized exception
		return self::getTemplateDir() . "/" . $templateName . "." . self::$config->Template->Extension;
	}
        
   	public static function GetTemplatePhp($templateName) {
        return self::getTemplatePhpDir() . "/" . $templateName . ".php";   
	}
        
	public static function GetTemplateCacheDir(){
     	return self::$dir .  "/" . self::$config->Template->CacheDir;       
	}

	public static function getPluginsDir() {
		return self::$dir .  "/" . self::$config->Plugin->Dir;
	}

    public static function GetPluginDir($plugin){
        return self::getPluginsDir() . "/" . $plugin . "/";
    }
        
   	public static function GetPluginExt(){
   		return self::$config->Plugin->Ext;
    }
        
    public static function GetPluginCacheDir($plugin){
     	return self::$config->Plugin->CacheDir . "/" . $plugin . "/";      
    }
    
    public static function GetPluginPhp($plugin){
        return self::getPluginDir($plugin) . "/" . $plugin . ".php";
    }
        
    public static function GetPluginTemplate($plugin){
    	return self::getPluginDir($plugin) . "/" . $plugin . "." . self::GetPluginExt();       
    }
        
    public static function GetPluginCacheExt(){
     	return self::$config->plugin->CacheExt;       
	}
        
    public static function GetNamespace(){
    	return self::$config->General->Namespace;        
	}
}

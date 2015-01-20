<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Templater
 *
 * @author David
 */

namespace Snorky;

class Templater {

	private $pageRegistry = null;

	private $instanceRegistry = null;

	public function __construct() {

		// Init register of instance
    	$this->instanceRegistry = Registry::getRegistry("instance");

		// Init register of page
        $this->pageRegistry = Registry::getRegistry("page");

        $templateFile = $this->instanceRegistry->get("configurator")->getTemplate($this->pageRegistry->get("tpl"));

		//$parser = new Parser($templateFile);
	}

	public function getPluginTemplateData($pluginName) {
		$result = \dibi::query("SELECT tpl_dir, cache_dir FROM [:prefix:plugin_template] WHERE name = %s", $pluginName);
		return $result->fetchSingle();

	}

}

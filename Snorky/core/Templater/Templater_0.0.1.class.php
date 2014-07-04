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

	public function __construct() {
		//$parser = new Parser();
		
	}

	public function getPluginTemplateData($pluginName) {
		$result = \dibi::query("SELECT tpl_dir, cache_dir FROM [:prefix:plugin_template] WHERE name = %s", $pluginName);
		return $result->fetchSingle();

	}

}

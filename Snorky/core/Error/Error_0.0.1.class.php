<?php

/* 
 *  author: David Buchta <david.buchta@hotmail.com>
 *  created: 13.03.2014
 *  copyright: prorybare.eu
 *  
 *  version: 0.1.1
 *  last modification: 13.03.2014
 * 
 */

namespace Snorky;

class Error {

	private static $instance = null;

	private $errors = null;

	private function __construct();

	public static function getInstance() {
		if (self::$instance === null)
			self::$instance = new Error();	
		else
			return self::$instance;
	}

	public function putError($errorNum, $errorMsg) {
		$this->$errors[$errorNum] = $errorMsg;
	}

	public function getErrors() {
		return $this->$errors;
	}
}

?>
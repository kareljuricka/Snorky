<?php
/* 
 *  author: Karel Juřička <kapa@loveart.cz>
 *  created: 03.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 03.07.2014
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
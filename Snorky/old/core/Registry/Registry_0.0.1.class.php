<?php
/* 
 *  author: Karel Juřička <kapa@loveart.cz>, David Buchta <david.buchta@hotmail.com>
 *  created: 02.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 03.07.2014
 * 
 */
namespace Snorky;

class Registry {

	// Registry instance
	private static $instance = Array();
	
	// Registry
	private $registry = null;
	
	/** Return and if not exist, then create Registry instance
	 * Something like constructor, becouse it everytime returns instance of registry specified by param 
	 * @param name name of registr to use
	 * @return object Registry
	 */
	public static function getRegistry($name) {
		if (!array_key_exists($name, self::$instance)) {
			self::$instance[$name] = new Registry();
		}
		return self::$instance[$name];
	}
	
	/** Put record to registry
	 * @param int|string key of registry record
	 * @param mixed value of registry record
	 * @return bool success or fail
	 */
	public function put($key, $value) {
		if (isset($this -> registry[$key])) {
			throw new Exception("There is already an entry for key " . " in registry");
			return false;
		} else {
			$this -> registry[$key] = $value;
			return true;
		}
	}

	/** Get record from registry
	 * @param int|string key of registry record
	 * @return mixed|bool value of registry record or false in case of non-existing record
	 */
	public function get($key) {
		if (!isset($this -> registry[$key])) {
			throw new Exception("There is no entry for key " . " in registry");
			return false;
		} else {
			return $this -> registry[$key];
		}		
	}
	
	/** Remove record from registry
	 * @param int|string key of registry record
	 * @return bool success or fail
	 */
	public function remove($key) {
		if (isset($this -> registry[$key])) {
			unset($this->registry["key"]);
			return true;
		} else {
			return false;
		}			
	}

	private function __construct() {
	}

	private function __clone() {
	}

}

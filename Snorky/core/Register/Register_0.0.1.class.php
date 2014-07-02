<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Register
 *
 * @author David & Karel
 */
class Register {

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
			self::$instance[$name] = new Register();
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

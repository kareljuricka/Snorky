<?php

/* 
 *  author: Karel Juřička <kapa@loveart.cz>
 *  created: 04.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 04.07.2014
 * 
 */
namespace Snorky;

abstract class Globals {

	/**
	 * Check if table exists in database
	 * @param  string $tableName name of table
	 * @return bool 
	 */
    public static function checkDatabaseTableExists($tableName) {
        $result = \dibi::query("SHOW TABLES LIKE %s", $tableName);
        return (!count($result)) ? false : true;
    }

    public static function addArrayToRegistry($array, $registr) {
    	foreach ($array as $key => $value) {
    		$registr->put($key, $value);
    	}
    }
}
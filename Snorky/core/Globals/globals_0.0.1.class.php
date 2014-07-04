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
}
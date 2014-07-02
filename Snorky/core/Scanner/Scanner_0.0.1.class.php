<?php

/* 
 *  author: David Buchta <david.buchta@hotmail.com>
 *  created: 02.07.2014
 *  copyright: Snorky Systems 2014
 *  
 *  version: 0.0.1
 *  last modification: 02.07.2014
 * 
 */
/**
 * Class for lexical scanner of Snorky template language. Instance of this class is usually is called
 * from lexical anallyzer.
 */
class Scanner {
    //put your code here
    private $rowNumber = null;
    private $fileHandler = null;
    private $cachedString =null;
    
    //this array defines token fo regullar expressions
    protected static $_terminals = array(
        "/^({:)/" => "T_OPEN",
        "/^(:})/" => "T_CLOSE",
        "/^(plugin)/" => "T_PLUGIN",
        "/^(name)/" => "T_NAME",
        "/^(cacheable)/" => "T_CACHEABLE",
        "/^(params)/" => "T_PARAMS",
        "/^(=)/" => "T_IS",
        "/^(\.)/" => "T_CONCAT",
        "/^(\{)/" => "T_PARAM_START",
        "/^(\})/" => "T_PARAM_END",
        "/^(,)/" => "T_COMMA",
        "/^(\s+)/" => "T_WHITESPACE",
        "/^((?:\"|\')(?:[a-zA-Z]|_)[a-zA-Z0-9]*(?:\"|\')/" => "T_VAL",
        "/^(true)/" => "T_TRUE",
        "/^(false)/" => "T_FALSE",
        "/^(\$::\w+)/" => "T_VAR_CONTEXT",
        "/^(\$\w+)/" => "T_VAR",
    );
    
     public function __construct($file) {
        $this->rowNumber = 0;
        
        if(! ($this->fileHandler = fopen($file, "r"))){
             throw new Exception('Cannot open template file',0);
        }        
    }
}

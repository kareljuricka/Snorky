<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Parser
 *
 * @author David & Karel
 */

namespace Snorky;

/***************************************************
 * main exception class as main point between Snorky exceptions and php Exception
 ***************************************************/
class Exception extends \Exception {
    //put your code here
}


/***************************************************
 *  exceptions from Snorky
 ***************************************************/

// Snorky mluvit lidsky

/**************************************************
 *  Scanner exceptions
 **************************************************/

class ScannerEX extends Exception{
    
}

class EndOfFile extends ScannerEX{
    protected $_field;
    public function __construct($message="", $code=0 , Exception $previous=NULL, $field = NULL)
    {
        $this->_field = $field;
        parent::__construct($message, $code, $previous);
    }
    public function getField()
    {
        return $this->_field;
    }
}

class EndOfMethod extends ScannerEX{
    protected $_field;
    public function __construct($message="", $code=0 , Exception $previous=NULL, $field = NULL)
    {
        $this->_field = $field;
        parent::__construct($message, $code, $previous);
    }
    public function getField()
    {
        return $this->_field;
    }
}

class FileNotExist extends ScannerEX{
    protected $_field;
    public function __construct($message="", $code=0 , Exception $previous=NULL, $field = NULL)
    {
        $this->_field = $field;
        parent::__construct($message, $code, $previous);
    }
    public function getField()
    {
        return $this->_field;
    }
}



class LexError extends Exception{
     public function __construct($message="", $code=0 , Exception $previous=NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**************************************************
 *  Parser exceptions
 **************************************************/
class ParserEX extends Exception{
    
}
class SyntaxError extends ParserEX{
    protected  $levelOfException = null;
    // Redefine the exception so message isn't optional
    public function __construct($message, $level = 1, $code = 0, Exception $previous = null) {
        // make sure everything is assigned properly
        $this->levelOfException = $level;
        parent::__construct($message, $code, $previous);
    }
    
    public function GetExceptionLevel(){
        return $this->levelOfException;
    }
}

class EndOfBlock extends ParserEX{
    protected  $levelOfException = null;
    protected $_field;
    
    public function __construct($message="",$field = NULL,  $level = 10, $code = 0, Exception $previous = null) {
        // make sure everything is assigned properly
        $this->_field = $field;
        $this->levelOfException = $level;
        parent::__construct($message, $code, $previous);
    }
    
    public function GetExceptionLevel(){
        return $this->levelOfException;
    }
    
    public function GetField(){
        return $this->_field;
    }
}

class SemanticError extends ParserEX{
    protected  $levelOfException = null;
    
    public function __construct($message="", $level = 10, $code = 0, Exception $previous = null) {
        // make sure everything is assigned properly
        $this->levelOfException = $level;
        parent::__construct($message, $code, $previous);
    }
    
    public function GetExceptionLevel(){
        return $this->levelOfException;
    }
}
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
class EndOfFile extends Exception{
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


class LexError extends \Exception{
     public function __construct($message="", $code=0 , Exception $previous=NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}


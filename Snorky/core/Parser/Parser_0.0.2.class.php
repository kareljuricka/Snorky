<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

/**
 * Parser go through php code and make replacment array for all registered variable for replacing in tamplate code. For any new plugin is called new istance
 * of parser.
 *
 * @author David
 */
class Parser{
    //put your code here
    
    private $replacement = null;
    
    public function __construct() {
        $this->replacement = new ReplacementRegister();
    }
    
    
    
    
}

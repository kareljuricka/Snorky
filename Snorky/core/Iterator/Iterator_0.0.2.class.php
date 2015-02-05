<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Iterator_0
 *
 * @author David
 */
namespace Snorky;

class Iterator {
    private $counter = 0;
    
    public function __construct() {
        $this->counter = 0;
    }
    
    /**
     * Because php doesn't suport operator overloading iterator incrementation can only be done by callin this function.
     */
    public function inc(){
        $this->counter++;
    }
    
    /**
     * If it si first iteration returns true otherwise false.
     * @return boolean
     */
    public function isFirts(){
        return $this->counter == 0 ? true :false;
    }
    /**
     * If it is last iteration this method returns true otherwise it retuns false. Method needs as argument array on which we are iterating.
     * @param array $array
     * @return boolean
     */
    public function isLast($array){        
        return (sizeof($array) - 1) == $this->counter ? true : false;       
    }
    /**
     * Returns counter value starting from 1.
     * @return int
     */
    public function getCounter(){
        return $this->counter + 1;
    }
    
    /**
     * Returns true if current iteration is odd.
     * @return bool
     */
    public function isOdd(){
        //iteration is  <1;n> counter <0;(n-1)>
        return $this->counter % 2 == 1? true :false;
    }
    
    /**
     * Returns true if current iteration is even.
     * @return bool
     */
    public function isEven(){
        //iteration is  <1;n> counter <0;(n-1)>
        return $this->counter % 2 == 0? true :false;
    }
            
}

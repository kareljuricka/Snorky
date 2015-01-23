<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 */

/**
 * Parser go through php code and make replacment array for all registered variable for replacing in tamplate code. For any new plugin is called new istance
 * of parser.
 *
 * @author David
 */

namespace Snorky;

class Parser extends Templater {
    //put your code here
    
    private $replacement = null;
    private $scanner = null;
    private $miniCode = null;
    
    public function __construct() {
        $this->replacement = new ReplacementRegister();
        $this->scanner = new Scanner();
    }
    
    public function Run($page){
        
        $this->scanner->SetFile($page);
        $minicode = $this->initializePlugins();
        
    }
    
    /**
     * Function goes throw template file and find all plugins call, initialize every plugin, register every plugin as public into 
     * InstanceRegister and create cache minicode. 
     * @return string minicode for initialization objects in cache file while is interpretede.
     * @throws SyntaxError
     */
    private function initializePlugins(){
        $minicode = "";
        
        try{
            while(true){
                //get start token
                $this->scanner->GetToken();
                
                /* findind plugins */
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "PLUGIN"){continue;}
                
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "T_IS"){
                    throw new SyntaxError('Missing "=" after "plugin" keyword, on line: '.$this->scanner->GetRowNumber());
                }
                
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "IDENTIFIER "){
                    throw new SyntaxError('Missing plugin identifier after "=" keyword, on line: '.$this->scanner->GetRowNumber());
                }
                
                //call plugin constructor and register newly created object
                $obj = new $token["value"]();
                $instances = InstanceRegister::Instance();
                $instances->RegisterObject($obj,$token["value"]);
                
                //check if plugin is cacheable, if this is true we don't need generate code for creating this plugin
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "CACHEABLE"){
                    //adding creation of this object to minicode syntax
                    $minicode .= "\$obj = new {$token["value"]}();\$instances->RegisterObject(\$obj,{$token["value"]});";
                }
            }
        }
        catch (EndOfFile $ex){return $minicode;}
    }
    
    
    
}

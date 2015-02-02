<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Scanner_0
 *
 * @author David
 */

namespace Snorky; 

class Scanner{
    //put your code here
    private $file = null;
    private $rowNumber = 0;
    private $filePointerPosition = 0;
    private $fileHandler = null;
    private $cachedString =null;    
    protected $cacheStack = null; 
    public static $debug = false;
    
//this array defines token fo regullar expressions
    protected static $_terminals = array(
        "T_OPEN" => "/^({:)/",
        "T_CLOSE" => "/^(:})/",
        "PLUGIN" => "/^(plugin)/",
        "VARIABLE" => '/^(\$\w+)/',
        "T_IS" => "/^(=)/",
        "METHOD" => "/^(method)/",
        "CACHEABLE" => "/^(cacheable)/",
       // "ARGUMENTS" => "/^(\(.*\))/",
        // \((((?:\".+\"|-?[0-9]+(\.[0-9]+)*)s*\,s*)*(?:\".+\"|-?[0-9]+(\.[0-9]+)*))?\)
        "T_NAME" => "/^(name)/",
        "T_PARAMS" => "/^(params)/",
        "IDENTIFIER" => "/^([a-zA-Z_][a-zA-Z0-9_]*)/",
        "METHOD_LABEL" => "/^({{\s*[a-zA-Z_][a-zA-Z0-9_]*\s}}\s*\n)/",        
        "ARRAY_INDEX" => "/^(\[(?:[0-9]+|\"\w+\")\])/",        
        "T_WHITESPACE" => "/^(\s+)/",
        
        /*
        "/^(\+)/" => "T_PLUS",
        "/^(\.)/" => "T_CONCAT",
        "/^(\/)/" => "T_DIV",
        "/^(\*)/" => "T_MUL",
        "/^(\()/" => "L_BRA",
        "/^(\))/" => "R_BRA",
        "/^(\{)/" => "T_PARAM_START",
        "/^(\})/" => "T_PARAM_END",
        "/^(,)/" => "T_COMMA",
        "/^((?:\"|\')(?:[a-zA-Z]|_)[a-zA-Z0-9]*(?:\"|\')/" => "T_VAL",
        "/^(true)/" => "T_TRUE",
        "/^(false)/" => "T_FALSE",
        "/^(\$::\w+)/" => "T_VAR_CONTEXT",
        "/^(\$\w+)/" => "T_VAR",*/
    );
    
    public function __construct() {
        $this->rowNumber = 0;
        $this->cacheStack = new \SplStack();
        
    }
    
    /**
     * Setting template file name with all extensions and full path  
     * @param string $file path to file
     */
    public function SetFile($file){
       if( ($this->fileHandler = fopen($file, "r")) == false){
           throw new FileNotExist("Cannot open $file");
       }
    }
    
    /**
     * Accessing privat row number variable.
     * @return int
     */
    public function GetRowNumber(){return $this->rowNumber;}
    
    /**
     * This function is used to get keyword from template file.
     * @param bool $newBlock - this parametr determine if function should looking for new start of code block default value is true
     * @return asociative array which contains two keys - "type" and "value". Type is token type, value is used for additional information
     * @throws EndOfFile exception if there is no more text to read from file
     */
    public function GetToken($newBlock = true){
        
         // newBlock is true, function is looking for new block of code in template. It searchs for "{:" tag.
        if($newBlock){
            $removedString = null;
            
            do{
                $pos = strpos($this->cachedString,"{:");
                
                if(preg_match(self::$_terminals['METHOD_LABEL'],$this->cachedString,$match)){
                    throw new EndOfMethod('End of file',1,NULL, $removedString);
                }
                
                // start tag wasn't found, it reads next line from source file.
                if(!$pos){                    
                    $removedString.= $this->cachedString;
                    
                    if(!($this->cachedString = fgets($this->fileHandler))){
                        throw new EndOfFile('End of file',1,NULL, $removedString);
                    }
                    $this->rowNumber++;
                }
            }while(!$pos);
            
            $removedString.= substr($this->cachedString, 0,$pos);
            $this->cachedString = substr($this->cachedString, $pos + 2);
            return array("type" => "T_START", "value" => $removedString);
        }
        
        /*now we are getting more tokens which are before end tag*/
        //firstly we need to remove whitespaces from string start
        $this->cachedString = preg_replace(self::$_terminals['T_WHITESPACE'], '', $this->cachedString);
        
        while(true){        
            if(self::$debug){echo "<dd>/-/{$this->cachedString}/-/</dd>";}
            foreach(static::$_terminals as $pattern => $name) {
            
                if(preg_match($name, $this->cachedString, $matches,PREG_OFFSET_CAPTURE )) {
                    //method label EoF for me
                    if ($name == "METHOD_LABEL"){
                        throw new EndOfMethod('End of file',1,NULL, NULL);
                    }
                    
                    if(self::$debug){echo "<dd>match: ".$matches[0][0]." type: $pattern</dd><br>";}
                    //removing matched token from code string
                    $this->cachedString = substr($this->cachedString, $matches[0][1]+strlen($matches[0][0]));
                    return array('type' => $pattern, 'value' => $matches[0][0]);
                }
            }
            
            //There is no token in our cached string, so we load new line
            if(!($this->cachedString = fgets($this->fileHandler))){
                throw new EndOfFile('End of file',1,NULL, NULL);
            }
            $this->rowNumber++;
        }
    }
    
    /**
     * Function rewind loaded code from template file just after the given label name.
     * @param string $methodName
     * @throws EndOfFile
     */
    public function RewindToMethod($methodName){
        $this->cachedString = preg_replace(self::$_terminals['T_WHITESPACE'], '', $this->cachedString);       
       
        while(true){        
            
            if(preg_match("/^({{\s*$methodName\s}}\s*\n)/", $this->cachedString, $matches,PREG_OFFSET_CAPTURE )){
                $this->cachedString = substr($this->cachedString, $matches[0][1]+strlen($matches[0][0]));
                $this->filePointerPosition = ftell($this->fileHandler);
                return;
            }
            //There is no token in our cached string, so we load new line
            if(!($this->cachedString = fgets($this->fileHandler))){
                // cause run is default method so if it is only method template in file it hasn't to be specified by label
                if(strcasecmp($methodName, "run") == 0){rewind($this->fileHandler); return;}
                else {throw new EndOfFile('End of file',1,NULL, NULL);}
            }
            $this->rowNumber++;
        }
    }
    
    /**
     * Function load methods argument from template file as string of them all for example "method(4,"test")" it returns "(4,"test")"
     * @return string
     * @throws EndOfFile
     * @throws SyntaxError
     */
    public function GetMethodArgs(){
        $stringIndex = 0;
        $start = 0;
        $args ="";
        $quotaion = false;
        $apostrof = false;
        $comma = false;
        $escape = false;
                
        while(true){
           
            if(self::$debug){
                echo "<b>e:</b> ";
                echo $escape ? 'true' : 'false';
                echo "<b> c: </b>" ;
                echo $comma ? 'true' : 'false';
                echo "<b> a: </b>" ;
                echo $apostrof ? 'true' : 'false';
                echo "<b> qu: </b>" ;
                echo $quotaion ? 'true' : 'false';
                echo "<b> znak: </b>".$this->cachedString[$stringIndex]."<br>";
            }
            
             //remove excess whitespaces
            if(!($apostrof || $quotaion)){ $this->cachedString = preg_replace(self::$_terminals['T_WHITESPACE'], '', $this->cachedString);}
            if($this->cachedString ==""){
                if(!($this->cachedString = fgets($this->fileHandler))){
                    throw new EndOfFile('End of file',1,NULL, NULL);
                }
                $this->rowNumber++;
                //neew line is loaded so is neccesary to reset string index because raw starts at the begining of the loaded code
                $stringIndex = 0;
                continue;
            }
            
            if($start == 0 && $this->cachedString[$stringIndex] !='('){throw new SyntaxError("Unexpected symbol \"{$this->cachedString[$stringIndex]}\" after method name, was expecting \"(\" row:{$this->scanner->GetRowNumber()}");}
            // reading function arguments
            else if($this->cachedString[$stringIndex] == '(' && !($quotaion || $apostrof)) {$start++; }
            else if($this->cachedString[$stringIndex] == ')' && !($quotaion || $apostrof)){
                // we finish reading function arguments
                if(--$start == 0 ){
                    $args .= $this->cachedString[$stringIndex++];
                    break;
                }                
            }
            //string argument
            else if($this->cachedString[$stringIndex] == '"' && !$escape && !$apostrof){ $quotaion = !$quotaion;}
            else if($this->cachedString[$stringIndex] == "'" && !$escape && !$quotaion){ $apostrof = !$apostrof;}
            //escape
            else if($this->cachedString[$stringIndex] == "\\" && !$escape){$escape = true;}
            // two consecutive commas  - ,, 
            else if($this->cachedString[$stringIndex] == "," && $comma && (!$quotaion || !$apostrof)){  throw new SyntaxError("Missing argument row:{$this->GetRowNumber()}");}
            else if($this->cachedString[$stringIndex] == ',' && !($quotaion || $apostrof)) {$comma=true; }
            //nulling escape and doma
            else {
                $comma = false;
                $escape = false;                
            }
                        
            $args .= $this->cachedString[$stringIndex++];
            
        }
    
        //removing args from source code string
        $this->cachedString = substr($this->cachedString, $stringIndex);
        return $args;
    }
    
    public function  rewindSource(){
        $this->cachedString ="";
        $this->rowNumber = 0;
        fseek($this->fileHandler, $this->filePointerPosition);
    }
}

<?php

/* 
 *  author: David Buchta <david.buchta@hotmail.com>
 *  created: 02.07.2014
 *  copyright: Snorky Systems 2014
 *  
 *  version: 0.0.1
 *  last modification: 03.07.2014
 * 
 */
/**
 * Class for lexical scanner of Snorky template language. Instance of this class is usually is called
 * from lexical anallyzer.
 */

namespace Snorky;

class Scanner {
    //put your code here
    private $rowNumber = 0;
    private $fileHandler = null;
    private $cachedString =null;
    protected $instanceRegister = null;
    protected $configurator = null;
    
    protected $cacheStack = null; 

    //this array defines token fo regullar expressions
    protected static $_terminals = array(
        "/^({:)/" => "T_OPEN",
        "/^(:})/" => "T_CLOSE",
        "/^(plugin)/" => "T_PLUGIN",
        "/^(name)/" => "T_NAME",
        "/^(cacheable)/" => "T_CACHEABLE",
        "/^(params)/" => "T_PARAMS",
        "/^(=)/" => "T_IS",
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
        "/^(\$\w+)/" => "T_VAR",
    );
    
     public function __construct($templateName) {
        $this->rowNumber = 0;
        $this->instanceRegister = Register::getRegistr("instance");
        $this->configurator = $this->instanceRegister->get("configurator");
        $this->cacheStack = new \SplStack();
        
        if(! ($this->fileHandler = fopen($this->configurator->getTemplate($templateName), "r"))){
             throw new Exception('Cannot open template file',0);
        }        
    }
    
    /**
     * 
     * @return INT - Row number
     */
    public function getRow(){
        return $this->rowNumber;
    }
    
    /**
     * Method for storin token which was already read, but not used by parser, so parser will want this token next time
     * when he asks lexer for token.
     * @param type $token
     */
    public function setCache($token){
        
        $this->cacheStack->push($token);
    }
    /**
     * 
     * @param type $token
     * @return type string error name
     */
    public function getTokenMeaning($token){
        $str = $this->terminals[$token];
        
        //deleting things for pregmatch
        return substr($str, 3,-1);
    }
    
     /**
     * This method get next token from file.
     * @param bool $findNext - if this parametr is false, method get tokens between {: and :} tags. If is true method find next {: 
      * ang get the first token. Default value is true.
     * @throws Exception - If file is read to end, this execption is thrown
     */
    public function getToken(bool $findNext = true){
        
        if(strlen($this->cachedString) < 0){
            if(!($this->cachedString = fgets($this->fileHandler))){
                 throw new EndOfFile('End of file',1,NULL, NULL);
            }
            $this->rowNumber++;
        }
        
        //find start token tag, and cut string to this start
        if($findNext){
            $helpString = ''; //store string which was remove from original file string until we found start tag, it is clasical hmtl, css, etc.. code
            
            do{
                $pos = strpos($this->cachedString,"{:");
                // start tag wasn't found, it reads next line from source file.
                if(!$pos){
                    //store string
                    $helpString.= $this->cachedString;
                    if(!($this->cachedString = fgets($this->fileHandler))){
                        throw new EndOfFile('End of file',1,NULL, $helpString);
                    }
                    $this->rowNumber++;
                }
            }while(!$pos);
            
            $helpString.= substr($this->cachedString, $pos);
            $this->cachedString = substr($this->cachedString, $pos+2);
            
            $token['token'] = "T_OPEN";
            $token['match'] = $helpString;
            
            return $token; 
            
        }else{
            
            if(!$this->cacheStack->isEmpty()) {
                return $this->cacheStack->pop();
            }
            // continues reading between start and end tag
            //removing white spaces from start of source string
            $this->cachedString = preg_replace(self::$_terminals['T_WHITESPACE'], '', $this->cachedString);
            
            //if string is empty, we need to load next line from file, and again is neccesary to remove whte spaces
            while(strlen($this->cachedString) < 0){
                if(!($this->cachedString = fgets($this->fileHandler))){
                     throw new EndOfFile('End of file',1,NULL, NULL);
                }
                $this->rowNumber++;
                $this->cachedString = preg_replace(self::$_terminals['T_WHITESPACE'], '', $this->cachedString);
            }
            
            //find token, if token is found function returns with it otherwise it continues and throw exception
            foreach(static::$_terminals as $pattern => $name) {
                if(preg_match($pattern, $this->cachedString, $matches,PREG_OFFSET_CAPTURE )) {
                    //set start of string to the end of matched result
                    $this->cachedString = substr($this->cachedString, $matches[1]+strlen($matches[0]));
                    return array(
                        'match' => $matches[0],
                        'token' => $name
                    );
                }
            }
 
            throw new EndOfFile;
        }
        
        
    }// end public function getToken(bool $findNext = true)
    
    public function __destruct() {
        fclose($this->fileHandler);
    }
}

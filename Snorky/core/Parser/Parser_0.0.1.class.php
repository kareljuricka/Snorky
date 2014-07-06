<?php
/* 
 *  author: David Buchta <david.buchta@hotmail.com>
 *  created: 02.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 03.07.2014
 * 
 */
namespace Snorky;

class Parser {
    // variables for objects
    protected $Lexer = null;
    protected $Error = null;
    protected $Register = null;
    protected $Multilanguage = null;
    
    protected $registerInstance = null;
    protected $registerPluginInstance =null;
    protected $template = null;
    protected $cacheFile = null;
    protected $code = "";
    protected static $pluginsStack = array(); // variable for checking cyclical calling plugins    
    protected $codeOk = true;
    protected $errorMessage = '';
    protected $context = "";
    
    
    protected $equation = "";

    protected $cache = false; 
    
    protected $pluginChecked = array(); // array for skipping already checked plugins
    
    public function __construct($file,$cacheFile) {
        $this->code=" echo\"";
        $this->template = $file;
        $this->cacheFile = $cacheFile;
        $this->Lexer = new Scanner($file);
        $this->Error = Error::getInstance(); // singleton
        $this->Register = Register::getRegistry("variables"); 
        $this->registerInstance = Register::getRegistry("instance");
        $this->registerPluginInstance = Register::getRegistry("PluginInstance");
        $this->Multilanguage = $this->registerInstance->get("multilanguage");
       
    }
    
        
    public function run(){
        $run =  true;
        while($run){
            // gettting open tag
            try {
                $this->cache = false;
                $token = $this->lexer->getToken();
                $this->code.=$token['match'];
                $this->T_OPEN();
            }
            //catching syntax error
            catch (SyntaxError $ex){
                $this->recoverToKey();
                $this->Error->putError($ex->GetExceptionLevel(),$ex->getMessage());
                $this->codeOk = false;
            }
            
            catch (LexError $ex){
                $this->recoverToKey();
                $this->Error->putError($ex->GetExceptionLevel(),$ex->getMessage());;
                $this->codeOk = false;
            }
            catch (EndOfFile $ex) {
                $this->code.= $ex->getField();
                $run = false;
            }
        } 
        
        
        
    } 
    
    
    protected function T_OPEN(){
        try {
            $token = $this->Lexer->getToken(FALSE);
        } 
        catch (LexError $ex){
                $this->recoverToKey();
                $this->errorMessage.="Lexical error on line: {$this->lexer->getRow()}\r\n";
                $this->codeOk = false;
        }
        catch (EndOfFile $ex) {
            throw new Exception("Unexpected end of file.",4 );
        
        }
        
        
        // continue in gramatical rules
        
        switch ($token['token']){
            case "T_CACHEABLE":     $this->cache = true;
                                    $this->T_OPEN();
                                    break;
            case "T_VAR":   $this->equation .= '$this->Register->get('.$token["match"].')'; 
                            $this->signParse();
                            break;
                            
            case "T_VAR_CONTEXT":   $this->equation .= '$this->Multilanguage->getContextVariableValue('.$this->template.','.$token['match'].')';
                                    $this->signParse();
                                    break;
                                
            case "T_NAME":          $this->T_PLUGIN();
                                    break;
                                
            default:    throw new Exception("Unexpected token \"{$token['match']}\" on line: {$this->Lexer->getRow()}",4 );
        }
        
    }
    
    protected function signParse(){
        try {
            $token = $this->Lexer->getToken(FALSE);
        } 
        catch (LexError $ex){
                $this->recoverToKey();
                $this->errorMessage.="Lexical error on line: {$this->lexer->getRow()}\r\n";
                $this->codeOk = false;
        }
        catch (EndOfFile $ex) {
            throw new Exception("Unexpected end of file.",4 );
        
        }
        
        switch ($token['token']){
            case "L_BRA":
            case "R_BRA":
            case "T_PLUS": 
            case "T_CONCAT":
            case "T_MUL": 
            case "T_DIV":   $this->equation.= $token['match'];
                            $this->VARS();
                            break;
            
            case "T_CLOSE": if($this->cache){
                                $this->code .= self::calcString($this->equation); 
                            }else{
                                $this->code.=   '\" 
                                                 $result='.$this->equation.'; echo $result; 
                                                 echo \"';
                            }
                            
                            $this->equation="";
                            break;
            default:    throw new Exception("Unexpected token \"{$token['match']}\" on line: {$this->Lexer->getRow()}",4 );
                        
        }
    }
    
    protected function VARS(){
        try {
            $token = $this->Lexer->getToken(FALSE);
        } 
        catch (LexError $ex){
                $this->recoverToKey();
                $this->errorMessage.="Lexical error on line: {$this->lexer->getRow()}\r\n";
                $this->codeOk = false;
        }
        catch (EndOfFile $ex) {
            throw new Exception("Unexpected end of file.",4 );
        
        }
        
        switch ($token['token']){           
            case "T_VAR":   $this->equation .= '$this->Register->get('.$token["match"].')'; 
                            $this->signParse();
                            break;
                            
            case "T_VAR_CONTEXT":   $this->equation .= '$this->Multilanguage->getContextVariableValue('.$this->template.','.$token['match'].')';
                                    $this->signParse();
                                    break;
                                
            default:    throw new Exception("Unexpected token \"{$token['match']}\" on line: {$this->Lexer->getRow()}",4 );
        }
    }
    
    protected function T_PLUGIN(){
        try {
            $token = $this->Lexer->getToken(FALSE);
        } 
        catch (LexError $ex){
                $this->recoverToKey();
                $this->errorMessage.="Lexical error on line: {$this->lexer->getRow()}\r\n";
                $this->codeOk = false;
        }
        catch (EndOfFile $ex) {
            throw new Exception("Unexpected end of file.",4 );
        
        }
        
        if($token['token']!== T_IS){
             throw new Exception("Unexpected token \"{$token['match']}\", it should be \"=\".",4 );
        }
        
        try {
            $token = $this->Lexer->getToken(FALSE);
        } 
        catch (LexError $ex){
                $this->recoverToKey();
                $this->errorMessage.="Lexical error on line: {$this->lexer->getRow()}\r\n";
                $this->codeOk = false;
        }
        catch (EndOfFile $ex) {
            throw new Exception("Unexpected end of file.",4 );
        
        }
        
        if($token['token']!== T_VAL){
             throw new Exception("Unexpected token \"{$token['match']}\", it should be some string.",4 );
        }
        
        $name = $token['match'];
        
    }


    public static function calcString($mathString){
       
            $mathString = trim($mathString);     // trim white spaces
            $mathString = ereg_replace ('[^0-9\+-\*\/\(\) ]', '', $mathString);    // remove any non-numbers chars; exception for math operators
 
            $compute = create_function("", "return (" . $mathString . ");" );
            return 0 + $compute();
    }
 
    
}

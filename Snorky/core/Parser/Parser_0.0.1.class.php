<?php
/* 
 *  author: David Buchta <david.buchta@hotmail.com>
 *  created: 02.07.2014
 *  copyright: Snorky Systems
 *  
 *  version: 0.0.1
 *  last modification: 02.07.2014
 * 
 */
namespace Snorky;

class Parser {
    // variables for objects
    protected $Lexer = null;
    protected $Error = null;
    protected $Register = null;
    
    
    protected $code = null;
    protected $pluginsStack = array(); // variable for checking cyclical calling plugins    
    protected $codeOk = true;
    protected $errorMessage = '';
   
    
    protected   $cache = false; 
    
    private $pluginChecked = array(); // array for skipping already checked plugins
    
    public function __construct($file) {
        $this->Lexer = new Scanner($file);
        $this->Error = Error::getInstance(); // singleton
        $this->Register = Register::getRegistry("variables");
        $this->code="echo\"";
    }
    
        
    public function run(){
        $run =  true;
        while($run){
            // getttin open tag
            try {
                $this->cache = false;
                $token = $this->lexer->getToken();
                $this->$code.=$token['match'];
                $this->T_OPEN();
            }
            //catching syntax error
            catch (SyntaxError $ex){
                $this->recoverToKey();
                $this->Error->putError(4,$ex->getMessage());
                $this->codeOk = false;
            }
            
            catch (LexError $ex){
                $this->recoverToKey();
                $this->Error->putError($ex->GetExceptionLevel(),$ex->getMessage());;
                $this->codeOk = false;
            }
            catch (EndOfFile $ex) {
                $this->$code.= $ex->getField();
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
            case "T_VAR":   if($this->cache){
                                $this->code.= $this->Register->get($token["match"]);
                            }
                            else {
                                $this->code .= $token["match"];
                            }
                            $this->
                            break;
                            
            case
        }
        
        protected function 
    }
    
    

}

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
    
    
    protected $code = null;
    protected $pluginsStack = array(); // variable for checking cyclical calling plugins    
    protected $codeOk = true;
    protected $errorMessage = '';
    protected $Error;
    protected $cache = false; //chaching not allowed, becasue imho is unneficent so it is disable by lexer
    
    private $pluginChecked = array(); // array for skipping already checked plugins
    
    public function __construct($file) {
        $this->Lexer = new Scanner($file);
        $this->Error = Error::getInstance(); // singleton
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
            
        } 
        catch (LexError $ex){
                $this->recoverToKey();
                $this->errorMessage.="Lexical error on line: {$this->lexer->getRow()}\r\n";
                $this->codeOk = false;
        }
        catch (EndOfFile $ex) {
            throw new Exception("Unexpected end of file.",4 );
        
        }
    }
    
    

}

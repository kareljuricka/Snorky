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

class Parser {
    //put your code here
    
    private $replacement = null;
    private $scanner = null;
    private $plugin = null; // name of plugin
    private $method = null; //name of method
    private $finalCacheCode = "<?php \$instances = \\Snorky\\InstanceRegister::Instance();";
    private $scope = "";
    private $hasPhpFile = true;
    protected static $pluginStack = null;
    
    private static $debug =  true;
    
    public function __construct($plugin =null,$method=null) {
       // $this->replacement = new \ReplacementRegister();
        $this->scanner = new Scanner();
        $this->plugin = $plugin;
        $this->method = $method;
        
    }
    
    /**
     * Main entry point to Parser. This function call every other function to provide proper cache file code string generation.
     * @param type $page template name without any extensions etc..
     * @return string cache code for givven template
     */
    public function Run($page){
        
        if($this->plugin == null){$this->scanner->SetFile(Configurator::GetTemplate($page));}
        else {$this->scanner->SetFile(Configurator::GetPluginTemplate($page));}
        $instances = InstanceRegister::Instance();
        $instances->SetScope($page);
              
        //rewinding template fiel to method if it was specified
        if($this->method != null){$this->scanner->RewindToMethod($this->method);}
        
        $this->finalCacheCode.= $this->initializePlugins();
        
        //setting replacement register for curent scope which is defined by plugin name or global keyword for template, this setting is only for cache, in parser it doesn't affect anything
        if($this->plugin!=null){$scope = crc32($this->plugin);}
        else {$scope ="global";}
        $this->scope = $scope;      
        $this->finalCacheCode .=" RR::SetScope(\"$scope\"); ?>";
        
       
        /* loading php script for page into string and adding it to cache code, plugin code is obtained by require_once in template file, php script is optional */
        if($this->plugin == null){
            //geting php code file
            $phpCodeFile = Configurator::GetTemplatePhp($page);  
           
            if(file_exists($phpCodeFile)){
                $phpCode = php_strip_whitespace($phpCodeFile);
                $phpOpenTagRegex = "/(?:<\?php|<\?)/";
                $closeTagRegex = "/\?>/";            
                if(preg_match_all($phpOpenTagRegex, $phpCode, $dummy) > preg_match_all($closeTagRegex, $phpCode, $dummy)){$phpCode .=" ?>";}   
                $this->finalCacheCode .=$phpCode;
            }   
        
        } 
        
         
        try{
            while(true){
               $this->finalCacheCode.= $this->parseTemplate(); 
            }
        } catch (EndOfFile $ex) {
            $this->finalCacheCode.= $ex->getField();
        } catch (EndOfMethod $ex){
            $this->finalCacheCode.= $ex->getField();
        }
        
        
       //returning one level up, is need to remove current name from plugin stack which of course is last element in stack
       if(self::$pluginStack != null){
           array_pop(self::$pluginStack);
       }
       return $this->finalCacheCode;
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
            
            $instances = InstanceRegister::Instance();
            
            while(true){
                //get start token
                $this->scanner->GetToken();
                
                /* finding plugins */
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "PLUGIN"){continue;}
                
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "T_IS"){
                    throw new SyntaxError('Missing "=" after "plugin" keyword, on line: '.$this->scanner->GetRowNumber()." file: {$instances->GetScope()}");
                }
                
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "IDENTIFIER"){
                    throw new SyntaxError('Missing plugin identifier after "=" keyword, on line: '.$this->scanner->GetRowNumber()." file: {$instances->GetScope()}");
                }
                
                //call plugin constructor and register newly created object
                $obj = new $token["value"]();
                $instances->RegisterObject($obj,$token["value"],"public",false);
                $objName = $token["value"];
                
                //check if plugin is cacheable, if this is true we don't need generate code for creating this plugin
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "CACHEABLE"){
                    //adding creation of this object to minicode syntax
                    $pluginPhpCode = Configurator::GetPluginPhp($objName);
                    $minicode .= "require_once(\"$pluginPhpCode\"); \$obj = new $objName();\$instances->RegisterObject(\$obj,\"$objName\",\"public\",false);";
                }
            }
        }
        catch (ScannerEX $ex){
            $this->scanner->rewindSource();
            return $minicode;            
        }
    }
    
    private function parseTemplate(){
        $code = "";
        $instances = InstanceRegister::Instance();
        
        $startToken = $this->scanner->GetToken();       
        
        $code .=$startToken["value"]; 
        try{
            $token = $this->scanner->GetToken(false);
                
            switch ($token["type"]){
                case "VARIABLE":    $code.= $this->variable($token["value"]);
                                    break;
                case "PLUGIN":  $code.= $this->plugin();
                                break;
                case "T_CLOSE": break;
                default: {throw new SyntaxError("Unexpected token \"{$token['value']}\", row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");}
            }
        }
        catch (EndOfFile $ex){
            $tplFile = Configurator::GetTemplate($instances->GetScope());
            throw new SyntaxError("Unexpected end of file in: \"$tplFile\"");}
        
        catch (EndOfMethod $ex){
            $tplFile = Configurator::GetTemplate($instances->GetScope());
            throw new SyntaxError("Unexpected end of method on line: {$this->scanner->GetRowNumber()} in: \"$tplFile\"");}
            
        return $code;
    }
    
     /** 
     * @param sting $varTemplateName variable name in template
     * @return string php code
     * @throws SyntaxError
     */
    public function variable($varTemplateName){
        
        $code = "<?php \$pom=\\RR::Get('$varTemplateName');";
        
       //geting indexes to array if it was in template as array with index
        $arrayIndexes ="";
        while(true){
             try{
                $token = $this->scanner->GetToken(false);
                if($token["type"]== "T_CLOSE") {break;}
                elseif($token["type"] == "ARRAY_INDEX" ){$arrayIndexes.=$token["value"];}
                else {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":\}\" or array index after \"$templateName\", row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");}                
            }            
            catch (EndOfFile $ex){throw new SyntaxError("Unexpected end of file in: \"$tplFile\"");}
        }
        
        
        $code.= "echo \$pom$arrayIndexes; ?>";
        
        return $code;     
    }
    
    
    /**
     * Function parse plugin call create all neccessary cache files and return cachce string for given plugin call.
     * @return string
     * @throws SyntaxError
     * @throws SemanticError
     */
    private function plugin(){
        $cacheable = false;
        $methodName = "run"; //default plugin method, which is called if method isn't specified
        $args = "()"; // default args
        $method = null;
        /* {: plugin=PLUGIN_NAME [method=METHOD(args)] [cacheable] :}*/
        //=
        $token = $this->scanner->GetToken(false);        
        if($token['type']!= "T_IS"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"=\" after \"plugin\", row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");} 
        
        //PLUGIN_NAME
        $token = $this->scanner->GetToken(false); 
        if($token['type']!= "IDENTIFIER"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting plugin name, row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");} 
        else {$pluginName =  $token["value"];} 
      
        if(self::$pluginStack!=null && in_array($pluginName, self::$pluginStack)){throw new SemanticError("Cyclical plugin calling row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");}
       
        //method | chaceable | :}
        $token = $this->scanner->GetToken(false);
        $close = false; //if closing tag has already been loaded
        switch ($token['type']){
            case "T_CLOSE": 
                $close = true;
                break;
            case "CACHEABLE":   $cacheable = true;
                                break;
            //method=methodName(args)
            case "METHOD":  $token = $this->scanner->GetToken(false);
                            if($token['type']!= "T_IS"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"=\" after \"method\", row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");}
                
                            $token = $this->scanner->GetToken(false); 
                            if($token['type']!= "IDENTIFIER"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting method name, row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");} 
                
                            $methodName = $token['value'];
                            $method = $methodName;
                            try{$args = $this->scanner->GetMethodArgs();} 
                            catch (SyntaxError $ex) {
                                $msg = $ex->getMessage();
                                throw new SyntaxError("$msg file: \"$tplFile\"");
                            }
                
                            break;
            default:    {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\", \"cacheable\" or \"method\" row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");}  
           }
        
       // $namespace = Configurator::GetNamespace();
        $pluginCall = "\$obj = \$instances->GetObject('$pluginName'); \$obj->$methodName$args;";
        
        if(!$close){
            $token = $this->scanner->GetToken(false);
       
            if ($token['type']== "CACHEABLE" && !$cacheable){
                $cacheable = true;
                $token = $this->scanner->GetToken(false);
            }
       
            if ($token['type']!= "T_CLOSE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":\}\", row:{$this->scanner->GetRowNumber()} file: \"$tplFile\"");}
        }
        
        
        //making cache file for plugin
        $cacheDir = Configurator::GetPluginCacheDir($pluginName);
        $cacheFilename = $cacheDir.$pluginName."_".$methodName.Configurator::GetPluginCacheExt();
        if (self::$debug || (!file_exists($cacheFilename) || filemtime(Configurator::GetPluginTemplate($pluginName) > filemtime($cacheFilename)))){ 
           
            $newParser = new Parser($pluginName, $method);
            $retCode = $newParser->Run($pluginName);
            //if cache directory doesnt exist create new one
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            file_put_contents($cacheFilename, $retCode);            
        }
        //loading cahce file to string
        else {
            $retCode = file_get_contents($cacheFilename);
        }
        
        //making cache string for plugin call
        $pluginPhpCode = Configurator::GetPluginPhp($pluginName);
        
        if($cacheable){
            $cacheCode = $this->RunMethod($pluginCall,$pluginPhpCode);
            ob_start();
                require($cacheFilename);
            $cacheCode .= ob_get_clean();            
        }
        else{
            $cacheCode = "<?php require_once(\"$pluginPhpCode\"); $pluginCall?>$retCode <?php RR::SetScope(\"{$this->scope}\"); ?>";
        }
        
        return $cacheCode;
    }
    
    
    /**
     * Function for setting new scope for evaluating mehtods
     * @param string $pluginCall
     * @return string 
     */
    private function RunMethod($pluginCall, $pluginPhpCode){ 
        require($pluginPhpCode);
        ob_start();
            eval($pluginCall);
            $contents = ob_get_contents();
        ob_end_clean();
        
        return $contents;
    }


   
    
    
}

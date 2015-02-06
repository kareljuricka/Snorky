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
    
    private $curTplFileName = null;
    private $currIterator = null;
    private $scanner = null;
    private $plugin = null; // name of plugin
    private $method = null; //name of method
    private $finalCacheCode = "<?php \$instances = \\Snorky\\InstanceRegister::Instance();";
    private $scope = "";
    private $hasPhpFile = true;
    private static $varCounter = 0;
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
        
        $this->curTplFileName = $page;
        
        if($this->plugin == null){$this->scanner->SetFile(Configurator::GetTemplate($page));}
        else {$this->scanner->SetFile(Configurator::GetPluginTemplate($page));}
        $instances = InstanceRegister::Instance();
        $instances->SetScope($page);
              
        //rewinding template fiel to method if it was specified
        if($this->method != null){$this->scanner->RewindToMethod($this->method);}
        
        if(self::$debug){echo "Plugin Init: <br>";}
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
        
        if(self::$debug){echo "Template parse: <br>";} 
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
                $cacheable = false;
                //get start token
                $this->scanner->GetToken();
                
                /* finding plugins */
                $token = $this->scanner->GetToken(false);
                
                //skipping literal
                if($token['type'] == "LITERAL"){
                    $token = $this->scanner->GetToken(false);
                    if($token['type'] != "T_CLOSE"){
                        throw new SyntaxError('Missing ":}" after "literal" keyword, on line: '.$this->scanner->GetRowNumber()." file: {$this->curTplFileName}");
                    }
                    
                   $this->scanner->LiteralRead();
                }
                if($token['type'] != "PLUGIN"){continue;}
                
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "T_IS"){
                    throw new SyntaxError('Missing "=" after "plugin" keyword, on line: '.$this->scanner->GetRowNumber()." file: {$this->curTplFileName}");
                }
                
                $token = $this->scanner->GetToken(false);
                if($token['type'] != "IDENTIFIER"){
                    throw new SyntaxError('Missing plugin identifier after "=" keyword, on line: '.$this->scanner->GetRowNumber()." file: {$this->curTplFileName}");
                }
                
                
                //call plugin constructor
                $objName = $token["value"];
                $obj = new $objName();                
                $registrationName  = $objName;
                
                do {
                    $token = $this->scanner->GetToken(false);                    
                    if($token['type'] == "CACHEABLE"){ $cacheable = true;}
                    if($token['type'] == "LABEL"){
                        
                        $token = $this->scanner->GetToken(false);
                        if($token['type'] != "T_IS"){
                            throw new SyntaxError('Missing "=" after "label" keyword, on line: '.$this->scanner->GetRowNumber()." file: {$this->curTplFileName}");
                        }
                
                        $token = $this->scanner->GetToken(false);
                        if($token['type'] != "IDENTIFIER"){
                            throw new SyntaxError('Missing label identifier after "=" keyword, on line: '.$this->scanner->GetRowNumber()." file: {$this->curTplFileName}");
                        }
                        
                        $registrationName  = $token["value"]; 
                    }
                    
                }while($token["type"] != "T_CLOSE");
                
                $instances->RegisterObject($obj,$registrationName,"public",false);
                
                // code is generated only if plugin isn't marked as cacheable
                if(!$cacheable){
                    //adding creation of this object to minicode syntax
                    $pluginPhpCode = Configurator::GetPluginPhp($objName);
                    $minicode .= "require_once(\"$pluginPhpCode\"); \$obj = new $objName();\$instances->RegisterObject(\$obj,\"$registrationName\",\"public\",false);";
                }
            }
        }
        catch (ScannerEX $ex){
            $this->scanner->rewindSource();
            return $minicode;            
        }
    }
    
    private function parseTemplate($block = false){
        $code = "";
        $instances = InstanceRegister::Instance();
        
        $startToken = $this->scanner->GetToken();       
        
        $code .=$startToken["value"]; 
        try{
            $token = $this->scanner->GetToken(false);
            
            switch ($token["type"]){
                case "VARIABLE":    
                    $code.= $this->variable($token["value"]);
                    break;
                case "PLUGIN":  
                    $code.= $this->plugin();
                    break;
                case "FOREACH": 
                    $code.= $this->foreach_m();
                    break;
                case "BLOCK_END":
                    if(!$block){
                        throw new SyntaxError("Unexpected token \"{$token['value']}\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");
                    }
                    $token = $this->scanner->GetToken(false);
                    if($token['type'] !="T_CLOSE") {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\",  after \"block_end\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
                    else{throw new EndOfBlock("", $code);}
                    break;
                case "FIRST":
                    $code.= $this->first();
                    break;
                case "LAST":
                    $code .= $this->last();
                    break;
                case "EVEN":
                    $code .= $this->even();
                    break;
                case "ODD":
                    $code .= $this->odd();
                    break;
                case "ITERATION":
                    $code.= $this->iteration();
                    break;
                case "ITERATOR":
                    if($this->currIterator == null){
                        throw new SyntaxError("\"iterator\" can only be used in loops, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");
                    }
                    $token = $this->scanner->GetToken(false);
                    
                    if($token['type'] !="T_CLOSE") {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\",  after \"block_end\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
                    else{$code .= "<?php echo \${$this->currIterator}->getCounter(); ?>";}
                    break;
                case "LITERAL":
                    $token = $this->scanner->GetToken(false);                    
                    if($token['type'] !="T_CLOSE") {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\",  after \"literal\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
                    
                    $code .= $this->scanner->LiteralRead();
                    break;
                case "T_CLOSE": 
                    break;
                default: 
                    throw new SyntaxError("Unexpected token \"{$token['value']}\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");
            }
        }
        catch (EndOfFile $ex){
            $tplFile = Configurator::GetTemplate($instances->GetScope());
            throw new SyntaxError("Unexpected end of file in: \"$tplFile\"");}
        
        catch (EndOfMethod $ex){
            $tplFile = Configurator::GetTemplate($instances->GetScope());
            throw new SyntaxError("Unexpected end of method on line: {$this->scanner->GetRowNumber()} in: \"{$this->curTplFileName}\"");}
            
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
                else {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":\}\" or array index after \"$templateName\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}                
            }            
            catch (EndOfFile $ex){throw new SyntaxError("Unexpected end of file in: \"{$this->curTplFileName}\"");}
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
        
        /* {: plugin=PLUGIN_NAME [method=METHOD(args)] [label=label_Name][cacheable] :}*/
        //=
        $token = $this->scanner->GetToken(false);        
        if($token['type']!= "T_IS"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"=\" after \"plugin\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        
        //PLUGIN_NAME
        $token = $this->scanner->GetToken(false); 
        if($token['type']!= "IDENTIFIER"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting plugin name, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        else {$pluginName =  $token["value"];} 
      
        if(self::$pluginStack!=null && in_array($pluginName, self::$pluginStack)){throw new SemanticError("Cyclical plugin calling row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
       
        //method | label | cacheable | :}
        $token = $this->scanner->GetToken(false); 
        
        
        if($token['type'] == "METHOD"){
            
            $token = $this->scanner->GetToken(false);
            if($token['type']!= "T_IS"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"=\" after \"method\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
            
            $token = $this->scanner->GetToken(false); 
            if($token['type']!= "IDENTIFIER"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting method name, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
                
            $methodName = $token['value'];
            $method = $methodName;
            
            try{$args = $this->scanner->GetMethodArgs();} 
            catch (SyntaxError $ex) {
                $msg = $ex->getMessage();
                throw new SyntaxError("$msg file: \"{$this->curTplFileName}\"");
            }
            
            $token = $this->scanner->GetToken(false); 
        }
        
        // label | cacheable | :}       
        if($token['type'] == "LABEL"){
            
            $token = $this->scanner->GetToken(false);
            if($token['type']!= "T_IS"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"=\" after \"label\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
            
            $token = $this->scanner->GetToken(false);
            if($token['type']!= "IDENTIFIER"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting label name, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
            else {$labelName =  $token["value"];} 
            
            $token = $this->scanner->GetToken(false); 
        }
        else{$labelName = null;}
        
        // cacheable | :}
        if($token['type'] == "CACHEABLE"){
            $cacheable = true;            
            $token = $this->scanner->GetToken(false); 
        }
        
        // :}
        if ($token['type']!= "T_CLOSE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":\}\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
               
        
       // $namespace = Configurator::GetNamespace();;
        $pluginCall = "\$obj = \$instances->GetObject('".($labelName == null ? $pluginName: $labelName)."'); \$obj->$methodName$args;";
        
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
    /**
     * Method translate Snorky syntax foreach into php foreach. 
     * @return string
     * @throws SyntaxError
     */
    private function foreach_m(){
        // {: foreach $array as [$key => ] $row :}
        //              .
        //              .
        //              .
        //          {: end :}
        
      
        //$array
        $token = $this->scanner->GetToken(false);        
        if($token['type']!= "VARIABLE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"arrray name\" after \"foreach\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        $array = $token['value'];
        $_array = "array"."_".self::$varCounter++."_".floor(microtime(true));
        
        //as
        $token = $this->scanner->GetToken(false);        
        if($token['type']!= "AS"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"as\" after \"arrray name\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}

        
        //$row
        $token = $this->scanner->GetToken(false);        
        if($token['type']!= "VARIABLE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"variable name\" after \"as\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        $row = $token['value'];
        $_row = "row"."_".self::$varCounter++."_".floor(microtime(true));
        
        $token = $this->scanner->GetToken(false);    
        
        // =>, previsouly loaded $row isn't row but $key
        if($token['type'] == "ASSIGN"){
            $key = $row;
            $_key = $_row;
            
            $token = $this->scanner->GetToken(false);        
            if($token['type']!= "VARIABLE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"variable name\" after \"=>\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
            
            $partCode = " $_key => ";
            $partCode2 = " \\RR::Add(\$$_key,'$key');";
            $row = $token['value'];
            $_row = "row"."_".self::$varCounter++."_".floor(microtime(true));
            
            $token = $this->scanner->GetToken(false);
            if($token['type']!= "T_CLOSE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\"  after \"=>\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        }
        else{
            if($token['type']!= "T_CLOSE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\" or \"=>\" after \"identifier\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        }
         
        
        $iterator = "iterator"."_".self::$varCounter++."_".floor(microtime(true));
        $prevIterator = $this->currIterator;
        $this->currIterator = $iterator;
        
        $code = "<?php \$$_array = \\RR::Get('$array'); \$$iterator = new \\Snorky\\Iterator(count(\$$_array)); foreach($$_array as $partCode \$$_row) {";
        $code .= " \\RR::Add(\$$_row,'\$row'); $partCode2 ?>";
        while(true){
            try{
                $code .= $this->parseTemplate(true);
            } catch (EndOfBlock $ex) {$code.= $ex->GetField(); break;}
        }
       
        $this->currIterator = $prevIterator;
        $code .= "<?php  \$".$iterator."->inc(); }?>";
        return $code;        
    }
    
    /*
     * Method for parsing {: first :} ... {: block_end :}
     */
    private function first(){
        echo " <br>";
        $token = $this->scanner->GetToken(false);
        if($token['type'] != "T_CLOSE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\" after \"first\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        
        if($this->currIterator == null){throw new SemanticError ("\first\" block can only be used in loop block, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
        
        $code = "<?php if(\${$this->currIterator}->isFirts()) { ?>";
        while(true){
            try{
                $code .= $this->parseTemplate(true);
            } catch (EndOfBlock $ex) {$code.= $ex->GetField(); break;}
        }
        
        $code .= "<?php } ?>";
        return $code; 
    }
    /*
     * Method for parsing {: last :} ... {: block_end :}
     */
    private function last(){
        
        $token = $this->scanner->GetToken(false);
        if($token['type'] != "T_CLOSE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\" after \"last\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        
        if($this->currIterator == null){throw new SemanticError ("\last\" block can only be used in loop block, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
        
        $code = "<?php if(\${$this->currIterator}->isLast()) { ?>";
        while(true){
            try{
                $code .= $this->parseTemplate(true);
            } catch (EndOfBlock $ex) {$code.= $ex->GetField(); break;}
        }
        
        $code .= "<?php } ?>";
        return $code; 
    }
    
    /*
     * Method for parsing {: even :} ... {: block_end :}
     */
    private function even(){
        
        $token = $this->scanner->GetToken(false);
        if($token['type'] != "T_CLOSE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\" after \"even\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        
        if($this->currIterator == null){throw new SemanticError ("\even\" can only be used in loop block, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
        
        $code = "<?php if(\${$this->currIterator}->isEven()) { ?>";
        while(true){
            try{
                $code .= $this->parseTemplate(true);
            } catch (EndOfBlock $ex) {$code.= $ex->GetField(); break;}
        }
        
        $code .= "<?php } ?>";
        return $code; 
    }
    
    /*
     * Method for parsing {: odd :} ... {: block_end :}
     */
    private function odd(){
        
        $token = $this->scanner->GetToken(false);
        if($token['type'] != "T_CLOSE"){throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\" after \"odd\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        
        if($this->currIterator == null){throw new SemanticError ("\odd\" can only be used in loop block, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
        
        $code = "<?php if(\${$this->currIterator}->isOdd()) { ?>";
        while(true){
            try{
                $code .= $this->parseTemplate(true);
            } catch (EndOfBlock $ex) {$code.= $ex->GetField(); break;}
        }
        
        $code .= "<?php } ?>";
        return $code; 
    }
    
    /*
     * Method for parsing {: iteration=n :} ... {: block_end :}
     */
    private function iteration(){
        // = n :}
        $token =  $this->scanner->GetToken(false);
        if($token['type'] != "T_IS") {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \"=\" after \"iteration\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        
        // n :}
        $token =  $this->scanner->GetToken(false);
        if($token['type'] != "POSITIVE_NUMBER") {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting positive int after \"=\", row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");} 
        $iteration = $token["value"];
        
        // :} 
        $token =  $this->scanner->GetToken(false);
        if($token['type'] != "T_CLOSE") {throw new SyntaxError("Unexpected token \"{$token['value']}\", was expecting \":}\" after positive int, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
        
        if($this->currIterator == null){throw new SemanticError ("\iteration\" block can only be used in loop block, row:{$this->scanner->GetRowNumber()} file: \"{$this->curTplFileName}\"");}
        
        $code = "<?php if(\${$this->currIterator}->iteration($iteration)) { ?>";
        while(true){
            try{
                $code .= $this->parseTemplate(true);
            } catch (EndOfBlock $ex) {$code.= $ex->GetField(); break;}
        }
        
        $code .= "<?php } ?>";
        return $code; 
        
    }
}

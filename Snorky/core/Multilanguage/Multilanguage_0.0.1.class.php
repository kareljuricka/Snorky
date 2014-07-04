<?php

/* 
 *  author: David Buchta <david.buchta@hotmail.com>
 *  created: 13.03.2014
 *  copyright: prorybare.eu
 *  
 *  version: 0.1.1
 *  last modification: 13.03.2014
 * 
 */
namespace Snorky;

class Multilanguage {

    private $language = null;

    private $instanceRegister = null;

    public function __construct($defaultLanguage) {

        // Init register of instance
        $this->instanceRegister = Register::getRegistr("instance");

        $this->setLang($defaultLanguage);
    }

    public function setLang($lang) {
        $this->language = $lang;
        $_SESSION["webLanguage"] = $this->language;
    }

    public function getLang() {
        if (isset($_SESSION["webLanguage"]))
            $this->language = $_SESSION["webLanguage"];
        return $this->language;
    }

    public function getContextVariableValue($pageTemplate, $contextVariableName) {

        $contextVarsTable = "context_variables_" . $this->language;

        $result = \dibi::query("SHOW TABLES LIKE %s", $this->instanceRegister->get("configurator")->getDatabasePrefix() . $contextVarsTable);
        if (!count($result))
            throw new Exception("Database table for language '" . $this->language . "' doesn't exist",0);

        $result = \dibi::query("SELECT value FROM [:prefix:".$contextVarsTable."] WHERE [pageTemplate] = %s AND [name] = %s", $pageTemplate, $contextVariableName);
        
        return $result->fetchSingle();
    }
}
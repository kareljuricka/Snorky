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

    /**
     * Set active language
     * @param string $lang language identifier
     */
    public function setLang($lang) {
        $this->language = $lang;
        $_SESSION["webLanguage"] = $this->language;
    }

    /**
     * Get active language
     * @return string language identifier
     */
    public function getLang() {
        if (isset($_SESSION["webLanguage"]))
            $this->language = $_SESSION["webLanguage"];
        return $this->language;
    }

    /**
     * Get value of specific context variable
     * @param  string $pageTemplate        template name
     * @param  string $contextVariableName context var name
     * @return string                      context var value
     */
    public function getContextVariableValue($pageTemplate, $contextVariableName) {

        $contextVarsTable = "context_variables_" . $this->language;

        if(!globals::checkDatabaseTableExists($this->instanceRegister->get("configurator")->getDatabasePrefix() . $contextVarsTable))
            throw new Exception("Database table for language '" . $this->language . "' doesn't exist",0);

        $result = \dibi::query("SELECT value FROM [:prefix:".$contextVarsTable."] WHERE [pageTemplate] = %s AND [name] = %s", $pageTemplate, $contextVariableName);
        
        return $result->fetchSingle();
    }
}
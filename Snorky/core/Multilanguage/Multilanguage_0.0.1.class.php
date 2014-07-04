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

    public function __construct() {

        // Init register of instance
        $this->instanceRegister = Register::getRegistr("instance");
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

        \dibi::query("SELECT value FROM [:prefix:context_varibles_cz] WHERE [pageTemplate] = %s AND [variableName] = %s", $pageTemplate, $contextVariableName);
    }
}
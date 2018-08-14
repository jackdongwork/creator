<?php

/**
 * @name Action_MisFz_View_Index
 * @desc /mis/
 */
class Action_Index extends MisFz_Action_Base
{
    public function invoke() {
        if ($_SERVER['REQUEST_URI'] == '/misfz' || $_SERVER['REQUEST_URI'] == '/misfz/') {
            $this->redirect("/misfz/view");
        }
        
        $content = file_get_contents("/home/homework/webroot/static/misfz/index.html");
        $this->_processLog();
        echo $content;
        exit;
    }
}
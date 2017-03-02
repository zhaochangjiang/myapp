<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\common;

use communal\common\UtilsResultData;

/**
 * Description of ReusltData
 *
 * @author changjiang
 */
class ReusltData extends UtilsResultData
{

    private $javascriptContent;


    function getJavascriptContent()
    {
        return $this->javascriptContent;
    }

    function setJavascriptContent($javascriptContent)
    {
        $this->javascriptContent = $javascriptContent;
    }


    //put your code here
}

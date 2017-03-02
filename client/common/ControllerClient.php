<?php

namespace client\common;

use framework\bin\AController;
use client\common\ClientResultData;

/*
   * To change this license header, choose License Headers in Project Properties.
   * To change this template file, choose Tools | Templates
   * and open the template in the editor.
   */

/**
 * Description of ClientController
 *
 * @author zhaocj
 */
class ControllerClient extends AController
{

    //  private $clientResultData = null;
    public $outputCategory = 'json'; //'json','obj'

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);
    }

    public function init()
    {
        parent::init();
    }

    /**
     * 显示数据
     * @param type $result
     */
    public function output(ClientResultData $result)
    {
        switch ($this->outputCategory) {
            case 'json':
                die(json_encode($result));
                break;
            default:
                die(var_export($result, true));
                break;
        }
    }

}
  
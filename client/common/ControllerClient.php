<?php

namespace client\common;

use framework\bin\base\AController;
use client\common\ClientResultData;
use framework\bin\utils\ADesEncrypt;

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
        $this->init();
    }

    public function init()
    {

        parent::init();
        $clientResultData = new ClientResultData();
        $sessionId = $clientResultData->getSessionid();
        $token = $this->accessToken($sessionId);
        if (empty($this->params['accessToken'])) {
            $clientResultData->setCode(100);
            $clientResultData->setData($token);
            $this->output($clientResultData);
        } elseif ($token !== $this->params['accessToken']) {
            $clientResultData->setCode(101);
            $clientResultData->setData('token is error!');
            $this->output($clientResultData);
        }
    }


    protected function accessToken($sessionId)
    {
        ADesEncrypt::encrypt($sessionId);
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
  
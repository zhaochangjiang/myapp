<?php

namespace client\common;

use framework\bin\base\AController;
use framework\bin\dataFormat\ResultClient;
use framework\bin\utils\ADesEncrypt;
use framework\bin\dataFormat\ErrorCode;

/**
 * Description of ClientController
 *
 * @author zhaocj
 */
class ControllerClient extends AController
{

    //  private $clientResultData = null;
    protected $result;//返回的数据

    //输出数据的格式
    public $outputCategory = 'json'; //'json','obj'

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);
        $this->init();
    }

    public function init()
    {

        parent::init();
        $this->result = new ResultClient();

        //校验令牌
        $this->authAccessToken();


    }

    /**
     * 令牌校验
     * return void
     */
    protected function authAccessToken()
    {
        $sessionId = session_id();
        $token = $this->accessToken($sessionId);

        if (empty($this->params['accessToken'])) {

            $returnData = ErrorCode::$ERRORACCESSTOKEN;
            $returnData['code'] = $sessionId;
            $this->result->setResult($returnData);
            $this->output($this->result);
            return;
        }

        if ($token !== $this->params['accessToken']) {

            $this->result->setResult(ErrorCode::$ERRORACCESSTOKENERROR);
            $this->output($this->result);
            return;
        }
    }

    /**
     * @param string $sessionId
     * @return string
     */
    protected function accessToken($sessionId)
    {
        return ADesEncrypt::encrypt($sessionId);
    }

    /**
     * 显示数据
     * @param ClientResultData $result
     * @return void
     */
    public function output(ClientResultData $result)
    {
        switch ($this->outputCategory) {
            case 'json':
                json_encode($result);
                break;
            default:
                echo var_export($result, true);
                break;
        }
        exit;
    }

}
  
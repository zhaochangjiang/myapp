<?php

namespace client\modules\site\controllers;

use client\common\ControllerClient;
use client\common\ClientResultData;
use client\common\ErrorCode;
use communal\models\user\UserMainModel;

use framework\App;

/**
 * Description of PassportController
 *
 * @author zhaocj
 */
class ControllerPassport extends ControllerClient
{

    protected $clientResultData;

    /**
     * 初始化功能
     *
     */
    public function init()
    {
        parent::init();
        $this->_initClientResultData();
    }

    private function _initClientResultData()
    {
        $this->clientResultData = ClientResultData::getInstance();
    }

    /**
     * 退出页面的界面
     * @author Karl.zhao<zhaocj2009@126.com>
     * @since 2016/09/20
     */
    public function actionLogout()
    {
        App::$app->session->sessionDestroy();
        $this->clientResultData->setResult(ErrorCode::$SUCCESS);
        $this->outPut($this->clientResultData);
    }

    /**
     * 注册页面提交页面
     * @author Karl.zhao<zhaocj2009@126.com>
     * @since 2016/09/20
     */
    public function actionFrameRegister()
    {

    }

    /**
     * 登录表单提交页面
     * @author Karl.zhao<zhaocj2009@126.com>
     * @since 2016/09/20
     *
     */
    public function actionIframeLogin()
    {
       
        if (empty($this->params['username'])) {
            $this->clientResultData->setResult(ErrorCode::$USERNAMENOTNULL);
        }

        if (empty($this->params['password'])) {
            $this->clientResultData->setResult(ErrorCode::$PASSWORDNOTNULL);
        }

        if ($this->clientResultData->getCode() == 201) {
            $this->outPut($this->clientResultData);
        }

        $userMainModel = new UserMainModel();

        $this->clientResultData = $userMainModel->login($this->params);

        $this->outPut($this->clientResultData);
    }

}

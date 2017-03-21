<?php

namespace client\modules\site\controllers;

use client\common\ControllerClient;
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


    /**
     * 退出页面的界面
     * @author Karl.zhao<zhaocj2009@126.com>
     * @since 2016/09/20
     */
    public function actionLogout()
    {
        App::$app->session->sessionDestroy();
        $this->result->setResult(ErrorCode::$SUCCESS);
        $this->outPut($this->result);
    }

    /**
     * 注册页面提交页面
     * @author Karl.zhao<zhaocj2009@126.com>
     * @since 2016/09/20
     */
    public function actionIFrameRegister()
    {

    }

    /**
     * 登录表单提交页面
     * @author Karl.zhao<zhaocj2009@126.com>
     * @since 2016/09/20
     *
     */
    public function actionIFrameLogin()
    {

        if (empty($this->params['username'])) {
            $this->result->setResult(ErrorCode::$USERNAMENOTNULL);
        }

        if (empty($this->params['password'])) {
            $this->result->setResult(ErrorCode::$PASSWORDNOTNULL);
        }

        if (201 == (int)$this->result->getCode()) {
            $this->outPut($this->result);
        }
        stop($this->params);
        $userMainModel = new UserMainModel();
        $this->outPut($userMainModel->login($this->params));
    }

}

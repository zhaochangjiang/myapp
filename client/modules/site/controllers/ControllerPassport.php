<?php

namespace client\modules\site\controllers;

use client\common\ControllerClient;
use communal\models\user\UserMainModel;

use framework\App;
use framework\bin\dataFormat\ErrorCode;

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
     * 登录功能参数过滤
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     *
     */
    private function _filterIFrameLogin()
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
    }

    /**
     * 登录表单提交页面
     * @author Karl.zhao<zhaocj2009@126.com>
     * @since 2016/09/20
     *
     */
    public function actionIFrameLogin()
    {

        //参数条件过滤
        $this->_filterIFrameLogin();

        $userMainModel = new UserMainModel();
        $this->outPut($userMainModel->login($this->params));
    }

}

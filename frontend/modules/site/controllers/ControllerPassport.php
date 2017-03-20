<?php

namespace frontend\modules\site\controllers;

use frontend\common\ControllerFrontend;
use framework\lib\captcha\CCaptchaAction;
use frontend\common\FrontendResultContent;
use framework\App;

/**
 * Description of passport
 *
 * @author zhaocj
 */
class ControllerPassport extends ControllerFrontend
{

    public $layout = 'passport';

    /**
     * 退出登录
     */
    public function actionLogout()
    {

        $result = $this->httpConnectionByBase(array('Passport', 'logout'), null, $this->params);
        //  stop($result);
        header("Location:" . App::$app->parameters->domain['web']);
        App::$app->session->sessionDestroy();
    }

    /**
     * 登录界面
     */
    public function actionLogin()
    {

        $this->data['goto'] = $this->getInput('goto');
        $this->render();
    }

    /**
     * 注册表单提交
     */
    public function actionIframeRegister()
    {

        $this->data['goto'] = base64_decode($this->params('goto'));
        $result = (array)parent::httpConnectionByBase(
            array('Passport', 'iframeRegister'), array(), $this->params);
        $resultData = FrontendResultContent::getInstanceAnother();

        $jsString = '';
        if ($result->code != 200) {
            $jsString .= 'parent.showerror("' . $result->message . '");';
        }
        App::setSessionArray($result['data']);
        //默认注册成功跳转
        $resultData->setJavascriptContent($jsString, empty($this->data['goto']) ? $this->getDefaultLoginGoto() : $this->data['goto']);
        $this->outPutIframeMessage($resultData);
    }

    /**
     * 登录表单提交
     * @author karl.zhao<zhaocj2009@126.com>
     * @since 2016/09/19
     *
     */
    public function actionIframeLogin()
    {

        $this->data['goto'] = base64_decode($this->getInput('goto'));

        $result = (array)$this->httpConnectionByBase(['Passport', 'iframeLogin']
            , [], $this->params);
        $jsString = '';
        if (200 != $result['code']) {
            $jsString .= 'parent.showerror("' . $result['message'] . '");';
        }
        App::$app->session->setSessionArray($result['data']);

        $resultData = FrontendResultContent::getInstanceAnother();
        //默认登录成功跳转
        $resultData->setJavascriptContent($jsString, empty($this->data['goto']) ? $this->getDefaultLoginGoto() : $this->data['goto']);
        $this->outPutIframeMessage($resultData);
    }

    /**
     * 验证码请求
     */
    public function actionAuthcode()
    {
        $cCaptchaAction = new CCaptchaAction ();
        $cCaptchaAction->run(true);
    }

    /**
     *
     */
    public function actionForget()
    {
        $this->render();
    }

    /**
     *
     */
    public function actionRegister()
    {
        $this->data['goto'] = $this->getInput('goto');

        $this->render();
    }

}

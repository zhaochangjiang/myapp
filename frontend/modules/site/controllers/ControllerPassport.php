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
class ControllerPassport extends ControllerFrontend {

    public $layout = 'passport';

    public function init() {

        parent::init();
    }

    /**
     * 退出登录
     */
    public function actionLogout() {

        $result = $this->httpConnectionByBase(array('Passport','logout'), null, $this->params);
        //  stop($result);
        header("Location:" . App::base()->params['domain']['web']);
        App::sessionDestroy();
    }

    /**
     * 登录界面
     */
    public function actionLogin() {
        $this->data['goto'] = $this->getInput('goto');
        $this->render();
    }

    /**
     * 注册表单提交
     */
    public function actionIframeRegister() {

        $this->params = $this->getRequestParams();
        $this->data['goto'] = base64_decode($this->params('goto'));
        $result = (array) parent::httpConnectionByBase(
                        array('Passport','iframeRegister'), array(), $this->params);
        $resultData = FrontendResultContent::getInstanceAnother();

        $jsString = '';
        if ($result->code != 200) {
            $jsString.='parent.showerror("' . $result->message . '");';
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
    public function actionIframeLogin() {
//        $_POST = array(
//            'username' => 'admin',
//            'password' => '111111'
//        );
        $this->params = $this->getRequestParams();
        $this->data['goto'] = base64_decode($this->getInput('goto'));

        $result = (array) $this->httpConnectionByBase(array('Passport', 'iframeLogin')
                        , null, $this->params);
        $jsString = '';
        if ($result['code'] != 200) {
            $jsString.='parent.showerror("' . $result['message'] . '");';
        }
        App::setSessionArray($result['data']);

        $resultData = FrontendResultContent::getInstanceAnother();
        //默认登录成功跳转
        $resultData->setJavascriptContent($jsString, empty($this->data['goto']) ? $this->getDefaultLoginGoto() : $this->data['goto']);
        $this->outPutIframeMessage($resultData);
    }

    /**
     * 验证码请求
     */
    public function actionAuthcode() {
        $cCaptchaAction = new CCaptchaAction ( );
        $cCaptchaAction->run(true);
    }

    /**
     * 
     */
    public function actionForget() {
        $this->render();
    }

    /**
     * 
     */
    public function actionRegister() {
        $this->data['goto'] = $this->getInput('goto');

        $this->render();
    }

}

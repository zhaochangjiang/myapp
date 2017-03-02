<?php

/**
 * Description of SiteController
 *
 * @author zhaocj
 */

namespace frontend\modules\site\controllers;

use frontend\common\ControllerUserCommon;
use framework\App;

class ControllerUser extends ControllerUserCommon
{

    public function actionIndex()
    {
        //      stop(App::getSession());

        $this->render();
    }

    public function actionSetting()
    {
        $this->render();
    }

}
  
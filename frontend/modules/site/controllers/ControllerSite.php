<?php

namespace frontend\modules\site\controllers;

use frontend\common\ControllerFrontend;
use framework\App;

class ControllerSite extends ControllerFrontend
{

    /**
     *
     */
    public function actionIndex()
    {
        $this->render();
    }

    /**
     *
     */
    public function action404()
    {
        $this->layout = 'error';
        $this->render();
    }

}
  
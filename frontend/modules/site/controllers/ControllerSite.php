<?php

  /**
   * Description of SiteController
   *
   * @author zhaocj
   */

  namespace frontend\modules\site\controllers;

  use frontend\common\ControllerFrontend;
  use framework\App;

  class ControllerSite extends ControllerFrontend
  {

      public function actionIndex()
      {

          $this->render();
          print_r(App::getSession());
      }

      public function action404()
      {
          $this->layout = 'error';
          $this->render();
      }

  }
  
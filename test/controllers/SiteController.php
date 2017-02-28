<?php

  namespace test\controllers;

  use test\common\FrontendController;

  /**
   * Description of Passport
   *
   * @author zhaocj
   */
  class SiteController extends FrontendController
  {

      public function actionIndex()
      {
          $this->render();
      }

  }
  
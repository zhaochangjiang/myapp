<?php

  namespace test\controllers;

  use test\common\FrontendController;

  /**
   * Description of Passport
   *
   * @author zhaocj
   */
  class PassportController extends FrontendController
  {

      /**
       * 
       */
      public function actionIframeLogin()
      {
          $this->params = array(
              'username' => 'admin',
              'password' => '111111',
          );

          $result = $this->httpConnectionByBase('Passport', 'iframeLogin', null, $this->params);

          print_r($result);
          exit;
      }

  }
  
<?php

  namespace communal\models\user;

  use framework\bin\AModel;
  use client\common\ClientResultData;
  use client\common\ErrorCode;
  use framework\App;

  /**
   * Description of UserMainModel
   *
   * @author zhaocj
   */
  class UserMainModel extends AModel
  {

      protected $linkName = 'user';

      public function tableName()
      {
          return '{{main}}';
      }

      public function login($params)
      {

          $clientResultData = ClientResultData::getInstance();

          $params['password'] = $this->addPassword($params['password']);

          $userData = $this->find($params);

          if (empty($userData))
          {//如果用户存在
              $clientResultData->setResult(ErrorCode::$USERNOTEXISTS);
              return $clientResultData;
          }
          unset($userData['password'], $userData['flag_del']);
          $clientResultData->setResult(ErrorCode::$LOGINSUCCESS);
          $clientResultData->setData($userData);
          App::setSessionArray($userData);
          return $clientResultData;
      }

      public function addPassword($password)
      {
          return sha1($password);
      }

  }
  
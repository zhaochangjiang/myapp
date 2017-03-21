<?php

namespace communal\models\user;

use framework\bin\database\AModel;
use client\common\ResultClient;
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
    private $result;

    public function tableName()
    {
        return '{{main}}';
    }

    public function login($params)
    {

        $this->result = ResultClient::getInstance();

        $params['password'] = $this->addPassword($params['password']);

        $userData = $this->find($params);


        //如果用户不存在
        if (empty($userData)) {
            $this->result->setResult(ErrorCode::$USERNOTEXISTS);
            return $this->result;
        }

        unset($userData['password'], $userData['flag_del']);

        $this->result->setResult(ErrorCode::$LOGINSUCCESS);
        $this->result->setData($userData);

        App::$app->session->setSessionArray($userData);
        return $this->result;
    }

    /**
     * 加密密码
     * @param $password
     * @return string
     */
    private function addPassword($password)
    {
        return sha1($password);
    }

}
  
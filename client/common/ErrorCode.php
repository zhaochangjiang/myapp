<?php

namespace client\common;

/**
 * Description of ErrorCode
 *
 * @author zhaocj
 */
class ErrorCode
{

    public $code = 0;
    public $message = '';
    public $data = null;
    public static $USERNAMENOTNULL = array(
        'code' => '201',
        'message' => '请输入用户名！',
        'data' => ' username is permit null!',
    );
    public static $PASSWORDNOTNULL = array(
        'code' => '201',
        'message' => '请输入密码！',
        'data' => ' password is permit null!',
    );
    public static $USERNOTEXISTS = array(
        'code' => '201',
        'message' => '你输入的用户名或密码不正确！',
        'data' => ' username or password is wrong!',
    );
    public static $LOGINSUCCESS = array(
        'code' => '200',
        'message' => '登录成功！',
        'data' => ' login success',
    );
    public static $SUCCESS = array(
        'code' => '200',
        'message' => '操作成功！',
        'data' => ' operate success',
    );
    public static $ERRORACCESSTOKEN = array(
        'code' => '100',
        'message' => 'access_token is null',
        'data' => ' access_token is null',
    );
    public static $ERRORACCESSTOKENERROR = array(
        'code' => '101',
        'message' => 'access_token is error',
        'data' => ' access_token is error',
    );

}
  
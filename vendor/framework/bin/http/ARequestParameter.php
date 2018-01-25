<?php
/**
 * Created by PhpStorm.
 * @Author: karl.zhao<zhaocj2009@hotmail.com>
 * @Date: 2017/3/15
 * @Time: 23:21
 */

namespace framework\bin\http;

use framework\bin\dataFormat\ErrorCode;

class ARequestParameter
{
    private static $post;
    private static $get;
    private static $request;
    private static $session;
    private static $_self;
    private static $server;

    private function __construct()
    {
        $this->setGet(empty($_GET) ? [] : $_GET);
        $this->setPost(empty($_POST) ? [] : $_POST);
        $this->setRequest(empty($_REQUEST) ? [] : $_REQUEST);
        $this->setServer(empty($_SERVER) ? [] : $_SERVER);

        //清空系统函数
        $_GET     = null;
        $_POST    = null;
        $_REQUEST = null;
        $_SERVER  = null;
    }

    /**
     * @return mixed
     */
    public static function getServer()
    {
        return self::$server;
    }

    /**
     * @param mixed $server
     */
    public static function setServer($server)
    {
        self::$server = $server;
    }


    /**
     * 单次请求的参数单例模式
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * @return ARequestParameter
     */
    public static function getSingleton()
    {

        if (null === self::$_self) {
            self::$_self = new ARequestParameter();
        }

        return self::$_self;
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return self::$post;
    }

    /**
     * @param mixed $post
     */
    public function setPost($post)
    {
        self::$post = $post;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getGet($key = '')
    {
        if (!empty($key)) {
            return isset(self::$get[$key]) ? self::$get[$key] : '';
        }
        return self::$get;
    }

    /**
     * @param mixed $get
     */
    public function setGet($get)
    {
        self::$get = $get;
    }

    public function getRequestByKey($key)
    {
        if (!isset(self::$request[$key])) {
            return null;
        }
        return self::$request[$key];
    }


    public function getServerByKey($key)
    {
        if (!isset(self::$server[$key])) {
            return null;

        }
        return self::$server[$key];
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {

        return self::$request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        self::$request = $request;
    }


}
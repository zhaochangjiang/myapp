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

    /**
     * @var array $_POST
     */
    private static $post = [];

    /**
     * @var array $_GET
     */
    private static $get = [];
    /**
     * @var array $_REQUEST
     */
    private static $request = [];

    /**
     * @var array
     */
    private static $session = [];
    /**
     * @var ARequestParameter
     */
    private static $_self = null;

    /**
     * @var array $_SERVER
     */
    private static $server = [];

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

    /**
     * 向本次请求get参数中添加信息
     * @param $get
     */
    public function addGet($get)
    {
        self::$get = array_merge((array)self::$get, (array)$get);
    }

    /**
     * 向本次请求get参数中添加信息
     * @param $request
     */
    public function addRequest($request)
    {
        self::$request = array_merge((array)self::$request, (array)$request);
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
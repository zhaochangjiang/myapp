<?php
/**
 * Created by PhpStorm.
 * @Author: karl.zhao<zhaocj2009@hotmail.com>
 * @Date: 2017/3/15
 * @Time: 23:21
 */

namespace framework\bin\http;

class ARequestParameter
{
    private $post;
    private $get;
    private $request;
    private static $content;

    private function __construct()
    {

    }

    public static function getSingleton()
    {
        if (null === self::$_self) {
            self::$content = new self;
        }
        return self::$content;
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param mixed $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getGet($key = '')
    {
        if (!empty($key)) {
            return isset($this->get[$key]) ? $this->get[$key] : '';
        }
        return $this->get;
    }

    /**
     * @param mixed $get
     */
    public function setGet($get)
    {
        $this->get = $get;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }


}
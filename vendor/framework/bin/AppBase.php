<?php
/**
 * 所有类的基类
 * Created by PhpStorm.
 * User: karl.zhao
 * Date: 2017/3/9
 * Time: 10:58
 */

namespace framework\bin;


abstract class AppBase
{
    // 初始化方法，留待具体实现类需要时去实现
    public function init()
    {
    }

    /**
     * 开始方法
     * @return mixed
     */
    public function before()
    {
    }

    /**
     * 结束方法
     * @return mixed
     */
    public function after()
    {
    }

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {

        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
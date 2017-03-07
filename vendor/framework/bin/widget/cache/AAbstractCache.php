<?php
namespace framework\bin\cache;

/**
 * 缓存抽象类
 * Created by PhpStorm.
 * User: karl.zhao
 * Date: 2017/3/6
 * Time: 17:13
 */

abstract class AAbstractCache implements ACache
{

    //数据格式,目前支持 string ,php ,json格式
    protected $dataFormat = 'string';


    //缓存的数据库
    protected $database = '';

    //数据的键前缀
    protected $prefix = '';


    public function __construct()
    {

    }




    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public abstract function set($key, $value, $lifeTime);

    /**
     * @param $key
     * @return mixed
     */
    public abstract function get($key);

    /**
     * @param $key
     * @return mixed
     */
    public abstract function delete($key);

    /**
     * @return string
     */
    public function getDataFormat()
    {
        return $this->dataFormat;
    }

    /**
     * @param string $dataFormat
     */
    public function setDataFormat($dataFormat)
    {
        $this->dataFormat = $dataFormat;
    }
}
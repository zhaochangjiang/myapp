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


    /**
     * 简单缓存操作
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * @param $_config
     * @return array *
     */
    public static function cache($_config)
    {
        //   $instance    =
        self:: getInstance();
        $cache_array = $_config ['cache'];
        $resultData = array();
        foreach ($cache_array as $key => $val) {
            $class = ucfirst($val ['class']);
            switch ($class) {
                case 'Redis' : // redis
                    $resultData [$key] = new RedisClass($val ['host'], $val ['port']);
                    break;
                case 'FileCache' : // 文件缓存
                    $fileCache = new FileCacheClass ();
                    $val ['file_name_prefix'] != '' && $fileCache->setCacheDir($val ['file_name_prefix']);
                    $val ['mode'] != '' && $fileCache->setCacheMode($val ['mode']);
                    $resultData [$key] = $fileCache;
                    break;
            }
        }
        unset($_config);
        return $resultData;
    }

}
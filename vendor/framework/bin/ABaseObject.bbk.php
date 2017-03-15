<?php

namespace framework\bin;
/**
 * 基础对象
 *
 * @author heypigg
 */
class ABaseObject
{

    public static $arr;
    public static $cache;

//    private $redis;

    public function __construct($config)
    {
        self::$cache = $config ['cache'];
        unset($config['cache']);
        foreach ($config as $key => $val) {

            switch ($key) {
                case 'urlManager':
                    $this->$key = $this->_initObject($val);
                    break;
                default:
                    $this->$key = $val;
                    break;
            }
        }
        foreach (self::$cache as $key => $val) {
            self::$arr[$key] = $val;
        }
    }


    /**
     * 实例化对象
     * @return ABaseObject
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * 初始化对象
     * @param $initObjectArgument
     * @return mixed
     */
    private function _initObject(array $initObjectArgument)
    {
        $className = $initObjectArgument['class'];
        unset($initObjectArgument['class']);
        $object = new $className();

        foreach ($initObjectArgument as $param => $argument) {
            $function = "set" . ucfirst($param);
            $object->$function($argument);
        }
        return $object;
    }

    private function __get($property_name)
    {
        switch (self::$arr[$property_name]['class']) {
            case 'Redis':
                $this->$property_name = new RedisClass(self::$arr[$property_name]['host'], self::$arr[$property_name]['port']);
                break;
            case 'FileCache':
                $fileCache = new FileCacheClass ();
                self::$arr[$property_name]['file_name_prefix'] != '' && $fileCache->setCacheDir(self::$arr[$property_name]['file_name_prefix']);
                self::$arr[$property_name] ['mode'] != '' && $fileCache->setCacheMode(self::$arr[$property_name] ['mode']);
                $this->$property_name = $fileCache;
                break;
            case 'RedisNew':
                $this->$property_name = new RedisNewClass(self::$arr[$property_name]['host'], self::$arr[$property_name]['port']);
                break;
            case 'RedisCache':
                $this->$property_name = new RedisCacheClass(self::$arr[$property_name]['host'], self::$arr[$property_name]['port'], self::$arr[$property_name]['prefix']);
                break;

        }
        return $this->$property_name;
    }

    private function __set($property_name, $value)
    {
        $this->$property_name = $value;
    }

}

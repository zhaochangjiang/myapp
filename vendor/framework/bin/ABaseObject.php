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
            $this->$key = $val;
        }
        foreach (self::$cache as $key => $val) {
            self::$arr[$key] = $val;
        }
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

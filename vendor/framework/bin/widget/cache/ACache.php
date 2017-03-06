<?php
namespace framework\bin\cache;

/**
 * Created by PhpStorm.
 * User: karl.zhao
 * Date: 2017/3/6
 * Time: 17:13
 */
interface ACache
{
    public function set($key, $value);

    public function get($key);

    public function delete($key);
}
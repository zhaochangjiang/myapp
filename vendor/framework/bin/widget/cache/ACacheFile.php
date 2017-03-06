<?php
/**
 * Created by PhpStorm.
 * User: karl.zhao
 * Date: 2017/3/6
 * Time: 17:18
 */

namespace framework\bin\cache;

use framework\App;

class ACacheFile implements ACache
{
    private $cacheFileDirectory = '';//缓存文件所在目录

    private $fileName = ''; //缓存文件文件名


    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    private function setCacheFileDirectory($directory)
    {
        $this->cacheFileDirectory = $directory;
    }

    public function set($key, $value)
    {
        // TODO: Implement set() method.
    }

    public function get($key)
    {
        return;
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return true;
    }
}
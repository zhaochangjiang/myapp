<?php
/**
 * Created by PhpStorm.
 * User: karl.zhao
 * Date: 2017/3/6
 * Time: 17:18
 */

namespace framework\bin\cache;

use framework\App;

class ACacheFile extends AAbstractCache
{
    private $cacheFileDirectory = '';//缓存文件所在目录

    private $fileName = ''; //缓存文件文件名

    //是否为PHP代码
    private $isPhpCode = false;

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * 设置默认的缓存存储路径
     * ACacheFile constructor.
     */
    public function __construct()
    {
        $this->setCacheFileDirectory();
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $this->prefix . $fileName;
    }

    /**
     * 设置缓存的默认目录
     */
    private function setDefaultCacheFileDirectory()
    {
        $this->cacheFileDirectory = App:: getBasePath() . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }


    /**
     * 设置缓存存储的路径
     * @param string $directory
     * @return string
     */
    public function setCacheFileDirectory($directory = '')
    {
        if (!empty($directory)) {
            return $this->cacheFileDirectory = $directory;
        }
        $this->setDefaultCacheFileDirectory();
    }

    /**
     * @param $key
     * @param $value
     * @param int $lifeTime
     * @return bool
     * @throws RuntimeException
     */
    public function set($key, $value, $lifeTime = 0)
    {
        if (empty($lifeTime)) {
            return true;
        }
        switch ($this->dataFormat) {
            case 'string'://如果存储的是字符串
                break;
            case 'json'://如果json数据
                $value = json_encode($value);
                break;
            case 'php'://如果存储PHP 数据
                $this->isPhpCode = true;
                break;
            default:
                throw new RuntimeException('the data format must be in array("string","json")，the error is at line:' . __LINE__ . ',in file:' . __FILE__, FRAME_THROW_EXCEPTION);
                break;
        }
        $cache = array(
            'dataFormat' => $this->dataFormat,
            'contents' => $value,
            'expire' => FRAME_TIMESTAMP + $lifeTime,
            'mtime' => FRAME_TIMESTAMP,
        );

        //写数据到文件
        return $this->_filePutContent($this->getFileName(), $cache);

    }


    /**
     * @param $key
     * @return array|bool
     * @throws RuntimeException
     */
    public function get($key)
    {
        $this->isPhpCode = false;
        switch ($this->dataFormat) {
            case 'string':

                break;

            case 'json':

                break;
            case 'php'://如果存储PHP 数据
                $this->isPhpCode = true;
                break;
            default:
                throw new RuntimeException('the data format must be in array("string","json")，the error is at line:' . __LINE__ . ',in file:' . __FILE__, FRAME_THROW_EXCEPTION);
                break;
        }

        //获得缓存文件内容
        return $this->_fileGetContents($this->getFileName());
    }

    /**
     * 删除文件
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return unlink($this->getFileName());
    }

    /**
     * 从文件得到数据
     *
     * @param  sring $file
     * @return boolean|array
     */
    private function _fileGetContents($file)
    {
        if (!is_file($file)) {
            return false;
        }

        if (!$this->isPhpCode) {
            $f = fopen($file, 'r');
            $data = fread($f, filesize($file));
            fclose($f);
            return unserialize($data);
        }

        if (file_exists($file)) {
            return include_once $file;
        }

    }


    /**
     * 写入文件
     * @param $file
     * @param $contents
     * @param $isPhpCode
     * @return bool
     */
    private function _filePutContent($file, $contents)
    {
        $contents = (!$this->isPhpCode) ? '' : '<?php' . PHP_EOL . ' // mktime: ' . FRAME_TIMESTAMP . PHP_EOL . ' return ' . var_export($contents, true) . PHP_EOL . '?>';

        $result = false;
        if ($f = fopen($file, 'w')) {
            flock($f, LOCK_EX);
            fseek($f, 0);
            ftruncate($f, 0);
            $tmp = fwrite($f, $contents);
            if (!($tmp === false)) {
                $result = true;
            }
            fclose($f);
        }
        chmod($file, 0755);
        return $result;
    }


}
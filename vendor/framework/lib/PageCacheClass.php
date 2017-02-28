<?php
namespace framework\lib;
class PageCacheClass extends AComponent {

    private $dirCache; //缓存目录,用相对目录
    public $lifeTime; //缓存生存生存时间单位秒
    private $fileName; //缓存文件(包括相对路径)
    private $cacheFlg; //是否开启缓存
    private $fileExt; //缓存文件的扩展名
    public $id;//片断缓存id

    public function __construct() {
        $this->dirCache = App::getBasePath() . '/cache/html/'.D_S. substr(md5($_SERVER['REQUEST_URI']),0,2);
        $this->lifeTime = 0; //默认为60秒
        $this->cacheFlg = true; //默认为开启
        $this->fileExt = '.html';
      
    }

  
    


    //获取缓存文件名
    public function init() {
        $this->id =  $this->id? md5($this->id.'Fragment Caching'):md5($_SERVER['REQUEST_URI']);
        $this->fileName = $this->id. $this->fileExt;
    }

    //开始缓存
    public function startCache() {
        if ($this->cacheFlg) {
            if (file_exists($this->dirCache .
            '/' . $this->fileName) //检查文件是否存在
            && filesize($this->dirCache . '/' . $this->fileName) > 0 //检查文件大小即文件缓存是否保存成功
            && $this->checkCacheExpire() && $this->checkCacheLife()) {
                echo file_get_contents($this->dirCache . '/' . $this->fileName); //读取缓存内容
                $this->cacheFlg = false; //关闭生成缓存
               return false;

            } else {
                ob_start(); //开始缓存
                return true;
            }
        }
    }

    //检查被请求文件在缓存未过期时间内是否被修改
    private function checkCacheExpire() {
        if (filemtime($this->dirCache . '/' . $this->fileName) > filemtime($_SERVER['SCRIPT_FILENAME'])) {
            return true;
        } else {
            return false;
        }
    }

    //检查缓存是否过期
    private function checkCacheLife() {
        if ($this->lifeTime == 0)
            return true;
        if (time() - filemtime($this->dirCache . '/' . $this->fileName) < $this->lifeTime) {
            return true;
        } else {
            return false;
        }
    }

    //缓存页面结束
    public function endCache() {
        if ($this->cacheFlg && $this->makeDir()) {
            $contents = ob_get_contents(); //得到页面要缓存的内容
            ob_end_flush();
            if (!$this->createFile($contents)) {
                @ unlink($this->dirCache . '/' . $this->fileName);
            }
        }
    }

    //创建缓存文件
    private function createFile($contents) {
        $fp = @ fopen($this->dirCache . '/' . $this->fileName, 'w');
        if (@ fwrite($fp, $contents)) {
            @ fclose($fp);
            return true;
        } else {
            @ fclose($fp);
            return false;
        }
    }

    //创建缓存目录
    function makeDir() {
        
        if (file_exists($this->dirCache))
            return true;
        $dir = explode('/', str_replace('\\', '/', $this->dirCache));
        $mdir = '';
        foreach ($dir as $val) {
            $mdir .= $val . "/";
            if ($val == '..' || $val == '.' || empty($val))
                continue;
            if (!file_exists($mdir)) {
                if (!@ mkdir($mdir, 0775)) {
                    return false;
                }
            }
        }
        return true;
    }

    //清除缓存
    function clearCache($getFile = '') {
        if (empty($getFile)) {
            $getFile = $this->dirCache;
        }
        if (file_exists($getFile) && is_dir($getFile)) {
            $handle = opendir($getFile);
            while (false != $file = readdir($handle)) {
                if ($file == '..' || $file == '.' || empty($file))
                    continue;
   
                if (strrchr($file, '.') == $this->fileExt) {
                    @ unlink($getFile . $file);
                }

                if (is_dir($getFile . $file)) {
                    if (!@ rmdir($getFile . $file)) {
                        $this->ClearCache($getFile . $file . '/');
                    }
                }
            }
            closedir($handle);
            $result = @ rmdir($getFile);
        } else {
            $result = true;
        }
        if (is_file($file))
            @ unlink($file);
        return $result;
    }

}

?> 
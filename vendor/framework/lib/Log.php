<?php

namespace framework\lib;

use framework\App;

/**
 * @author zhaocj
 * 写日志文件类
 */
class Log
{

    public static function set($fileName, $content, $param = null)
    {
        //将日志目录制定到默认的日志目录位置
        $fileName  = App::getBasePath() . D_S . 'log' . D_S . $fileName;
        $systemLog = new Log();
        $systemLog->write($fileName, $content);
    }

    /**
     * 向服务器写入一条数据
     * @param  $fileName -完整服务器可写文件路径
     * @param  $content  - 写入文件的内容
     * @return  boolean
     */
    public function write($fileName, $content)
    {
        $dirNameString = dirname($fileName);
        if (!file_exists($dirNameString))
        {
            App::createDir($dirNameString, 0777);
        }
        //if(mkdirByOs($fileName))
        //{
        $handle = fopen($fileName, 'a');
        if ($handle)
        {
            if (!fwrite($handle, '[' . date('Y-m-d H:i:s') . '] ' . $content . "\r\n"))
            {
                throw new Exception("The fileName:{$fileName} can not  writable!");
            }
            fclose($handle);
        }
        return true;
        //}
        //else
        //{
        //    throw new Exception("The dirname:{$fileName} is not  createable!");
        // }
    }

}


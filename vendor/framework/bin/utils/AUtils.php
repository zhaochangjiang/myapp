<?php

namespace framework\bin\utils;
use framework\bin\http\ARequestParameter;

/**
 * Description of AUtils
 *
 * @author zhaocj
 */
class AUtils
{

    /**
     * 当前页面的URL
     * @var type
     */
    static $nowUrl;

    /**
     * 切割字符串函数，主要用于页面显示列表
     * @param String $string
     * @param Integer $cutLength 切割的标题长度
     * @param $suffix string 生成的标题如果超出补充的字符串
     * @return String
     */
    public static function cutStringUtf8($string, $cutLength, $suffix = '...')
    {
        $returnString = '';
        $i = $n = $word_length = 0;
        $str_length = strlen($string); //字符串的字节数
        while (($i <= $str_length)) {
            $word_length += 1;
            $temp_str = substr($string, $i, 1);
            $ascCode = Ord($temp_str); //得到字符串中第$i位字符的ascii码
            if ($ascCode >= 224) {//如果ASCII位高与224，
                $n < $cutLength ? $returnString .= substr($string, $i, 3) : ''; //根据UTF-8编码规范，将3个连续的字符计为单个字符
                $i = $i + 3; //实际Byte计为3
                $n++; //字串长度计1
                continue;
            }
            if ($ascCode >= 192) {//如果ASCII位高与192，
                $n < $cutLength ? $returnString .= substr($string, $i, 2) : ''; //根据UTF-8编码规范，将2个连续的字符计为单个字符
                $i = $i + 2; //实际Byte计为2
                $n++; //字串长度计1
                continue;
            }
            if ($ascCode >= 65 && $ascCode <= 90) { //如果是大写字母，
                $n < $cutLength ? $returnString .= substr($string, $i, 1) : '';
                $i = $i + 1; //实际的Byte数仍计1个
                $n++; //但考虑整体美观，大写字母计成一个高位字符
                continue;
            } else {//其他情况下，包括小写字母和半角标点符号，
                $n < $cutLength ? $returnString .= substr($string, $i, 1) : '';
                $i = $i + 1; //实际的Byte数计1个
                $n = $n + 0.5; //小写字母和半角标点等与半个高位字符宽...
            }
        }
        $word_length = $word_length - 1;
        return $word_length > $cutLength ? "{$returnString}{$suffix}" : $returnString;
    }

    /**
     * 将一个对象转换成一个数组
     *
     * @param Object $object
     * @return multitype:
     */
    public static function objectToArray($object)
    {
        $result = array();
        $_array = is_object($object) ? get_object_vars($object) : $object;
        if (is_array($_array)) {
            foreach ($_array as $key => $value) {
                $result [$key] = (is_array($value) || is_object($value)) ? std_class_object_to_array($value) : $value;
            }
        }
        return $result;
    }

    public static function getARequestParameter()
    {
        return ARequestParameter::getSingleton();
    }

    /**
     * 获得网站的URL地址
     *
     * @return string
     */
    public static function baseUrl()
    {
        $server = self::getARequestParameter()->getServer();
        return self::getDomain() . substr($server['PHP_SELF'], 0, strrpos($server['PHP_SELF'], '/'));
    }

    /**
     * 多个分隔符分割字符串
     * @param array $delimiters 分割符
     * @param string $string 要分割的字符串
     * @return array
     */
    function multipleExplode($delimiters = array(), $string = '')
    {
        $mainDelim = $delimiters[count($delimiters) - 1]; // dernier

        array_pop($delimiters);


        foreach ($delimiters as $delimiter) {
            $string = str_replace($delimiter, $mainDelim, $string);
        }

        $result = explode($mainDelim, $string);

        return $result;
    }

    public static function base64encodeCurrentUrl()
    {
        if(empty(static::$nowUrl)){
            static::$nowUrl = self::currentUrl();
        }
        return base64_encode(static::$nowUrl);
    }

    /**
     * 当前url
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     *
     * @return string *
     */
    public static function currentUrl()
    {
        $server = self::getARequestParameter()->getServer();
        if (empty($baseurl)) {
            $php_self = $server['PHP_SELF'] ? $server['PHP_SELF'] : $server['SCRIPT_NAME'];
            $path_info = isset($server['PATH_INFO']) ? $server['PATH_INFO'] : '';
            $relate_url = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : $php_self . (isset($server['QUERY_STRING']) ? '?' . $server['QUERY_STRING'] : $path_info);
            $baseurl = (isset($server['SERVER_PORT']) && $server['SERVER_PORT'] == '443' ? 'https://' : 'http://') . (isset($server['HTTP_HOST']) ? $server['HTTP_HOST'] : '') . $relate_url;
        }
        return $baseurl;
    }

    /**
     * JS 跳转目录
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * * @param $href
     *
     */
    public static function redirect($href)
    {
        if ($href == 'back') {
            echo "<script type='text/javascript'>window.history.back('-1')</script>";
        } elseif ($href == 'back-2') {
            echo "<script type='text/javascript'>window.history.back('-2')</script>";
        } else {
            header("Location:{$href}");
        }
        exit;
    }

    /**
     * 字符串特殊字符转义函数
     * Enter description here ...
     * @param unknown_type $string
     * @param  $quote boolean
     * @return string
     */
    public static function mAddSlashes(&$string, $quote = false)
    {
        !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
        if (!MAGIC_QUOTES_GPC) {
            if (is_array($string)) {
                foreach ($string as $key => $val) {
                    $string[$key] = maddslashes($val);
                }
            } else {
                if (!$quote)
                    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
                else
                    $string = htmlentities($string, ENT_COMPAT, 'UTF-8');
                //$string = addslashes($string);
            }
        }
        return $string;
    }

    /**
     *创建一个目录
     *
     * @param String $dirname
     * @param type $mode Description
     * @return boolean
     */
    public static function directoryCreate($dirname, $mode)
    {
        if (is_dir($dirname) || mkdir($dirname, $mode)) {
            return true;
        }
        if (!self:: directoryCreate(dirname($dirname), $mode)) {
            return false;
        }
        return mkdir($dirname, $mode);
    }

    /**
     * 递归删除目录
     * @param string $path
     * @return  boolean
     */
    public static function directoryDelete($path)
    {
        if (!file_exists($path)) {
            return true;
        }
        $dh = opendir($path);
        while (($d = readdir($dh)) !== false) {
            if (in_array($d, array(
                '.',
                '..'))) {//如果为.或..
                continue;
            }
            $tmp = $path . DIRECTORY_SEPARATOR . $d;

            //如果为文件 //如果为目录
            (!is_dir($tmp)) ? unlink($tmp) : self::directoryDelete($tmp);
        }
        closedir($dh);
        rmdir($path);
        //如果删除成功，则返回TRUE，如果删除失败则返回FALSE
        if (!file_exists($tmp)) {
            return true;
        }
        return false;
    }

    /**
     * 获得真实ip
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @return bool
     */
    public static function getRealIp()
    {
        $server = self::getARequestParameter()->getServer();

        $ip = false;
        if (!empty($server["HTTP_CLIENT_IP"])) {
            $ip = $server["HTTP_CLIENT_IP"];
        }
        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $server['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi("^(10|172\.16|192\.168)\.", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    /**
     * 获得当前的域名
     *
     * @return string
     */
    public static function getDomain()
    {
        $server = ARequestParameter::getSingleton()->getServer();
        /* 协议 */
        $protocol = (isset($server['HTTPS']) && (strtolower($server['HTTPS']) != 'off')) ? 'https://' : 'http://';

        /* 域名或IP地址 */
        if (isset($server['HTTP_X_FORWARDED_HOST'])) {
            $host = $server['HTTP_X_FORWARDED_HOST'];
        } elseif (isset($server['HTTP_HOST'])) {
            $host = $server['HTTP_HOST'];
        } else {
            /* 端口 */
            if (isset($server['SERVER_PORT'])) {
                $port = ':' . $server['SERVER_PORT'];

                if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
                    $port = '';
                }
            } else {
                $port = '';
            }

            if (isset($server['SERVER_NAME'])) {
                $host = $server['SERVER_NAME'] . $port;
            } elseif (isset($server['SERVER_ADDR'])) {
                $host = $server['SERVER_ADDR'] . $port;
            }
        }

        return $protocol . $host;
    }
}
  
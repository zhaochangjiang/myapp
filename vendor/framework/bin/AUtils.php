<?php

namespace framework\bin;

/**
 * Description of AUtils
 *
 * @author zhaocj
 */
class AUtils
{

    public static function zhcut($str, $len, $dot = "")
    {
        if ($len == 0) {
            return $str;
        }
        $olen = strlen($str);
        if ($olen == 0) {
            return "";
        }
        $len *= 2;
        $count = 0;
        for ($i = 0; $i < $olen; $i++) {
            $value = ord($str [$i]);
            if ($value > 127) {
                if ($value >= 192 && $value <= 223) {
                    $i++;
                }
                if ($value >= 224 && $value <= 239) {
                    $i += 2;
                }
                $count++;
            }
            $count++;
            if ($count >= $len) {
                break;
            }
        }
        $bk = substr($str, 0, $i + 1);
        return $bk == $str ? $bk : $bk . $dot;
    }

    /**
     * 当前页面的URL
     * @var type
     */
    static $nowUrl;

    public static function base64encodeCurrentUrl()
    {
        if (static::$nowUrl) {
            return base64_encode(self::$nowUrl);
        }
        static::$nowUrl = currentUrl();
        return base64_encode(static::$nowUrl);
    }

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

}
  
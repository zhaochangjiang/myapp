<?php
/**
 * Created by PhpStorm.
 * @Author: karl.zhao<zhaocj2009@hotmail.com>
 * @Date: 2017/3/20
 * @Time: 23:18
 */

namespace framework\bin\utils;


class ADesEncrypt
{












    private static $token = TOKEN;

    /**
     * 加密算法
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * * @param $str
     * @return string *
     */
    static function encrypt($str)
    {

        $str = str_replace("\n", "", $str);

        $str = str_replace("\t", "", $str);

        $str = str_replace("\r", "", $str);

        $key= substr(md5(self::$token), 0, 24);

        $td = mcrypt_module_open('tripledes', '', 'ecb', '');

        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        mcrypt_generic_init($td, self::$token, $iv);

        $encrypted_data = mcrypt_generic($td, $str);

        mcrypt_generic_deinit($td);

        mcrypt_module_close($td);

        return trim(chop(base64_encode($encrypted_data)));


        $block = mcrypt_get_block_size('des', 'ecb');
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);
        return mcrypt_encrypt(MCRYPT_DES, self::$token, $str, MCRYPT_MODE_ECB);
    }

    /**
     * 解密算法
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * * @param $str
     * @return string *
     */
    static function decrypt($str)
    {
        $str = mcrypt_decrypt(MCRYPT_DES,  self::$token, $str, MCRYPT_MODE_ECB);
        $len = strlen($str);
        // $block = mcrypt_get_block_size('des', 'ecb');
        $pad = ord($str[$len - 1]);
        return substr($str, 0, $len - $pad);
    }
}
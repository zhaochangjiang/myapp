<?php
namespace framework\lib;
/**
 * 用户提交表单的验证
 * @author zhaocj
 */
class Verify
{


    /**
     * 根据不同的类型，验证该数据是否满足要求
     *
     * @param
     *            $type
     * @param
     *            $value
     * @param
     *            $notempty
     * @param
     *            $name
     * @return multitype:boolean string unknown
     */
    public static function doByType($type, $value, $notempty = true, $name = '')
    {
        $result = array(
            'status' => false,
            'message' => '',
            'typename' => $type,
            'colum' => $name,
            'isNull' => false
        );
        $verify = self::$$type;
        // 如果名称为空，则用默认的名称
        if (empty($name)) {
            $name = $verify ['name'];
            $result ['colum'] = $name;
        }
        if (empty($value) && $notempty === true) {

            $result ['message'] = '您输入的' . $name . '！';
            $result ['isNull'] = true;
        }
        if (preg_match($verify ['preg'], $value)) {
            $result = array(
                'status' => true,
                'message' => '',
                'typename' => $type,
                'colum' => $name,
                'isNull' => true
            );
        } else {
            $result ['message'] = '请输入正确格式的' . $name . '！';
        }
        return $result;
    }

    /**
     * 验证真实姓名
     */
    public static $realname = array(
        'name' => '姓名',
        'preg' => '/^[A-Za-z0-9\\u4e00-\\u9fa5]+$/'
    );

    /**
     * 浮点数
     */
    public static $decmal = array(
        'name' => '小数',
        'preg' => "/^([+-]?)\\d*\\.\\d+$/"
    );

    /**
     * 正浮点数
     */
    public static $decmal1 = array(
        'name' => '正小数',
        'preg' => "/^[1-9]\\d*.\\d*|0.\\d*[1-9]\\d*$/"
    );

    /**
     * 负浮点数
     */
    public static $decmal2 = array(
        'name' => '负小数',
        'preg' => "/^-([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*)$/"
    );

    /**
     * 浮点数
     */
    public static $decmal3 = "/^-?([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*|0?.0+|0)$/";

    /**
     * 非负浮点数（正浮点数 + 0）
     */
    public static $decmal4 = array(
        'name' => '零或正小数',
        'preg' => "/^[1-9]\\d*.\\d*|0.\\d*[1-9]\\d*|0?.0+|0$"
    );

    /**
     * 非正浮点数（负浮点数 + 0）
     */
    public static $decmal5 = array(
        'name' => '零或负小数',
        'preg' => "/^(-([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*))|0?.0+|0$/"
    );

    /**
     * 整数
     */
    public static $intege = array(
        'name' => '整数',
        'preg' => "/^-?[1-9]\\d*$/"
    );

    /**
     * 正整数
     */
    public static $intege1 = array(
        'name' => '正整数',
        'preg' => "/^[1-9]\\d*$/"
    );

    /**
     * 负整数
     */
    public static $intege2 = array(
        'name' => '负整数',
        'preg' => "/^-[1-9]\\d*$/"
    );

    /**
     * 数字
     */
    public static $number = array(
        'name' => '数字',
        'preg' => "/^([+-]?)\\d*\\.?\\d+$/"
    );

    /**
     * 正数（正整数 + 0）
     */
    public static $positiveInteger = array(
        'name' => '零或正整数',
        'preg' => "/^[1-9]\\d*|0$/"
    );

    /**
     * 负数（负整数 + 0）
     */
    public static $negativeInteger = array(
        'name' => '零或负整数',
        'preg' => "/^-[1-9]\\d*|0$/"
    );

    /**
     * 仅ACSII字符
     */
    public static $ascii = array(
        'name' => 'ACSII码',
        'preg' => "/^[\\x00-\\xFF]+$/"
    );

    /**
     * 仅中文
     */
    public static $chinese = array(
        'name' => '中文汉字',
        'preg' => "/^[\\u4e00-\\u9fa5]+$/"
    );

    /**
     * 颜色
     */
    public static $color = array(
        'name' => '颜色',
        'preg' => "/^[a-fA-F0-9]{6}$/"
    );

    /**
     * 日期
     */
    public static $date = array(
        'name' => '日期',
        'preg' => "/^\\d{4}(\\-|\\/|\.)\\d{1,2}\\1\\d{1,2}$/"
    );

    /**
     * 邮件
     */
    public static $email = array(
        'name' => '邮箱',
        'preg' => "/^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$/"
    );

    /**
     * 身份证
     */
    public static $idcard = array(
        'name' => '身份证',
        //'preg' => "/^[1-9]([0-9]{14}|[0-9]{17})$/" 
        'preg' => "/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9Xx])$/"
    );

    /**
     * ip地址
     */
    public static $ip4 = array(
        'name' => 'IP地址',
        'preg' => "/^(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)$/"
    );

    /**
     * 字母
     */
    public static $letter = array(
        'name' => '字母',
        'preg' => "/^[A-Za-z]+$/"
    );

    /**
     * 小写字母
     */
    public static $letter_l = array(
        'name' => '小写字母',
        'preg' => "/^[a-z]+$/"
    );

    /**
     * 大写字母
     */
    public static $letter_u = array(
        'name' => '大写字母',
        'preg' => "/^[A-Z]+$/"
    );

    /**
     * 手机
     */
    public static $mobile = array(
        'name' => '手机',
//			'preg' => '/^1[3|5][0-9]\d{4,8}$/' 
        'preg' => '/^1[345897]\d{9}$/ ',
        'jspreg' => '/^1[345897]\d{9}$/ '
    );

    /**
     * 电话号
     */
    public static $tel = array(
        'name' => '电话号码',
        'preg' => "/(^(86)\-(0\d{2,3})\-(\d{7,8})\-(\d{1,4})$)|(^0(\d{2,3})\-(\d{7,8})$)|(^0(\d{2,3})\-(\d{7,8})\-(\d{1,4})$)|(^(86)\-(\d{3,4})\-(\d{7,8})$)/"
    );

    /**
     * 非空
     */
    public static $notempty = array(
        'name' => '',
        'preg' => "/^\\S+$/"
    );

    /**
     * 密码
     */
    public static $password = array(
        'name' => '密码',
        'preg' => "/^[A-Za-z0-9_\-@!&%#?]{6,16}$/"
    );

    /**
     * 图片
     */
    public static $picture = array(
        'name' => '图片格式',
        'preg' => "/(.*)\\.(jpg|bmp|gif|ico|pcx|jpeg|tif|png|raw|tga)$/"
    );

    /**
     * QQ号码
     */
    public static $qq = array(
        'name' => 'QQ号码',
        'preg' => "/^[1-9][0-9]{4,10}$/"
    );

    /**
     * 压缩文件
     */
    public static $rar = array(
        'name' => '压缩文件格式',
        'preg' => "/(.*)\\.(rar|zip|7zip|tgz)$/"
    );

    /**
     * url
     */
    public static $url = array(
        'name' => '链接',
        'preg' => "/^http[s]? = \\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$/"
    );

    /**
     * 用户名
     */
    public static $username = array(
        'name' => '用户名',
        'preg' => "/^[A-Za-z0-9@!#?_]{5,25}$/"
    );

    /**
     * 用户名|邮箱|手机
     */
    public static $usernamecommon = array(
        'name' => '用户名',
        'preg' => "/(^[A-Za-z0-9@!#?_]{5,25}$)|(^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$)|(^1[3|5][0-9]\d{4,8}$)/"
    );

    /**
     * 邮编
     */
    public static $zipcode = array(
        'name' => '邮政编码',
        'preg' => "/^\\d{6}$/"
    );

    /*
      # 函数功能：计算身份证号码中的检校码
      # 函数名称：idcard_verify_number
      # 参数表 ：string $idcard_base 身份证号码的前十七位
      # 返回值 ：string 检校码
      # 更新时间：Fri Mar 28 09:50:19 CST 2008
     */

    private static function idcard_verify_number($idcard_base)
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); //debug 加权因子
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); //debug 校验码对应值  
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

    /*
      # 函数功能：将15位身份证升级到18位
      # 函数名称：idcard_15to18
      # 参数表 ：string $idcard 十五位身份证号码
      # 返回值 ：string
      # 更新时间：Fri Mar 28 09:49:13 CST 2008
     */

    private static function idcard_15to18($idcard)
    {
        if (strlen($idcard) != 15) {
            return false;
        } else {// 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false) {
                $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 15);
            } else {
                $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 15);
            }
        }
        $idcard = $idcard . self::idcard_verify_number($idcard);
        return $idcard;
    }

    /*
      # 函数功能：18位身份证校验码有效性检查
      # 函数名称：idcard_checksum18
      # 参数表 ：string $idcard 十八位身份证号码
      # 返回值 ：bool
      # 更新时间：Fri Mar 28 09:48:36 CST 2008
     */

    private static function idcard_checksum18($idcard)
    {
        if (strlen($idcard) != 18) {
            return false;
        }
        $idcard_base = substr($idcard, 0, 17);
        if (self::idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
            return false;
        } else {
            return true;
        }
    }

    /*
      # 函数功能：身份证号码检查接口函数
      # 函数名称：check_id
      # 参数表 ：string $idcard 身份证号码
      # 返回值 ：bool 是否正确
      # 更新时间：Fri Mar 28 09:47:43 CST 2008
     */

    public static function checkIdcard($idcard)
    {
        if (strlen($idcard) == 15 || strlen($idcard) == 18) {
            if (strlen($idcard) == 15) {
                $idcard = self::idcard_15to18($idcard);
            }
            if (self::idcard_checksum18($idcard)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}

?>
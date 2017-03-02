<?php
namespace frontend\common;
/**
 * Description of DataManager
 *
 * @author zhaocj
 */
class DataManager
{

    public static function getLogoUrl()
    {
        return 'source/images/logo.png';
    }

    public static function getCopyRight()
    {
        $year = date('Y');
        return '&copy; Copyright ' . $year . '-' . ($year + 1) . ' 版权所有.';
    }
}
  
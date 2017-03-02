<?php

namespace backend\common;

/**
 * Description of BackenBaseData
 *
 * @author zhaocj
 */
class BackenBaseData
{

    /**
     *
     * @return type
     */
    public static function getSuperAdminFlagArray()
    {
        return array(
            array(
                'key' => 'yes',
                'name' => '是',
                'isDefault' => false
            ),
            array(
                'key' => 'no',
                'name' => '否',
                'isDefault' => true
            )
        );
    }


    /**
     *
     * @param type $param
     * @param type $default
     * @return type
     */
    public static function outputIsSuperAdmin($param, $default = '-')
    {

        $arrayData = self::getSuperAdminFlagArray();

        foreach ($arrayData as $value) {
            if ($value['key'] === $param) {
                return $value['name'];
            }
        }
        return $default;
    }

}
  
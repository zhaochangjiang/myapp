<?php

namespace communal\lib;

use communal\models\admin\ModelSwitch;

/**
 * 系统开关管理
 * @version 1.0
 * @author karl.zhao<zhaocj2009@126.com>
 */
class SystemSwitch
{

    /**
     * 获得系统中所有的后台开关页=展示的数据
     */
    public static function getAllShowSwitch()
    {
        $modelSwitch = new ModelSwitch();
        return $modelSwitch->getAllShowSwitch();
    }

    //put your code here
}

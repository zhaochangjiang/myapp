<?php

namespace communal\models\admin;

use framework\bin\AModel;
use framework\App;

/**
 * Description of ModelSeverlist
 *
 * @author zhaocj
 */
class ModelSeverlist extends AModel
{

    protected $linkName = 'admin';

    public function tableName()
    {
        return '`{{serverlist}}`';
    }

    /**
     * 获得本服务器的唯一KEY(根据服务器的IP地址和机房造)
     *
     * @param type $serverIp
     * @param type $serverMachineRoom
     */
    public function getUniqueKey($serverIp, $serverMachineRoom)
    {

        $condition = array(
            'ip_addr' => $serverIp,
            'machine_room' => $serverMachineRoom
        );
        $result = $this->find($condition);
        $unique_key = '';
        if (empty($result)) {
            $i = 0;
            while (true) {
                $i++;
                $unique_key = $condition['unique_key'] = $this->_initKeyString(4);
                $flag = $this->addServerData($condition);
                if ($flag) {
                    return $unique_key;
                }
                if ($i > 5) {
                    throw new Exception("add data to {{serverlist}} error!");
                }
            }
        }
        return $result['unique_key'];
    }

    /**
     * 生成一个自定义长度的字符串
     */
    private function _initKeyString($length)
    {
        $str = '';
        $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    public function addServerData($condition)
    {
        return $this->add($condition);
    }

}
  
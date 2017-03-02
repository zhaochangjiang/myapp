<?php

namespace communal\models\admin\permit;

use framework\bin\AModel;
use communal\common\ResultData;
use  communal\models\admin\permit\ModelAdminUser;

/**
 * Description of PermitGroupModel
 *
 * @author zhaocj
 */
class ModelGroupUser extends AModel
{

    public $linkName = 'admin';

    public function tableName()
    {
        return '`{{groupuser}}`';
    }

    /**
     * 添加数据 如果有唯一约束则忽略
     * @param type $data
     * @return type
     */
    public function replaceData($data)
    {
        return $this->replace($data);
    }

    /**
     *
     * @param type $param
     * @return ResultData
     */
    public function deleteData($param)
    {
        return $this->delete($param);
    }

    public function getUserNameByUid($list)
    {
        $uidArray = array();
        foreach ($list as $value) {
            if (!empty($value['admin_userid'])) {
                $uidArray[] = $value['admin_userid'];
            }
        }
        $adminUserById = array();
        if (!empty($uidArray)) {
            $modelAdminUser = new ModelAdminUser();
            $adminUserArray = $modelAdminUser->getAdminUserByIds($uidArray);
            foreach ($adminUserArray as $value) {
                $adminUserById[$value['uid']] = $value;
            }
        }

        foreach ($list as $key => $value) {

            if (isset($adminUserById[$value['admin_userid']])) {
                $list[$key] = array_merge($adminUserById[$value['admin_userid']], $value);
            }
        }

        return $list;
    }

    public function getRow($data)
    {
        return $this->find($data);
    }

    public function getUserAccountCount($data)
    {
        return $this->findAll(array(
            'group_id' => array(
                'doType' => 'in',
                'value' => $data)), 'group_id as id,count(*) as adminUserCount', '', '', 'group_id');
    }

    public function getList($params)
    {
        $params['page'] = (int)$params['page'] < 1 ? 1 : (int)$params['page'];

        $result['pageSize'] = '15';
        $condition = array();
        $orderBy = '';
        $limitString = (($params['page'] - 1) * $result['pageSize']) . ',' . $result['pageSize'];
        $groupBy = '';
        $feild = '';
        $temp = $this->find($condition, 'count(*) as count', $orderBy, $groupBy);

        $result['count'] = $temp['count'];

        $result['list'] = $this->findAll($condition, $feild, $orderBy, $limitString, $groupBy);
        return $result;
    }

}
  
<?php

namespace communal\models\data\category;

use framework\bin\database\AModel;
use communal\common\UtilsCommunalTools;
use Exception;

class ModelCategory extends AModel
{

    protected $linkName = 'data';

    protected function tableName()
    {
        return '{{category}}';
    }

    /**
     *
     * @param type $params
     * @return type
     */
    public function fetchOne($params)
    {
        if (empty($params['category_id'])) {
            return null;
        }
        return $this->find(array(
            'category_id' => $params['category_id']));
    }

    public function getUponAndChildData($up_groupid)
    {

        $data = array();
        while (true) {
            $permit = $this->find(array(
                'category_id' => $up_groupid));
            $temp['nowId'] = (string)$up_groupid;
            $up_groupid = $permit['up_groupid'];
            $temp['permitList'] = $this->findAll(array(
                'higher_up_id' => $up_groupid));
            array_unshift($data, $temp);
            if (empty($permit['higher_up_id'])) {
                break;
            }
        }
        // print_r($data);
        return $data;
    }

    public function addData($data)
    {
        if (empty($data['category_id'])) {//生成唯一的KEY
            $data['category_id'] = UtilsCommunalTools::createGuid();
        }
        $tmp = $this->find(array(
            'sku' => $data['sku']));
        if (!empty($tmp)) {
            throw new Exception("系统中已存在该属性！");
        } else {
            $this->add($data);
        }
        return true;
    }

    /**
     *
     * @param type $params
     */
    public function getList($params)
    {
        $params['page'] = (int)$params['page'] < 1 ? 1 : (int)$params['page'];

        $result['pageSize'] = 15;
        $condition = array();


        if (!empty($params['id'])) {
            $condition['uppermit_id'] = $params['id'];
        }

        $orderBy = '';
        $limitString = (($params['page'] - 1) * $result['pageSize']) . ',' . $result['pageSize'];
        $groupBy = '';
        $feild = '';
        $temp = $this->find($condition, 'count(*) as count', $orderBy, $groupBy);

        $result['count'] = $temp['count'];

        $result['list'] = $this->findAll($condition, $feild, $orderBy, $limitString, $groupBy);
        return $result;
    }

    public function getSku($params)
    {
        $skuString = '';
        if (!empty($params['higher_up_id'])) {
            $tmp = $this->find(array(
                'category_id' => $params['higher_up_id']));
            $skuString .= isset($tmp['sku']) ? $tmp['sku'] : '';
        }
        $skuString .= (empty($params['category_name']) ? '' : $params['category_name'] . ':') . strtolower($params['category_label']) . ';';
        return $skuString;
    }

    public function updateData($feilds, $condition)
    {
        $tmp = $this->find(array(
            'sku' => $feilds['sku']));
        if (!empty($tmp)) {
            throw new Exception("系统中已存在该属性！");
        } else {
            return $this->update($feilds, $condition);
        }
    }

    /**
     *
     * @param type $params
     * @return type
     */
    public function deleteData($params)
    {
        return $this->delete(array(
            'category_id' => $params['category_id']));
    }

}
  
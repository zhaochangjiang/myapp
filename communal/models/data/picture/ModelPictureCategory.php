<?php

namespace communal\models\data\picture;

use framework\bin\database\AModel;

use communal\common\ResultData;

/**
 * Description of ModelPictureCategory
 *
 * @author zhaocj
 */
class ModelPictureCategory extends AModel
{

    protected $linkName = 'admin';

    public function tableName()
    {
        return '{{picture_category}}';
    }

    public function getList($params)
    {

        $params['page'] = (int)$params['page'] < 1 ? 1 : (int)$params['page'];

        $result['pageSize'] = '15';
        $condition = array();


        if (!empty($params['picure_category_id'])) {
            $condition['picure_category_id'] = $params['id'];
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

    public function updateData($feild, $condition)
    {
        return $this->update($feild, $condition);
    }

    public function addData($data)
    {
        return $this->add($data);
    }

    /**
     *
     * @param type $param
     * @return ResultData
     */
    public function deleteData($param)
    {
        $resultData = ResultData::getInstance();
        if (empty($param['id'])) {
            return $resultData;
        }

        $this->delete(array(
            'picure_category_id' => $param['id']));

        return $resultData;
    }

    /**
     *
     * @param type $params
     * @return type
     */
    public function fetchOne($params)
    {
        if (empty($params['id'])) {
            return null;
        }
        return $this->find(array(
            'picure_category_id' => $params['id']));
    }

}
  
<?php

namespace communal\models\data\picture;

use framework\bin\database\AModel;


/**
 * Description of ModelPictureCategory
 *
 * @author zhaocj
 */
class ModelPicture extends AModel
{

    protected $linkName = 'data';

    public function tableName()
    {
        return '{{picture}}';
    }

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
            'id' => $params['id']));
    }

}
  
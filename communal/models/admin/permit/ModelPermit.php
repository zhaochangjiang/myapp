<?php

namespace communal\models\admin\permit;

use framework\bin\AModel;

/**
 * Description of PermitModel
 *
 * @author zhaocj
 */
class ModelPermit extends AModel
{

    public $linkName = 'admin';

    public function tableName()
    {
        return '`{{permit}}`';
    }

    public function updateData($feild, $condition)
    {
        return $this->update($feild, $condition);
    }

    public function addData($data)
    {
        return $this->add($data);
    }

    public function getChildListByUppid($uppid)
    {
        return $this->findAll(array(
            'uppermit_id' => $uppid));
    }

    public function getChildList($uppid)
    {
        if (empty($uppid)) {
            return array();
        }
        return $this->getChildListByUppid($uppid);
    }

    public function getPermitData($permitId)
    {
        $data = array();
        while (true) {
            $permit = $this->find(array(
                'id' => $permitId));

            $temp['nowId'] = $permitId;
            $permitId = $permit['uppermit_id'];
            $temp['permitList'] = $this->findAll(array(
                'uppermit_id' => $permitId));
            array_unshift($data, $temp);
            if (empty($permit['uppermit_id'])) {
                break;
            }
        }
        return $data;
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

    /**
     *
     * @param type $ids
     * @return type
     */
    public function getPermitByIds($ids)
    {
        return $this->findAll(array(
            'id' => array(
                'doType' => 'in',
                'value' => $ids)));
    }

    /**
     *
     * @param type $param
     * @return ResultData
     */
    public function deleteData($param)
    {
        $resultData = ResultData::getInstance();
        $this->delete($param);
        return $resultData;
    }

    public function getList($params)
    {

        $params['page'] = (int)$params['page'] < 1 ? 1 : (int)$params['page'];

        $result['pageSize'] = 15;
        $condition = array(
            'uppermit_id' => 0
        );


        if (!empty($params['id'])) {
            $condition['uppermit_id'] = $params['id'];
        }

        $orderBy = '';
        $limitString = (($params['page'] - 1) * $result['pageSize']) . ',' . $result['pageSize'];
        $groupBy = '';
        $feild = '';
        $temp = $this->find($condition, 'count(*) as count', $orderBy, $groupBy);

        $result['count'] = $temp['count'];

        $result['list'] = $this->leftUpandChildPermit($this->findAll($condition, $feild, $orderBy, $limitString, $groupBy));
        return $result;
    }

    public function getListPermitDataByNowPermitId($params)
    {
        $permId = $params['id'];

        $permit = $this->find(array(
            'id' => $permId));

        $permitData[] = $this->findAll(
            array(
                'uppermit_id' => (int)$permit['uppermit_id']
            )
        );

        $permitIdArray[] = array(
            'uppermit_id' => $permit['uppermit_id'],
            'id' => $permit['id']
        );
        while (true) {
            //如果上级ID为空
            if (empty($permit['uppermit_id'])) {
                break;
            }

            $permit = $this->find(array(
                'id' => $permit['uppermit_id']));


            array_unshift($permitData, $this->findAll(
                array(
                    'uppermit_id' => (int)$permit['uppermit_id']
                )
            ));

            array_unshift($permitIdArray, array(
                'id' => $permit['id'],
                'uppermit_id' => $permit['uppermit_id']
            ));
        }

        if ($permId) {
            $pData = $this->findAll(
                array(
                    'uppermit_id' => (int)$permId
                )
            );

            $permitIdArray[] = array(
                'id' => $permit['id'],
                'uppermit_id' => $permit['uppermit_id']
            );
            $permitData[] = $pData;
        }


        //   stop($permitData);
        return array(
            'permitData' => $permitData,
            'permitIdArray' => $permitIdArray);
        //   stop($permitData);
    }

    private function leftUpandChildPermit($data)
    {
        if (empty($data)) {
            return array();
        }
        $ids = $upids = array();
        foreach ($data as $value) {
            if (!empty($value['uppermit_id']) && !in_array($value['uppermit_id'], $upids)) {
                $upids[] = $value['uppermit_id'];
            }
            if (!empty($value['id']) && !in_array($value['id'], $ids)) {
                $ids[] = $value['id'];
            }
        }
        $permitUp = $this->findAll(array(
            'id' => array(
                'doType' => 'in',
                'value' => $upids)));
        $permitUpKeyValue = $permitChildKeyValue = array();
        foreach ($permitUp as $value) {
            $permitUpKeyValue[$value['id']] = $value;
        }

        $permitChild = $this->findAll(array(
            'uppermit_id' => array(
                'doType' => 'in',
                'value' => $ids)));

        foreach ($permitChild as $value) {
            $permitChildKeyValue[$value['uppermit_id']][] = $value;
        }
        foreach ($data as $key => $value) {
            if (!empty($value['uppermit_id']) && isset($permitUpKeyValue[$value['uppermit_id']])) {
                $data[$key]['upPermitName'] = $permitUpKeyValue[$value['uppermit_id']]['name'];
            }
            if (!empty($value['id']) && isset($permitChildKeyValue[$value['id']])) {
                $data[$key]['childPermit'] = $permitChildKeyValue[$value['id']];
            }
        }
        return $data;
    }

    public function getNowPermit($module, $controllerString, $action)
    {

        if (empty($module) && empty($controllerString) && empty($action)) {
            return array();
        }

        $condition = array(
            'module' => $module,
            'controller' => $controllerString,
            'action' => $action
        );
        if (empty($module)) {
            $condition['module'] = '';
        }
        if ($controllerString === 'Site') {
            $condition['controller'] = array(
                'doType' => 'in',
                'value' => array(
                    '',
                    'Site'));
        }
        if ($action === 'index') {
            $condition['action'] = array(
                'doType' => 'in',
                'value' => array(
                    '',
                    'index'));
        }
        return $this->find($condition);
    }

    /**
     *
     * @param type $module
     * @param type $action
     */
    private function getNowPermitLink($permit)
    {

        $permitIdArray[] = array(
            'id' => $permit['id'],
            'uppermit_id' => $permit['uppermit_id']
        );

        while (true) {
            //如果上级ID为空
            if (empty($permit['uppermit_id'])) {
                break;
            }
            $permit = $this->find(array(
                'id' => $permit['uppermit_id']));
            array_unshift($permitIdArray, array(
                'id' => $permit['id'],
                'uppermit_id' => $permit['uppermit_id']
            ));
        }
        return $permitIdArray;
    }

    /**
     * 获得一个权限的子权限
     * @param type $pemitId
     * @return array
     */
    public function getAllChildPermitId($pemitId)
    {
        $data = array();
        $upPermitArray = array(
            $pemitId);
        while (true) {
            $temp = $this->findAll(array(
                'uppermit_id' => array(
                    'doType' => 'in',
                    'value' => $upPermitArray)));
            if (empty($temp)) {
                break;
            }
            $tmp = array();
            foreach ($temp as $value) {
                $tmp[] = $value['id'];
            }
            $upPermitArray = $tmp;
            $data = array_merge($data, $tmp);
        }
        return $data;
    }


    /**
     *
     * @param type $module
     * @param type $action
     * @return type
     */
    public function getShowPermit($module, $controllerString, $action)
    {
        /**
         * 获得当前页面的权限ID
         */
        $permit = $this->getNowPermit($module, $controllerString, $action);

        $permitIdArray = $this->getNowPermitLink($permit);


        if (!empty($permit['id'])) {
            $permitData ['childPermit'] = $this->findAll(array(
                'uppermit_id' => array(
                    'doType' => 'in',
                    'value' => $permit['id'])));
        }


        $permitData ['header'] = $this->findAll(array(
            'uppermit_id' => array(
                'doType' => 'in',
                'value' => 0)), '', '`obyid` asc');
        $headerActive = array_shift($permitIdArray);
        $uppermitIdArray = array();
        foreach ($permitData ['header'] as $key => $value) {
            if (($value['id'] == $headerActive['id'])) {
                $permitData['header'][$key]['active'] = true;
                $uppermitIdArray[] = $value['id'];
            } else {
                $permitData['header'][$key]['active'] = false;
            }
        }

        $uppermitIdData = array();


        //   stop($permitIdArray);

        $i = 0;
        while (true) {
            $temp = $this->findAll(array(
                'uppermit_id' => array(
                    'doType' => 'in',
                    'value' => $uppermitIdArray)), '', '`obyid` asc');
            $uppermitIdArray = array();
            $permitList = array();

            foreach ($temp as $value) {
                $permitList[$value['uppermit_id']][] = $value;
                $uppermitIdArray[] = $value['id'];
            }
            $uppermitIdData[] = $permitList;
            if ($i > 1) {
                break;
            }
            $i++;
        }

        $permitData['left'] = $this->organizationPermit($uppermitIdData, $permitIdArray);
        //    stop($permitData['left']);
        return $permitData;
    }

    private function organizationPermit($permitList, $permitIdArray)
    {
        $temp = array_shift($permitIdArray);
        $temp1 = array_shift($permitIdArray);
        $result = array_shift(array_shift($permitList));
        $child = array_shift($permitList);

        foreach ($result as $key => $value) {

            $result[$key]['active'] = ($value['id'] == $temp['id']) ? true : false;
            if (isset($child[$value['id']])) {
                foreach ($child[$value['id']] as $k => $v) {
                    $child[$value['id']][$k]['active'] = ($v['id'] == $temp1['id']) ? true : false;
                }
                $result[$key]['childList'] = $child[$value['id']];
            }
        }

        return $result;
    }

}
  
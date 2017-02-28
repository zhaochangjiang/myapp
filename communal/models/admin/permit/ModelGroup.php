<?php

  namespace communal\models\admin\permit;

  use framework\bin\AModel;
  use communal\common\ResultData;
 // use communal\models\admin\permit\ModelPermit;

  /**
   * Description of PermitGroupModel
   *
   * @author zhaocj
   */
  class ModelGroup extends AModel
  {

      public $linkName = 'admin';

      public function tableName()
      {
          return '`{{group}}`';
      }

      /**
       * 
       * @param type $uppid
       * @return type
       */
      public function getChildList($uppid)
      {
          if (empty($uppid))
          {
              return array();
          }
          return $this->findAll(array(
                      'up_groupid' => $uppid));
      }

      /**
       * 
       * @param type $up_groupid
       * @return type
       */
      public function getPermitData($up_groupid)
      {

          $data = array();
          while (true)
          {
              $permit = $this->find(array(
                  'id' => $up_groupid));

              $temp['nowId']      = $up_groupid;
              $up_groupid         = $permit['up_groupid'];
              $temp['permitList'] = $this->findAll(array(
                  'up_groupid' => $up_groupid));
              array_unshift($data, $temp);
              if (empty($permit['up_groupid']))
              {
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
          if (empty($params['id']))
          {
              return null;
          }
          return $this->find(array(
                      'id' => $params['id']));
      }

      /**
       * 
       * @param type $id
       * @return type
       */
      public function getOneData($id)
      {
          return $this->fetchOne(array(
                      'id' => $id));
      }

      /**
       * 
       * @param type $feild
       * @param type $condition
       * @return type
       */
      public function updateData($feild, $condition)
      {
          return $this->update($feild, $condition);
      }

      /**
       * 
       * @param type $data
       * @return type
       */
      public function addData($data)
      {
          return $this->add($data);
      }

      /**
       * 
       * @param array $params
       * @return type
       */
      public function getList($params)
      {
          $params['page'] = (int) $params['page'] < 1 ? 1 : (int) $params['page'];

          $result['pageSize'] = '15';
          $condition          = array();
          $orderBy            = '';
          $limitString        = (($params['page'] - 1) * $result['pageSize'] ) . ',' . $result['pageSize'];
          $groupBy            = '';
          $feild              = '';
          $temp               = $this->find($condition, 'count(*) as count', $orderBy, $groupBy);

          $result['count'] = $temp['count'];

          $result['list'] = $this->findAll($condition, $feild, $orderBy, $limitString, $groupBy);
          return $result;
      }

      /**
       * 
       * @param type $param
       * @return ResultData
       */
      public function deleteData($param)
      {
          $resultData = ResultData::getInstance();

          $this->delete(array(
              'id' => $param['id']));

          return $resultData;
      }

  }
  
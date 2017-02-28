<?php

  namespace communal\models\admin\permit;

  use framework\bin\AModel;
  use communal\common\ResultData;

  /**
   * Description of ModelAdminUser
   *
   * @author zhaocj
   */
  class ModelAdminUser extends AModel
  {

      public $linkName = 'admin';

      public function tableName()
      {
          return '{{user}}';
      }

      /**
       * 
       * @param type $param
       * @return type
       */
      public function getAdminUserByIds($param)
      {
          return $this->findAll(array(
                      'uid' => array(
                          'doType' => 'in',
                          'value'  => $param)));
      }

      /**
       * 
       * @param type $params
       * @return type
       */
      public function fetchOne($params)
      {
          if (empty($params['uid']))
          {
              return null;
          }
          return $this->find(array(
                      'uid' => $params['uid']));
      }

      /**
       * 添加数据
       * @param type $feild
       */
      public function addData($feild)
      {
          $this->add($feild);
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
       * @param type $param
       * @return ResultData
       */
      public function deleteData($param)
      {

          $resultData = ResultData::getInstance();

          $this->update(array(
              'isdel' => 'yes'), array(
              'uid' => $param['uid']));
          return $resultData;
      }

      public function getList($params)
      {

          $params['page'] = (int) $params['page'] < 1 ? 1 : (int) $params['page'];

          $result['pageSize'] = '15';


          $condition = array(
              'isdel' => 'no');


          if (!empty($params['id']))
          {
              //    $condition['uppermit_id'] = $params['id'];
          }

          $orderBy     = '';
          $limitString = (($params['page'] - 1) * $result['pageSize'] ) . ',' . $result['pageSize'];
          $groupBy     = '';
          $feild       = '';
          $temp        = $this->find($condition, 'count(*) as count', $orderBy, $groupBy);

          $result['count'] = $temp['count'];

          $result['list'] = $this->findAll($condition, $feild, $orderBy, $limitString, $groupBy);
          return $result;
      }

  }
  
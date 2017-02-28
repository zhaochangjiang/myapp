<?php

  namespace communal\models\admin\permit;

  use framework\bin\AModel;
  use communal\common\ResultData;
  use  communal\models\admin\permit\ModelPermit;
  use Exception;

  /**
   * Description of PermitGroupModel
   *
   * @author zhaocj
   */
  class ModelPermitGroup extends AModel
  {

      public $linkName = 'admin';

      public function tableName()
      {
          return '`{{permitgroup}}`';
      }

      public function getChildList($uppid)
      {
          $modelPermit = new ModelPermit();
          return $modelPermit->getChildList($uppid);
      }

      public function deletePermitGroupById($groupId)
      {
          $this->delete(array(
              'group_id' => $groupId));
      }

      public function permitListNotSuperAdmin($permitList, $session)
      {

          $pidArray = $this->_collectPermitId($permitList);

          $havePermit = $this->findAll(array(
              'permit_id' => array(
                  'doType' => 'in',
                  'value'  => $pidArray),
              'group_id'  => array(
                  'doType' => 'in',
                  'value'  => $this->_getUserGroupId($session))));
          $idList     = array();
          foreach ($havePermit as $value)
          {
              $idList[] = $value['permit_id'];
          }

          return $this->_dealPermit($permitList, $idList);
      }

      private function _getUserGroupId($session)
      {
          return array(
              3);
      }

      public function _dealPermit($permitList, $idList)
      {
          $pidArray = array();
          foreach ($permitList as $key => $value)
          {
              if (isset($value['id']))
              {
                  if (!in_array($value['id'], $idList))
                  {
                      unset($permitList[$key]);
                      continue;
                  }
                  if (isset($value['childList']))
                  {
                      $permitList[$key]['childList'] = $this->_dealPermit($value['childList'], $idList);
                  }
              }
              else
              {
                  $permitList[$key] = $this->_dealPermit($value, $idList);
              }
          }
          return $permitList;
      }

      private function _collectPermitId($permitList)
      {
          $pidArray = array();

          foreach ($permitList as $value)
          {
              if (isset($value['id']))
              {
                  $pidArray[] = $value['id'];
                  if (isset($value['childList']))
                  {
                      $tmp      = $this->_collectPermitId($value['childList']);
                      $pidArray = array_merge($pidArray, $tmp);
                  }
              }
              else
              {
                  $tmp      = $this->_collectPermitId($value);
                  $pidArray = array_merge($pidArray, $tmp);
              }
          }
          return $pidArray;
      }

      public function setAjaxsetpermit($param)
      {
          switch ($param['type'])
          {
              case 'close':
                  return $this->setAjaxsetClosepermit($param);
              case 'open':
                  return $this->setAjaxsetOpenpermit($param);
              default:
                  return '';
          }
      }

      private function setAjaxsetClosepermit($param)
      {
          $data = array(
              'group_id'  => $param['gid'],
              'permit_id' => $param['pid']);
          return $this->delete($data);
      }

      private function setAjaxsetOpenpermit($param)
      {
          $data = array(
              'group_id'  => $param['gid'],
              'permit_id' => $param['pid']);

          $temp = $this->find($data);
          if (empty($temp))
          {
              $this->add($data, $this->tableName(), true);
          }
          return true;
      }

      private function getPermitAndAllChildPermit($param)
      {
          $modelPermit = new ModelPermit();
          $data        = $modelPermit->getAllChildPermitId($param['pid']);
          $data[]      = $param['pid'];
          return $data;
      }

      private function setAjaxsetbatchClosepermit($param)
      {
          $data = $this->getPermitAndAllChildPermit($param);
          $this->startAffair();

          foreach ($data as $value)
          {
              $condition = array(
                  'group_id'  => $param['gid'],
                  'permit_id' => $value
              );
              $this->delete($condition);
          }
          $this->commit();
          return true;
      }

      /**
       * 批量添加用户组权限
       * @param type $param
       * @return boolean
       */
      private function setAjaxsetbatchOpenpermit($param)
      {

          $data = $this->getPermitAndAllChildPermit($param);

          $this->startAffair();
          $feilds = array();
          foreach ((array) $data as $value)
          {
              $feilds[] = array(
                  'permit_id' => $value,
                  'group_id'  => $param['gid']);
          }
          $this->addBatch($feilds, $this->tableName(), true);
          $this->commit();
          return true;
      }

      /**
       * 
       * @param type $param
       * @return type
       * @throws Exception
       */
      public function setAjaxsetbatchpermit($param)
      {
          switch ($param['type'])
          {
              case 'close':
                  return $this->setAjaxsetbatchClosepermit($param);
              case 'open':
                  return $this->setAjaxsetbatchOpenpermit($param);
              default:
                  throw new Exception('Params error! at "' . __FILE__ . '"  line ' . __LINE__);
          }
      }

      /**
       * 
       * @param type $nowPermitId
       * @return type
       */
      public function getPermitData($nowPermitId)
      {
          $modelPermit = new ModelPermit();
          return $modelPermit->getPermitData($nowPermitId);
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

      public function getPermitShow($param, $groupId)
      {
          $permitIds = array();

          foreach ($param['list'] as $value)
          {
              $permitIds[] = $value['id'];
          }

          $list = $this->findAll(array(
              'group_id'  => $groupId,
              'permit_id' => array(
                  'doType' => 'in',
                  'value'  => $permitIds)));
          $temp = array();
          foreach ($list as $key => $value)
          {
              $temp[$value['permit_id']] = $value;
          }
          foreach ($param['list'] as $key => $value)
          {
              $param['list'] [$key]['openFlag'] = (isset($temp[$value['id']])) ? true : false;
          }
          return $param;
          //stop($param);
      }

      public function getList($params)
      {
          $params['page'] = (int) $params['page'] < 1 ? 1 : (int) $params['page'];

          $result['pageSize'] = '15';
          $condition          = array();

          if (!empty($params['id']))
          {
              $condition['group_id'] = $params['id'];
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
  
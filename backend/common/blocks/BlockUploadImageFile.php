<?php

  namespace backend\common\blocks;

  use framework\bin\Ablocker;
  use communal\models\admin\permit\ModelPermitGroup;
  use \ArrayIterator;
  use communal\common\UtilsBootStrapFileUpload;
  
  /**
   * Description of newPHPClass
   *
   * @author zhaocj
   */
  class BlockUploadImageFile extends Ablocker
  {

      var $uploadParam;

      public function getJonUploadParam()
      {
          // print_r($this->uploadParam);
          foreach ($this->uploadParam as $key => $value)
          {
              if ($value === '')
              {
                  unset($this->uploadParam->$key);
              }
          }
          return json_encode($this->uploadParam);
      }

      /**
       * 
       */
      public function run()
      {
          //  $modelPermitGroup = new ModelPermitGroup;
          $id = $this->controllerObject->data['data']['id'];
          switch ($this->controllerObject->data['doType'])
          {
              case 'add':
                  $id = $this->controllerObject->data['data']['uppid'];
                  break;
              default:
                  break;
          }

          $this->uploadParam = new UtilsBootStrapFileUpload();
          $this->uploadParam->setUploadUrl($this->params['uploadUrl']);
          $this->uploadParam->setInitialPreviewShowDelete(true);

          foreach ((array) $this->params['initialPreview'] as $value)
          {
             
              $this->uploadParam->setInitialEveryPreview($value['pictureViewLocate'], $value['pictureViewConfig']);
          }

          $this->uploadParam = $this->_deleteObjectNullProperty($this->uploadParam);

          $this->render();
      }

      /**
       * 删除对象中的空属性
       * @param type $object
       */
      private function _deleteObjectNullProperty($object)
      {
          $objectContent = new ArrayIterator($object);
          foreach ($objectContent as $key => $value)
          {
              if ($value === null)
              {
                  unset($objectContent[$key]);
              }
          }
          return $objectContent;
      }

  }
  
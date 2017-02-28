<?php

  namespace backend\modules\data\blocks\category;

  use framework\bin\Ablocker;
  use communal\models\data\category\ModelCategory;

  /**
   * Description of CategoryStep
   *
   * @author zhaocj
   */
  class BlockStep extends Ablocker
  {

      private function _getModel()
      {
          if (empty($this->model))
          {
              $this->model = new ModelCategory();
          }
          return $this->model;
      }

      public function run()
      {
          $id = $this->controllerObject->data['data']['higher_up_id'];
          switch ($this->controllerObject->data['doType'])
          {
              case 'add':
                  $id = $this->controllerObject->data['data']['higher_up_id'];
                  break;
              default:
                  break;
          }
          $model              = $this->_getModel();
          $this->data['data'] = $model->getUponAndChildData($id);
       //   print_r( $this->data['data']);
          $this->render();
      }

  }
  
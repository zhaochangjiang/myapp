<?php

  /*
   * To change this license header, choose License Headers in Project Properties.
   * To change this template file, choose Tools | Templates
   * and open the template in the editor.
   */

  namespace backend\modules\permit\blocks\data;

  use framework\bin\Ablocker;
  use communal\models\admin\permit\ModelPermitGroup;

  /**
   * Description of newPHPClass
   *
   * @author zhaocj
   */
  class BlockPermitSelect extends Ablocker
  {

      public function run()
      {

          $modelPermitGroup = new ModelPermitGroup;
          $id               = $this->controllerObject->data['data']['id'];
          switch ($this->controllerObject->data['doType'])
          {
              case 'add':
                  $id = $this->controllerObject->data['data']['uppid'];
                  break;
              default:
                  break;
          }

          $this->data['data'] = $modelPermitGroup->getPermitData($id);

          $this->render();
      }

  }
  
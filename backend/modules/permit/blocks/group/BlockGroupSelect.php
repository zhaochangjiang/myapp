<?php

namespace backend\modules\permit\blocks\group;

use framework\bin\Ablocker;
use communal\models\admin\permit\ModelGroup;

/**
 * Description of PermitGroupSelect
 *
 * @author zhaocj
 */
class BlockGroupSelect extends Ablocker
{

    public function run()
    {
        $modelGroup = new ModelGroup;
        $id = $this->controllerObject->data['data']['id'];
        switch ($this->controllerObject->data['doType']) {
            case 'add':
                $id = $this->controllerObject->data['data']['uppid'];
                break;
            default:
                break;
        }

        $this->data['data'] = $modelGroup->getPermitData($id);

        $this->render();
    }

}
  
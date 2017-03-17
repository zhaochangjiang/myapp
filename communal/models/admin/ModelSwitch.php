<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace communal\models\admin;

use framework\bin\database\AModel;

/**
 * Description of ModelSwitch
 * @date 2016-12-30
 * @author changjiang
 */
class ModelSwitch extends AModel
{

    protected $linkName = 'admin';

    public function tableName()
    {
        return '`{{switch}}`';
    }

    /**
     *
     * @return type
     */
    public function getAllShowSwitch()
    {
        return $this->findAll(array('is_show' => 'yes'));
    }

    //put your code here
}

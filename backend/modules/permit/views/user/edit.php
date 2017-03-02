<?php

use communal\common\UtilsFormFormat;
// use backend\modules\permit\blocks\PermitGroupSelect;
use backend\common\BackenBaseData;

?>
<div class="box box-primary">
    <div class="box-body">
        <?php
        $urlParam = array(
            $this->controllerString,
            'iframe' . ucfirst($this->action),
            $this->moduleString);
        echo UtilsFormFormat::open($this->createUrl($urlParam, array(
            'uid' => $this->params['uid'],
            'dotype' => $this->params['doType'],
            'goto' => $this->params['goto'])), array(
            'class' => 'form-horizontal'));
        ?>
        <div class="box-body">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '组名'; ?></label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="text" name="name" placeholder="请填姓名"
                               value="<?php echo $this->data['data']['name']; ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">上级组</label>
                <?php
                echo $this->loadBlock('GroupSelect', array(
                    'nameSpace' => 'backend\modules\permit\blocks\group'));
                ?>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '超级管理？'; ?></label>
                <div class="col-lg-2">
                    <select name="super_admin" class="form-control">
                        <?php
                        $superAdminFlagArray = BackenBaseData::getSuperAdminFlagArray();
                        foreach ($superAdminFlagArray as $value) {
                            echo '<option ' . ($value['isDefault'] === true ? ' selected="selected" ' : '') . ' value="' . $value['key'] . '">' . BackenBaseData::outputIsSuperAdmin($value['key']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <div class=" form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">&nbsp;</label>
                <div class="col-lg-2">
                    <div class="input-group">
                        <?php
                        switch ($this->params['doType']) {
                            case 'update':
                                echo '<button class="btn btn-primary" type="submit">更新</button>';
                                break;
                            case 'add':
                                echo '<button class="btn btn-warning" type="submit">添加</button>';
                                break;
                            default:
                                break;
                        }
                        ?>
                    </div>
                </div>
                <div class="col-lg-8"><a href="<?php echo base64_decode($this->data['goto']); ?>" class="btn pull-right"
                                         type="button">返回</a></div>
            </div>
        </div>
        <?php UtilsFormFormat::close() ?>
    </div>
</div>

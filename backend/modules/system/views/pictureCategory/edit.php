<?php

use communal\common\UtilsFormFormat;

// use backend\widget\permit\PermitSelect;
?>
<div class="box box-primary">
    <div class="box-body">
        <?php
        $urlParam = array(
            $this->controllerString,
            'iframe' . ucfirst($this->action),
            $this->moduleString);
        echo UtilsFormFormat::open($this->createUrl($urlParam, array(
            'picure_category_id' => $this->data['data']['picure_category_id'],
            'dotype' => $this->data['doType'],
            'goto' => $this->data['goto'])), array(
            'class' => 'form-horizontal'));
        ?>
        <div class="box-body">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '类型名'; ?></label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="text" name="picure_categoryname" placeholder="请填写类型名"
                               value="<?php echo $this->data['data']['picure_categoryname']; ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '类型KEY'; ?></label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="text" name="picure_categorykey" placeholder="请填写类型KEY"
                               value="<?php echo $this->data['data']['picure_categorykey']; ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '类型savepath'; ?></label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="text" name="picure_savepath" placeholder="请填写savepath"
                               value="<?php echo $this->data['data']['picure_savepath']; ?>" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <div class=" form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">&nbsp;</label>
                <div class="col-lg-2">
                    <div class="input-group">
                        <?php
                        switch ($this->data['doType']) {
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

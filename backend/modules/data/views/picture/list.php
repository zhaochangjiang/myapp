<?php

use backend\common\Pager;
use framework\bin\utils\AUtils;

//权限验证准备
$permitDelete = $this->havePermit(array(
    $this->controllerString,
    'delete',
    $this->moduleString));
$permitEdit = $this->havePermit(array(
    $this->controllerString,
    'edit',
    $this->moduleString));
$permitHaveopertate = $permitEdit;
?>

<div class="box">
    <div class="box-body no-padding">
        <div class="box box-solid">
            <div class="row">
                <?php
                foreach ($this->data['listPermit']['permitData'] as $key => $value):
                    ?>
                    <div class="col-xs-6 col-md-3">
                        <a href="#" class="thumbnail">
                            <img src="<?php echo $value['pic'] ?>" alt="...">
                        </a>
                    </div>
                    <?php
                endforeach;
                ?>
            </div>
        </div>
    </div>
    <div class="box-footer clearfix">
        <?php if ($permitEdit): ?>
            <a class="btn btn-warning pull-left" href="<?php
            echo $this->createUrl(array(
                $this->controllerString,
                'edit',
                $this->moduleString), array(
                'type' => 'add',
                'uppid' => $this->params['id'],
                'goto' => base64_encode(AUtils::currentUrl())
            ));
            ?>">添加</a>
        <?php endif; ?>
        <?php echo $this->widget($this->data['pageObject']); ?>
    </div>
</div>

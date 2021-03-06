<?php

use backend\common\Pager;
use \framework\bin\utils\AUtils;
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
<div class="box box-solid">
    <ul class="list-group">
        <?php
        foreach ($this->data['listPermit']['permitData'] as $key => $value) {
            $temp = array_shift($this->data['listPermit']['permitIdArray']);
            if (empty($value)) {
                continue;
            }
            echo '<li  class="list-group-item">';
            echo ' <ul  class="list-inline">';
            foreach ($value as $v) {
                echo ' <li role="presentation" ><a class="' . ($v['id'] == $temp['id'] ? 'btn btn-success btn-sm' : '') . '" href="' . $this->createUrl(array(
                        $this->controllerString,
                        $this->action,
                        $this->moduleString), array(
                        'id' => $v['id'])) . '">' . $v['name'] . '</a></li>';
            }
            echo '</ul>';
            echo '</li>';
        }
        ?>
    </ul>
</div>
<div class="box">

    <div class="box-body no-padding">
        <table class="table ">
            <tbody>
            <tr>
                <?php
                if ($permitDelete) {
                    echo '<th>-</th>';
                }
                echo '<th  class="text-center">类型名</th>';
                echo '<th  class="text-center">key</th>';
                echo '<th  class="text-center">保存路径</th>';
                if ($permitHaveopertate) {
                    echo '<th style="width:100px" class="text-center">操作</th>';
                }
                ?>
            </tr>
            <?php
            foreach ($this->data['data']['list'] as $value) {
                echo '<tr>';
                if ($permitDelete) {
                    echo '<td><a onclick="doAjaxDelete(\'' . $this->createUrl(
                            array(
                                $this->controllerString,
                                'delete',
                                $this->moduleString)
                            , array(
                            'id' => $value['picure_category_id']
                        )) . '\',\'' . AUtils::currentUrl() . '\');return;" href="javascript:;">删除</a></td>';
                }
                echo '<td  class="text-center" >' . $this->outputHtml($value['picure_categoryname'], '-') . '</td>';
                echo '<td  class="text-center">' . $this->outputHtml($value['picure_categorykey'], '-') . '</td>';
                echo '<td  class="text-center">' . $this->outputHtml($value['picure_savepath'], '-') . '</td>';

                if ($permitHaveopertate) {
                    echo '<td style="width:100px"  class="text-center">';
                    echo '<a href="' . $this->createUrl(array(
                            $this->controllerString,
                            'edit',
                            $this->moduleString), array(
                            'id' => $value['picure_category_id'],
                            'doType' => 'update',
                            'goto' => $this->base64encodeCurrentUrl())) . '">编辑</a>';
                    echo '</td>';
                }
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="box-footer clearfix">
        <?php if ($permitEdit): ?>
            <a class="btn btn-warning pull-left" href="<?php
            echo $this->createUrl(array(
                $this->controllerString,
                'edit',
                $this->moduleString), array(
                'doType' => 'add',
                'uppid' => $this->params['id'],
                'goto' => base64_encode(AUtils::currentUrl())
            ));
            ?>">添加</a>
        <?php endif; ?>
        <?php echo $this->widget($this->data['pageObject']); ?>
    </div>
</div>

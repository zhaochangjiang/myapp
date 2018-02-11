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
<?php if ($this->data['data']['list']) { ?>
    <div class="box">
        <div class="box-body no-padding">
            <table class="table ">
                <tbody>
                <tr>
                    <?php
                    if ($permitDelete) {
                        echo '<th>-</th>';
                    }
                    echo '<th  class="text-center">权限名</th>';
                    echo '<th  class="text-center">module|controller|action</th>';
                    echo '<th  class="text-center">样式</th>';
                    echo '<th  class="text-center">上级权限</th>';
                    echo '<th  class="text-center">下级权限</th>';
                    echo '<th  class="text-center">排序</th>';
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
                                'id' => $value['id']
                            )) . '\',\'' . AUtils::currentUrl() . '\');return;" href="javascript:;">删除</a></td>';
                    }
                    echo '<td  class="text-center" >' . $this->outputHtml($value['name'], '-') . '</td>';

                    $moduleControllerAction = array();
                    $arrayContent = array(
                        'module',
                        'controller',
                        'action'
                    );
                    foreach ($arrayContent as $val) {
                        if (!empty($value[$val])) {
                            $moduleControllerAction[] = "<span title=\"{$val}\">{$value[$val]}</span>";
                        }
                    }
                    echo '<td  class="text-center">&nbsp;' . implode('&nbsp;&nbsp;', $moduleControllerAction) . '</td>';
                    echo '<td  class="text-center">' . $this->outputHtml($value['csscode'], '-') . '</td>';
                    echo '<td  class="text-center">' . $this->outputHtml($value['upPermitName'], '-') . '</td>';
                    echo '<td  class="text-center">' . (empty($value['childPermit']) ? '-' : '<a href="' . $this->createUrl(array(
                                $this->controllerString,
                                $this->action,
                                $this->moduleString), array(
                                'id' => $value['id'])) . '">查看</a>') . '</td>';
                    echo '<td  class="text-center">' . $this->outputHtml($value['obyid'], '-') . '</td>';
                    if ($permitHaveopertate) {
                        echo '<td style="width:100px"  class="text-center">';
                        echo '<a href="' . $this->createUrl(array(
                                $this->controllerString,
                                'edit',
                                $this->moduleString), array(
                                'id' => $value['id'],
                                'type' => 'update',
                                'goto' => $this->base64encodeCurrentUrl())) . '">编辑</a>';
                        echo '</td>';
                    }
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>

        </div>
    </div>
    <?php
} else {
    $this->loadViewCellCommon("DataNull", array('message' => ''));
}
?>
<div class="box-footer clearfix">
    <?php if ($permitEdit): ?>
        <a class="btn btn-warning pull-right" href="<?php
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

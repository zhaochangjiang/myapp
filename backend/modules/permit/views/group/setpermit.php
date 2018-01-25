<?php

use backend\common\Pager;
use backend\common\BackenBaseData;
use framework\bin\utils\AUtils;

//权限验证准备

$permitEdit = $this->havePermit(array(
    $this->controllerString,
    'ajaxsetpermit',
    $this->moduleString));
$permitAjaxsetbatchpermit = $this->havePermit(array(
    $this->controllerString,
    'ajaxsetbatchpermit',
    $this->moduleString));


$permitHaveopertate = $permitEdit || $permitAjaxsetbatchpermit;
?>
<div class="box" id="contentHtml" baseUrl="<?php echo AUtils::currentUrl(); ?>">
    <div class="box-body no-padding">
        <?php if ($this->data['data']['list']): ?>
            <table class="table ">
                <tbody>
                <tr>
                    <?php
                    echo '<th  class="text-center">组名</th>';
                    echo '<th  class="text-center">是否开启？</th>';
                    if ($permitHaveopertate) {
                        echo '<th style="width:180px" class="text-center">操作</th>';
                    }
                    ?>
                </tr>
                <?php
                foreach ($this->data['data']['list'] as $value) {
                    echo '<tr>';
                    echo '<td  class="text-center" ><a href="' . $this->createUrl(array(
                            $this->controllerString,
                            $this->action,
                            $this->moduleString), array(
                            'id' => $this->params['id'],
                            'uppid' => $value['id'],
                            'goto' => $this->params['goto'])) . '">' . $this->outputHtml($value['name'], '-') . '</a></td>';
                    echo '<td  class="text-center" title="点击开启当前及当前的子权限">' . ($value['openFlag'] ? '<a href="javascript:;" class="fa fa-unlock-alt">&nbsp;</a>' : '<a href="javascript:;" class="fa fa-lock">&nbsp;</a>') . '</td>';
                    if ($permitHaveopertate) {
                        echo '<td style="width:100px"  class="text-center">';
                        $operateStringArray = array();
                        if ($permitEdit) {
                            if ($value['openFlag']) {

                                $operateStringArray[] = '<a href="javascript:;" onclick="doAjaxData(\'' . $this->createUrl(array(
                                        $this->controllerString,
                                        'ajaxsetpermit',
                                        $this->moduleString), array(
                                        'gid' => $this->params['id'],
                                        'type' => 'close',
                                        'pid' => $value['id'],
                                    )) . '\');return;">关闭</a>';
                            } else {
                                $operateStringArray[] = '<a href="javascript:;" onclick="doAjaxData(\'' . $this->createUrl(array(
                                        $this->controllerString,
                                        'ajaxsetpermit',
                                        $this->moduleString), array(
                                        'gid' => $this->params['id'],
                                        'type' => 'open',
                                        'pid' => $value['id']
                                    )) . '\');return;">开启</a>';
                            }
                        }
                        if ($permitAjaxsetbatchpermit) {
                            if ($value['openFlag']) {

                                $operateStringArray[] = '<a href="javascript:;" onclick="doAjaxData(\'' . $this->createUrl(array(
                                        $this->controllerString,
                                        'ajaxsetbatchpermit',
                                        $this->moduleString), array(
                                        'gid' => $this->params['id'],
                                        'type' => 'close',
                                        'pid' => $value['id']
                                    )) . '\');return;" title="批量关闭子权限">批量关闭</a>';
                            } else {
                                $operateStringArray[] = '<a href="javascript:;" onclick="doAjaxData(\'' . $this->createUrl(array(
                                        $this->controllerString,
                                        'ajaxsetbatchpermit',
                                        $this->moduleString), array(
                                        'gid' => $this->params['id'],
                                        'type' => 'open',
                                        'pid' => $value['id']
                                    )) . '\');return;" title="批量开启子权限">批量开启</a>';
                            }
                        }
                        echo implode('&nbsp;|&nbsp;', $operateStringArray);
                        echo '</td>';
                    }
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>
            <?php
        else:
            ?>

            <div class="error-page">
                <div class="error-content">
                    <h3><i class="fa fa-warning text-yellow"></i> 没有子权限了</h3>
                    <p> &nbsp;</p>
                </div><!-- /.error-content -->
            </div>
        <?php endif;
        ?>
    </div>

</div>
<script>
    function doAjaxData(u) {
        $.post(u, null, function (r) {
            if (r === 'ok') {
                location.href = $("#contentHtml").attr("baseUrl");
            } else {
                // alert(r);
                $('#compose-modal .modal-body').html(r);
                $('#compose-modal').modal('show');
            }
        });
    }
</script>

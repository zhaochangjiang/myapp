<?php

use backend\common\Pager;
use backend\common\BackenBaseData;

//权限验证准备
$permitDelete = $this->havePermit(array(
    $this->controllerString,
    'deletegroupuser',
    $this->moduleString));

$permitHaveopertate = $permitEdit;
?>
<div class="box">
    <div class="box-body no-padding">
        <table class="table ">
            <tbody>
            <tr>
                <?php
                echo '<th  class="text-center">用户ID</th>';
                echo '<th  class="text-center">姓名</th>';
                if ($permitDelete) {
                    echo '<th class="text-right">-</th>';
                }
                ?>
            </tr>
            <?php
            foreach ($this->data['data']['list'] as $value) {
                echo '<tr>';
                echo '<td  class="text-center" >' . $this->outputHtml($value['admin_userid'], '-') . '</td>';
                echo '<td  class="text-center" >' . $this->outputHtml($value['name'], '-') . '</td>';
                if ($permitDelete) {
                    echo '<td   class="text-right"><a onclick="doAjaxDelete(\'' . $this->createUrl(
                            array(
                                $this->controllerString,
                                'deletegroupuser',
                                $this->moduleString)
                            , array(
                            'admin_userid' => $value['admin_userid'],
                            'group_id' => $value['group_id']
                        )) . '\',\'' . currentUrl() . '\');return;" href="javascript:;">删除</a></td>';
                }
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="box-footer clearfix">
        <a href="<?php echo base64_decode($this->params['goto']); ?>" class="btn btn-warning pull-right" type="button">返回</a>
        <?php echo $this->widget($this->data['pageObject']); ?>
    </div>
</div>

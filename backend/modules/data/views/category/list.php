<?php

  use backend\modules\data\blocks\category\Search;

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



<?php $this->loadBlock('Search'); ?>
<div class="box">

    <div class="box-body no-padding">
        <table class="table ">
            <tbody>
                <tr>
                    <?php
                      if ($permitDelete)
                      {
                          echo '<th>-</th>';
                      }
                      echo '<th  class="text-center">类型名</th>';
                      echo '<th  class="text-center">sku</th>';
                      if ($permitHaveopertate)
                      {
                          echo '<th style="width:100px" class="text-center">操作</th>';
                      }
                    ?>
                </tr>
                <?php
                  foreach ($this->data['data']['list'] as $value)
                  {
                      echo '<tr>';
                      if ($permitDelete)
                      {
                          echo '<td><a onclick="doAjaxDelete(\'' . $this->createUrl(
                                  array(
                              $this->controllerString,
                              'delete',
                              $this->moduleString)
                                  , array(
                              'category_id' => $value['category_id']
                          )) . '\',\'' . currentUrl() . '\');return;" href="javascript:;">删除</a></td>';
                      }
                      echo '<td  class="text-center" >' . $this->outputHtml($value['category_label'], '-') . '</td>';
                      echo '<td  class="text-center">' . $this->outputHtml($this->zhcut($value['sku'], 12, '...'), '-') . '</td>';
                      if ($permitHaveopertate)
                      {
                          echo '<td style="width:100px"  class="text-center">';
                          echo '<a href="' . $this->createUrl(array(
                              $this->controllerString,
                              'edit',
                              $this->moduleString), array(
                              'category_id' => $value['category_id'],
                              'doType' => 'update',
                              'goto' => $this->base64encodeCurrentUrl())) . '">编辑</a>';
                          echo '</td>';
                      }
                      echo '</tr>';
                  }
                ?>
            </tbody></table>
    </div>
    <div class="box-footer clearfix">
        <?php if ($permitEdit): ?>
              <a class="btn btn-warning pull-left" href="<?php
                 echo $this->createUrl(array(
                     $this->controllerString,
                     'edit',
                     $this->moduleString), array(
                     'doType' => 'add',
                     'goto' => $this->base64encodeCurrentUrl()
                 ));
                 ?>">添加</a>
             <?php endif; ?>
           <?php echo $this->widget($this->data['pageObject']); ?>
    </div>
</div>

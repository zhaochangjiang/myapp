<?php

  use backend\common\Pager;
  use backend\common\BackenBaseData;

//权限验证准备
  $permitDelete = $this->havePermit(array(
      $this->controllerString,
      'delete',
      $this->moduleString));
  $permitEdit = $this->havePermit(array(
      $this->controllerString,
      'edit',
      $this->moduleString));
  $permitGroupUser = $this->havePermit(array(
      $this->controllerString,
      'groupuser',
      $this->moduleString));
  $permitsSetPermit = $this->havePermit(array(
      $this->controllerString,
      'setpermit',
      $this->moduleString));

  $permitHaveopertate = $permitEdit | $permitsSetPermit;
?>

<div class="box box-solid">
    <ul class="list-group">
        <?php
          foreach ($this->data['listPermit']['permitData'] as $key => $value)
          {
              $temp = array_shift($this->data['listPermit']['permitIdArray']);
              if (empty($value))
              {
                  continue;
              }
              echo '<li  class="list-group-item">';
              echo ' <ul  class="list-inline">';
              foreach ($value as $v)
              {
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
                      if ($permitDelete)
                      {
                          echo '<th>-</th>';
                      }
                      echo '<th  class="text-center">组名</th>';
                      echo '<th  class="text-center">超级管理员?</th>';
                      echo '<th  class="text-center">上级</th>';
                      echo '<th  class="text-center" title="后台用户" rel="popover">用户（数量）</th>';
                      if ($permitHaveopertate)
                      {
                          echo '<th style="width:180px" class="text-center">操作</th>';
                      }
                    ?>
                </tr>
                <?php
                  foreach ($this->data['data']['list'] as $value)
                  {
                      echo '<tr>';
                      if ($permitDelete && $value['super_admin'] !== 'yes')
                      {
                          echo '<td><a onclick="doAjaxDelete(\'' . $this->createUrl(
                                  array(
                              $this->controllerString,
                              'delete',
                              $this->moduleString)
                                  , array(
                              'id' => $value['id']
                          )) . '\',\'' . currentUrl() . '\');return;" href="javascript:;">删除</a></td>';
                      }
                      elseif ($permitDelete && $value['super_admin'] === 'yes')
                      {
                          echo '<td>-</td>';
                      }
                      echo '<td  class="text-center" >' . $this->outputHtml($value['name'], '-') . '</td>';

                      $moduleControllerAction = array();
                      $arrayContent = array(
                          'module',
                          'controller',
                          'action'
                      );
                      foreach ($arrayContent as $val)
                      {
                          if (!empty($value[$val]))
                          {
                              $moduleControllerAction[] = "<span title=\"{$val}\">{$value[$val]}</span>";
                          }
                      }
                      echo '<td  class="text-center">' . BackenBaseData::outputIsSuperAdmin($value['super_admin'], '-') . '</td>';
                      echo '<td  class="text-center">' . $this->outputHtml($value['upPermitName'], '-') . '</td>';
                      echo '<td  class="text-center">' . ( empty($value['adminUserCount']) ? $this->outputHtml($value['adminUserCount'], '-') :
                              ($permitGroupUser ?
                                      "<a href=\"" . $this->createUrl(array(
                                          $this->controllerString,
                                          'groupuser',
                                          $this->moduleString), array(
                                          'gid' => $value['id'],
                                          'goto' => base64_encode(currentUrl())
                                      )) . "\">{$value['adminUserCount']}</a>" :
                                      $value['adminUserCount'])) . '</td>';
                      if ($permitHaveopertate)
                      {
                          echo '<td style="width:100px"  class="text-right">';

                          $operateStringArray = array();
                          if ($permitEdit)
                          {
                              $operateStringArray[] = '<a href="' . $this->createUrl(array(
                                          $this->controllerString,
                                          'edit',
                                          $this->moduleString), array(
                                          'id' => $value['id'],
                                          'type' => 'update',
                                          'goto' => $this->base64encodeCurrentUrl())) . '">编辑</a>';
                          }
                          if ($permitsSetPermit && $value['super_admin'] !== 'yes')
                          {
                              $operateStringArray[] = '<a href="' . $this->createUrl(array(
                                          $this->controllerString,
                                          'setpermit',
                                          $this->moduleString), array(
                                          'id' => $value['id'],
                                          'goto' => $this->base64encodeCurrentUrl())) . '">设置权限</a>';
                          }
                          echo implode('&nbsp;|&nbsp;', $operateStringArray);
                          echo '</td>';
                      }
                      echo '</tr>';
                  }
                ?>
            </tbody></table>
    </div>
    <div class="box-footer clearfix">
        <?php
          if ($permitEdit)
          {
              ?>
              <a class="btn btn-warning pull-left" href="<?php
                 echo $this->createUrl(array(
                     $this->controllerString,
                     'edit',
                     $this->moduleString), array(
                     'type' => 'add',
                     'goto' => base64_encode(currentUrl())));
                 ?>">添加</a>
             <?php } ?>
           <?php echo $this->widget($this->data['pageObject']); ?>
    </div>
</div>
<script>$('[rel="popover"]').popover(options);</script>

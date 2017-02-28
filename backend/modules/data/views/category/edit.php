<?php

  use communal\common\UtilsFormFormat;
?>     
<div class="box box-primary">
    <div class="box-body">
        <?php
          $urlParam = array(
              $this->controllerString,
              'iframe' . ucfirst($this->action),
              $this->moduleString);
          echo UtilsFormFormat::open($this->createUrl($urlParam, array(
                      'category_id' => $this->params['category_id'],
                      'dotype' => $this->params['doType'],
                      'goto' => $this->params['goto'])), array(
              'class' => 'form-horizontal'));
        ?>
        <div class="box-body">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '名称'; ?></label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="text" name="category_label" placeholder="请填类型名称" value="<?php echo $this->data['data']['category_label']; ?>" class="form-control">
                    </div>
                </div>
            </div>
            <?php if ('add' !== $this->params['doType']): ?>
                  <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label"><?php echo 'SKU'; ?></label>
                      <div class="col-lg-3">
                          <div class="input-group">
                              <input type="text" name="sku" disabled="disabled" placeholder="sku" value="<?php echo $this->data['data']['sku']; ?>" class="form-control">
                          </div>
                      </div>
                  </div>
              <?php endif; ?>
            <div class="form-group">
                <label  class="col-sm-2 control-label">上级组</label>
                <?php echo $this->loadBlock('Step'); ?>
            </div>
        </div>
        <div class="box-footer">
            <div class=" form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">&nbsp;</label>
                <div class="col-lg-2">
                    <div class="input-group">
                        <?php
                          switch ($this->params['doType'])
                          {
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
                <div class="col-lg-8"> <a  href="<?php echo base64_decode($this->data['goto']); ?>" class="btn pull-right"  type="button">返回</a></div>
            </div>
        </div>
       <?php UtilsFormFormat::close();?>
    </div>
</div>

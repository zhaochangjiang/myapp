<?php

use communal\common\UtilsFormFormat;
use backend\modules\data\blocks\UploadImageFile;

?>
>
<div class="box box-primary">

    <div class="box-body">

        <?php
        $urlParam = array(
            $this->controllerString,
            'iframe' . ucfirst($this->action),
            $this->moduleString);
        echo UtilsFormFormat::open($this->createUrl($urlParam, array(
            'id' => $this->data['data']['id'],
            'dotype' => $this->data['doType'],
            'goto' => $this->data['goto'])), array(
            'class' => 'form-horizontal',
            "enctype" => "multipart/form-data"));
        ?>
        <div class="box-body">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '食材名'; ?></label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="text" name="ingredient_name" placeholder="请填写食材名"
                               value="<?php echo $this->data['data']['ingredient_name']; ?>" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '缩略图'; ?></label>
                <div class="col-lg-10">
                    <?php
                    $uploadFileParams = array(
                        'paramName' => 'ingredient_thumbnail',
                        'minFileCount' => 1,
                        'uploadUrl' => $this->createUrl(array(
                            $this->controllerString,
                            'upload',
                            $this->moduleString), array(
                            'type' => 'thumbnail')),
                        'maxFileCount' => 1
                    );
                    $this->loadBlock(new UploadImageFile(), $uploadFileParams);
                    ?>

                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '其他图片'; ?></label>
                <div class="col-lg-10">
                    <?php
                    $uploadFileParams = array(
                        'paramName' => 'ingredient_images',
                        'minFileCount' => 1,
                        'uploadUrl' => $this->createUrl(array(
                            $this->controllerString,
                            'upload',
                            $this->moduleString), array(
                            'type' => 'images')),
                        'maxFileCount' => 8
                    );
                    $this->helper(new UploadImageFile(), $uploadFileParams);
                    ?>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '食材别名'; ?></label>
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="text" name="ingredient_foodalias" placeholder="请填写食材别名"
                               value="<?php echo $this->data['data']['ingredient_foodalias']; ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '食材特点'; ?></label>
                <div class="col-lg-8">
                    <div class="">
                        <input type="text" name="ingredient_keyword" placeholder="食材特点"
                               value="<?php echo $this->data['data']['ingredient_keyword']; ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"><?php echo '简单描述'; ?></label>
                <div class="col-lg-8">
                    <div class="">
                        <input type="text" name="ingredient_desc" placeholder="简单描述"
                               value="<?php echo $this->data['data']['ingredient_desc']; ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="inputEmail3"><?php echo '详情'; ?></label>
                <div class="col-lg-8">
                    <textarea name="ingredient_description" placeholder="请输入 ..." rows="5"
                              class="form-control"><?php echo $this->data['data']['ingredient_description']; ?></textarea>
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
        <?php UtilsFormFormat::close(); ?>
    </div>
</div>

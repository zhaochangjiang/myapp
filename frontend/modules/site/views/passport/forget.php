<?php

use communal\common\UtilsFormFormat;

?>
<div class="panel panel-sign">
    <div class="panel-title-sign mt-xl text-right">
        <h2 class="title text-uppercase text-bold m-none"><i class="fa fa-user mr-xs"></i> 密码重置</h2>
    </div>
    <div class="panel-body">
        <div class="alert alert-info" style="display: none">
            <p class="m-none text-semibold h6">&nbsp;</p>
        </div>
        <?php
        echo UtilsFormFormat::open($this->createUrl(array(
                $this->controllerString,
                'iFrame' . ucfirst($this->action)) . ucfirst($this->action)));
        ?>
        <div class="form-group mb-lg">
            <label>邮箱/手机</label>
            <div class="input-group input-group-icon">
                <input name="username" placeholder="邮箱/手机" value="" type="text" class="form-control input-lg"/>
                <span class="input-group-addon">
                    <span class="icon icon-lg">
                        <i class="fa fa-user"></i>
                    </span>
                </span>
            </div>
        </div>

        <div class="row form-group mb-lg">
            <div class="col-xs-5">
                <div class="input-group input-group-icon">
                    <input name="mobile" type="mobile" class="form-control input-lg"/>
                    <span class="input-group-addon">
                        <span class="icon icon-lg">
                            <i class="fa fa-lock"></i>
                        </span>
                    </span>
                </div>
            </div>
            <div class="col-xs-7 text-left">
                <img border="0" onclick="$(this).attr('src', '<?php
                echo $this->createUrl(array(
                    $this->controllerString,
                    'authcode'))
                ?>' + '?t=' + new Date().getTime())" src="<?php
                echo $this->createUrl(array(
                    $this->controllerString,
                    'authcode'), array(
                    't' => microtime(true)));
                ?>"/>;
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8">还没有账号? <a href="<?php
                echo $this->createUrl(array(
                    $this->controllerString,
                    'login'));
                ?>">登录</a></div>
            <div class="col-xs-4 text-right">
                <button type="submit" class="btn btn-primary">登录</button>
            </div>
        </div>
        <?php echo UtilsFormFormat::close(); ?>
    </div>
</div>



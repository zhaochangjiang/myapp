<?php

use communal\common\UtilsFormFormat;

?>
<div class="panel panel-sign">
    <div class="panel-title-sign mt-xl text-right">
        <h2 class="title text-uppercase text-bold m-none"><i class="fa fa-user mr-xs"></i> 注册</h2>
    </div>
    <div class="panel-body">
        <?php
        echo UtilsFormFormat::open($this->createUrl(
            $this->controllerString . '.iframe' . ucfirst($this->action), array(
            'goto' => $this->data['goto'])));
        ?>
        <div class="alert alert-danger" style="display: none">
            <p class="m-none text-semibold h6"></p>
        </div>
        <div class="form-group mb-lg">
            <div class="clearfix">
                <label>用户名</label>
                <a href="<?php
                echo $this->createURl("{$this->controllerString}.login"
                    , array(
                        'goto' => $this->data['goto']));
                ?>" class="pull-right">已有账号，去登录?</a>
            </div>
            <div class="input-group input-group-icon">
                <input name="username" type="text" class="form-control input-lg"/>
                <span class="input-group-addon">
                    <span class="icon icon-lg">
                        <i class="fa fa-user"></i>
                    </span>
                </span>
            </div>
        </div>
        <div class="form-group mb-lg">
            <div class="clearfix">
                <label class="pull-left">密码</label>
            </div>
            <div class="input-group input-group-icon">
                <input name="pwd" type="password" class="form-control input-lg"/>
                <span class="input-group-addon">
                    <span class="icon icon-lg">
                        <i class="fa fa-lock"></i>
                    </span>
                </span>
            </div>
        </div>
        <div class="form-group mb-lg">
            <div class="clearfix">
                <label class="pull-left">确认密码</label>
            </div>
            <div class="input-group input-group-icon">
                <input name="repwd" type="password" class="form-control input-lg"/>
                <span class="input-group-addon">
                    <span class="icon icon-lg">
                        <i class="fa fa-lock"></i>
                    </span>
                </span>
            </div>
        </div>
        <div class="form-group mb-lg">
            <div class="clearfix">
                <label class="pull-left">邮箱</label>
            </div>
            <div class="input-group input-group-icon">
                <input name="email" type="email" class="form-control input-lg"/>
                <span class="input-group-addon">
                    <span class="icon icon-lg">
                        <i class="fa fa-envelope"></i>
                    </span>
                </span>
            </div>
        </div>
        <div class="form-group mb-lg">
            <div class="clearfix">
                <label class="pull-left">手机</label>
            </div>
            <div class="input-group input-group-icon">
                <input name="mobile" type="mobile" class="form-control input-lg"/>
                <span class="input-group-addon">
                    <span class="icon icon-lg">
                        <i class="fa  fa-mobile"></i>
                    </span>
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5">
                <div class="input-group input-group-icon">
                    <input name="mobile" type="mobile" class="form-control input-lg"/>
                    <span class="input-group-addon">
                        <span class="icon icon-lg">
                            <i class="fa fa-yahoo"></i>
                        </span>
                    </span>
                </div>
            </div>
            <div class="col-xs-7 text-left">
                <img border="0" onclick="$(this).attr('src', '<?php
                echo $this->createUrl(
                    $this->controllerString . '.authcode')
                ?>' + '/?t=' + new Date().getTime())" src="<?php
                echo $this->createUrl("{$this->controllerString}.authcode", array(
                    't' => microtime(true)));
                ?>"/>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8">
                <div class="checkbox-custom checkbox-default"> &nbsp;
                </div>
            </div>
            <div class="col-sm-4 text-right">
                <button type="submit" class="btn btn-primary hidden-xs">注册</button>
                <button type="submit" class="btn btn-primary btn-block btn-lg visible-xs mt-lg">注册</button>
            </div>
        </div>
        <span class="mt-lg mb-lg line-thru text-center text-uppercase">
            <span>or</span>
        </span>
        <p class="text-center">回到&nbsp;
            <a href="<?php
            echo $this->createUrl('');
            ?>">首页</a></p>
        <?php UtilsFormFormat::close();
        ?>
    </div>
</div>

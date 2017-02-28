<?php

use communal\common\UtilsFormFormat;
?>      
<script type="text/javascript">
    function hideorshowSubmitButton(o1) {

        if (typeof (o1) == 'undefined') {
            $(".alert-danger").hide();
            $("#submitiframes").addClass('disabled');
        } else {
            $("#submitiframes").removeClass('disabled');
        }
    }
    function showerror(m) {
        $(".alert-danger").show().find('p').html(m);
        hideorshowSubmitButton(true);
    }

</script>
<div class="panel panel-sign">
    <div class="panel-title-sign mt-xl text-right">
        <h2 class="title text-uppercase text-bold m-none"><i class="fa fa-user mr-xs"></i> 登录</h2>
    </div>
    <div class="panel-body">
        <?php
        $urlParamArray = array();
        if (!empty($this->data['goto'])) {
            $urlParamArray = array(
                'goto' => $this->data['goto']);
        }

        UtilsFormFormat::open($this->createUrl(array($this->controllerString, 'iframe' . ucfirst($this->action), $this->moduleString), $urlParamArray));
        ?>
        <div class="form-group mb-lg">
            <div class="alert alert-danger" style="display: none;" >
                <p class="m-none text-semibold h6"></p>
            </div>
            <label>用户名</label>
            <div class="input-group input-group-icon">
                <input name="username" placeholder="邮箱/手机" value="" type="text" class="form-control input-lg" />
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
                <a href="<?php
                echo $this->createUrl(array($this->controllerString, 'forget'));
                ?>" class="pull-right">忘记密码?</a>
            </div>
            <div class="input-group input-group-icon">
                <input name="password" placeholder="密码" type="password" class="form-control input-lg" />
                <span class="input-group-addon">
                    <span class="icon icon-lg">
                        <i class="fa fa-lock"></i>
                    </span>
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8">
                <div class="checkbox-custom checkbox-default">
                    <input id="RememberMe" name="rememberme" type="checkbox"/>
                    <label for="RememberMe">记住账号？</label>
                </div>
            </div>
            <div class="col-sm-4 text-right">
                <button type="submit" class="btn btn-primary" id="submitiframes" onclick="hideorshowSubmitButton();
                        return;" >登录</button>
            </div>
        </div>
        <span class="mt-lg mb-lg line-thru text-center text-uppercase">
            <span>or</span>
        </span>
        <!--
        <div class="mb-xs text-center">
            <a class="btn btn-facebook mb-md ml-xs mr-xs">Connect with <i class="fa fa-facebook"></i></a>
            <a class="btn btn-twitter mb-md ml-xs mr-xs">Connect with <i class="fa fa-twitter"></i></a>
        </div>
        -->
        <p class="text-center">还没有账号? <a href="<?php
                                         echo $this->createUrl(array($this->controllerString, 'register',$this->moduleString)
                                                 , array(
                                             'goto' => $this->data['goto']));
                                         ?>">注册</a> </p>
            <?php echo UtilsFormFormat::close(); ?>
    </div>
</div>


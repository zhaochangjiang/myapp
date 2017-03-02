<div style=" padding:100px;">
    <a href="<?php
    echo $this->createUrl('passport.login');
    ?>">登录</a>
    <a href="<?php
    echo $this->createUrl('passport.register');
    ?>">注册</a>
    <a href="<?php
    echo $this->createUrl('Site.index', array(), framework\App::base()->params['domain']['dashboard']);
    ?>">后台</a>

</div>
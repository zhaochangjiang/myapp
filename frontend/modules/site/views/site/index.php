<?php
use framework\App;

?>
<div style=" padding:100px;">
    <a href="<?php
    echo $this->createUrl('passport.login');
    ?>">登录</a>
    <a href="<?php
    echo $this->createUrl('passport.register');
    ?>">注册</a>
    <a href="<?php
    echo $this->createUrl('Site.index', array(), APP::$app->params['domain']['dashboard']);
    ?>">后台</a>

</div>
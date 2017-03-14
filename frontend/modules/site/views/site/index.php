<?php
use framework\App;

?>
<div style=" padding:100px;">
    <a href="<?php echo $this->createUrl(array('passport', 'login')); ?>">登录</a>
    <a href="<?php echo $this->createUrl(array('passport', 'register')); ?>">注册</a>
   
    <a href="<?php
    echo $this->createUrl(array('Site', 'index'), array(),
        APP::$app->parameters->domain['dashboard']);
    ?>">后台</a>

</div>
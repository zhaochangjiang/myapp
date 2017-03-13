<?php

use framework\App;

$config = array(
    'controllerNameSpace' => 'frontend\modules',
);
$config1 = require_once App::setNameSpacePathMap('communal') . 'config/' . DEFINE_ENVIORMENT . '/config.php';
return array_merge($config1, $config);


  
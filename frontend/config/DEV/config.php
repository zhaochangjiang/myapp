<?php
/**
 * Created by PhpStorm.
 * @Author: karl.zhao<zhaocj2009@hotmail.com>
 * @Date: 2017/3/13
 * @Time: 23:29
 */


use framework\App;

$config = array(
    'controllerNameSpace' => 'frontend\modules',
);
$config1 = require_once App::setNameSpacePathMap('communal') . 'config/' . DEFINE_ENVIORMENT . '/config.php';
return array_merge($config1, $config);



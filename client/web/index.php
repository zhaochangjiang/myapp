<?php

use framework\App;

header("Content-type:text/html;charset=utf-8");

function xmp($obj = null)
{
    header("Content-type:text/html;charset=utf-8");
    echo '<br>-----------------test start-------------<xmp>';
    print_r($obj);
    echo '</xmp>--------------test over-----------------<br>';
}

function stop($obj = null)
{
    xmp($obj);
    exit;
}

//是否显示底部调试输出
define('SHOW_DEBUG_TRACE', false);

//调试SQL显示数目
define('DEBUG_SQL_NUM', 100);

//应用所在目录,此参数放在此处为宜
defined('DIR_APP_LOCATION') or define('DIR_APP_LOCATION', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

//是否合并css js
define('IS_MERAGE', TRUE);

defined('IS_CLIENT') or define('IS_CLIENT', TRUE);

require_once(dirname(DIR_APP_LOCATION) . "/vendor/framework/App.php");

defined('DEFINE_ENVIORMENT') or define('DEFINE_ENVIORMENT', App::getEnvironment());
//启动应用
// * 系统默认访问类，Site
// * 默认访问方法          indexAction

try {
    $config = dirname(dirname(__FILE__)) . '/config/' . DEFINE_ENVIORMENT . '/config.php';
    $app = App::createApplication($config)->run();
} catch (Exception $ex) {
    switch (DEFINE_ENVIORMENT) {//如果是开发环境，则抛除异常
        case 'DEV':
            throw  $ex;
            break;
    }
}


  
<?php

use framework\App;

header("Content-type:text/html;charset=utf-8");

  function xmp($obj = null)
  {
      header("Content-type:text/html;charset=utf-8");
      echo '<br>-------------------------------<xmp>';
      print_r($obj);
      echo '</xmp>-------------------------------<br>';
  }

  function stop($obj = null)
  {
      xmp($obj);
      exit;
  }

  function getEnvironment()
  {
      $enviorment = 'RUN';
      if ($enviorment)
      {
          $enviormentIni = get_cfg_var('enviorment');
          if ($enviormentIni)
          {
              $enviorment = $enviormentIni;
          }
      }
      return $enviorment;
  }

  //define('DEFINE_ENVIORMENT', getEnvironment());
  define('DEFINE_ENVIORMENT', 'DEV');
//$statTime = microtime(true);
////判断是否是加载的合法的程序
//defined('IS_SYSTEM') or define('IS_SYSTEM',
//                               true);
//是否显示底部调试输出
  define('SHOW_DEBUG_TRACE', false);

//调试SQL显示数目
  define('DEBUG_SQL_NUM', 100);

//是否合并css js
  define('IS_MERAGE', TRUE);

//启动应用
// * 系统默认访问类，Site
// * 默认访问方法          indexAction

  try
  {
      require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "vendor/framework/App.php");
      $config = dirname(dirname(__FILE__)) . '/config/' . DEFINE_ENVIORMENT . '/config.php';
      App::createApplication($config)->run();
  }
  catch (Exception $ex)
  {
      throw $ex;
  }

  
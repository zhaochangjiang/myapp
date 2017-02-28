<?php

  use framework\App;

header("Content-type:text/html;charset=utf-8");

  function xmp($obj = null)
  {
      header("Content-type:text/html;charset=utf-8");
      echo '<xmp>';
      print_r($obj);
      echo '</xmp>';
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

//$statTime = microtime(true);
////判断是否是加载的合法的程序
//defined('IS_SYSTEM') or define('IS_SYSTEM',
//                               true);
//是否显示底部调试输出
  define('SHOW_DEBUG_TRACE', false);

//调试SQL显示数目
  define('DEBUG_SQL_NUM', 100);

//应用所在目录,此参数放在此处为宜
  defined('DIR_APP_LOCATION') or define('DIR_APP_LOCATION', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);


//是否合并css js
  define('IS_MERAGE', TRUE);



//启动应用
// * 系统默认访问类，Site
// * 默认访问方法          indexAction

  try
  {
      require_once(dirname(DIR_APP_LOCATION) . "/vendor/framework/App.php");
      $config = dirname(dirname(__FILE__)) . '/config/' . getEnvironment() . '/config.php';
      $app = App::createApplication($config)->run();
  }
  catch (Exception $ex)
  {
      throw $ex;
  }


  
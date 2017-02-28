<?php

  namespace framework\helpers;

  use framework\bin\AController;
  use ReflectionClass;
  use Exception;

  /**
   * 组件,继承自Acontroller
   *
   * @author heypigg
   */
  Class AHelper extends AController
  {

      protected $params           = array();
      protected static $base;
      protected $context; //应用上下文
      protected $controllerObject = null;

      public function __construct($module = null, $action = null)
      {
          $this->context = self::$base? : $this;
      }

      public function init(AController $base)
      {
          self::$base = $base;
      }

      public function render($view = null, $params = array())
      {

          $className = get_class($this);

          $rc = new ReflectionClass($className);
          if ($view == null)
          {
              $arrayClassName = explode('\\', $className);
              array_shift($arrayClassName);
              array_shift($arrayClassName);
              foreach ($arrayClassName as $key => $value)
              {
                  $arrayClassName[$key] = lcfirst($value);
              }
              $view = lcfirst(implode('/', $arrayClassName));
          }

          //    $widget = dirname($rc->getFileName());
          
          $path = $this->getControllerObject()->getAppTemplatePath() . DIRECTORY_SEPARATOR . 'block' . DIRECTORY_SEPARATOR . $view . '.php';
          if (!file_exists($path))
          {
              throw new Exception("The file {$path} is not exits; ");
          }
          // extract($params);
          include $path;
      }

      function getControllerObject()
      {
          return $this->controllerObject;
      }

      function setControllerObject($controllerObject)
      {
          $this->controllerObject = $controllerObject;
      }

      //获得天气
      public function getCond($param, $t = 'day')
      {

//        if($param['txt_d']==$param['txt_n']){
          if ($t == 'night')
          {
              return $param['txt_n'];
          }
          return $param['txt_d'];
//        }else{
//            
//            return $param['txt_d'].'-'.$param['txt_n'];
//        }
      }

      //获得温度
      public function getTmp($param)
      {

          return $param['max'] . '/' . $param['min'] . '°C';
      }

      //获得风力
      public function getWind($param)
      {

          if (strpos($param['sc'], '-'))
          {

              $ji = '(级)';
          }
          else
          {
              $ji = '';
          }

          return $param['dir'] . ":<i>" . $param['sc'] . $ji . '</i>';
      }

      public function foramtWeatherData($params)
      {
          $rs = json_decode($params, true);
          return $rs;
      }

  }
  
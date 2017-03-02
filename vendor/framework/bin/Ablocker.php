<?php

namespace framework\bin;

use framework\bin\AController;
use ReflectionClass;
use Exception;

/**
 * 组件,继承自Acontroller
 *
 * @author heypigg
 */
abstract class Ablocker extends AController
{

    protected $params = array();
    protected static $base;
    protected $context; //应用上下文
    protected $controllerObject = null;
    private $renderTemplate;

    public function __construct($module = null, $action = null)
    {
        $this->context = self::$base ?: $this;
    }

    function getRenderTemplate()
    {
        return $this->renderTemplate;
    }

    function setRenderTemplate($renderTemplate)
    {

        $this->renderTemplate = $renderTemplate;
    }

    public function init(AController $base)
    {
        self::$base = $base;
    }

    abstract public function run();

    private function _getBlockTemplate($view)
    {
        if (empty($this->renderTemplate)) {
            $path = $this->getControllerObject()->getTemplateDir() . $view . '.php';
        } else {
            $path = \framework\App::getPathOfAlias($this->renderTemplate) . DIRECTORY_SEPARATOR . $view . '.php';
        }
        return $path;
    }

    public function render($view = null, $params = array())
    {

        $className = get_class($this);

        //     $rc = new ReflectionClass($className);
        if ($view == null) {
            $arrayClassName = explode('\\', $className);
            $arrayPathArray = array();
            $flag = false;
            foreach ((array)$arrayClassName as $value) {
                if ($flag === true || $value === 'blocks') {

                    $arrayPathArray[] = lcfirst($value);
                    $flag = true;
                }
            }

            $view = implode('/', $arrayPathArray);
        }

        $path = $this->_getBlockTemplate($view);

        if (!file_exists($path)) {
            throw new Exception("The file {$path} is not exits; on  Line " . __LINE__ . '  in  file ' . __FILE__);
        }
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
        if ($t == 'night') {
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

        if (strpos($param['sc'], '-')) {

            $ji = '(级)';
        } else {
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
  
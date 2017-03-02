<?php

namespace framework\bin;

use framework\bin\AHttpException;
use framework\bin\ARequest;
use framework\bin\AResponse;
use framework\bin\ABaseController;
use framework\bin\AErrorHandler;
use framework\helpers\AHelper;
use Exception;
use client\common\ClientResultData;
use client\common\ErrorCode;

// error_reporting(0);
ini_set('date.timezone', 'Asia/Shanghai');
/**
 * *****************domain over**************************
 */
// --------------------系统配置Start-----------------------------------/
// 当前系统时间
define('TIMESTAMP', time());
defined('MICROTIME') or define('MICROTIME', microtime(true));
define('D_S', DIRECTORY_SEPARATOR);

// 所用数据库类型？
defined('DATABASE_TYPE') or define('DATABASE_TYPE', 'Mysql');
// 当前是否是DEBUG状态
// defined('IS_DEBUG') or define('IS_DEBUG', false);
// 接口令牌,用户调用接口时判断是否合法.
defined('TOKEN') or define('TOKEN', 'f#jPk9$0');
/**
 * *******************服务目录配置 start*******************
 */
// 当前系统服务器目录
defined('DIR_SERVER') or define('DIR_SERVER', dirname(dirname(dirname(dirname(__FILE__)))) . D_S);
// --------------------系统配置Over-----------------------------------/
// 框架基础类所在文件位置
defined('DIR_FRAMEWORK') or define('DIR_FRAMEWORK', DIR_SERVER . 'vendor/framework' . D_S);

//是否为接口访问
defined('IS_CLIENT') or define('IS_CLIENT', false);
// 本地访问接口位置
//defined('DIR_IMPORT_LOCATE') or define('DIR_IMPORT_LOCATE',
//                                       DIR_SERVER . 'Cimport' . D_S);

/**
 * *******************服务目录配置 over*******************
 */
/**
 * *服务目录配置over*
 */

/**
 * 系统核心类
 *
 * @author zhaocj
 */
class ABaseApplication
{

    // 异常错误
    const EXCEPTION_HANDLER = "handleException";
    const ERROR_HANDLER = "handleError";
    const SHUTDOWN_HANDLER = "handleShutdown";

    private static $_instance = null;
    private static $_config = null;
    private static $_basePath = null;
    public static $enableIncludePath = true;
    // private static $_basePath;
    public static $classMap = array();
    public static $delimiterModuleAction = '_';
    public static $selfBasePathMap = array(
        '@framework' => DIR_FRAMEWORK,
    );
    //默认引入的文件
    private static $_aliases = array(
        'system' => DIR_FRAMEWORK); // alias
    private $clientResultData;

    /**
     * 实例对象
     * @return type
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }

    public function init()
    {
        // 初始化方法，留待具体实现类需要时去实现
    }

    // 系统views路径
    public static function getSystemViewPath()
    {
        return DIR_FRAMEWORK . D_S . 'views';
    }

    public function sessionDestory()
    {
        session_destroy();
    }

    /**
     *  递归删除目录
     *
     * @param type $path
     */
    public static function deleteDir($path)
    {
        if (!file_exists($path)) {
            return true;
        }
        $dh = opendir($path);
        while (($d = readdir($dh)) !== false) {
            if (in_array($d, array(
                '.',
                '..'))) {//如果为.或..
                continue;
            }
            $tmp = $path . D_S . $d;

            //如果为文件 //如果为目录
            (!is_dir($tmp)) ? unlink($tmp) : self::deleteDir($tmp);
        }
        closedir($dh);
        rmdir($path);

        //如果删除成功，则返回TRUE，如果删除失败则返回FALSE
        if (!file_exists($tmp)) {
            return true;
        }
        return false;
    }

    /**
     *  校验 合并两个数组
     * @param array $array1
     * @param array $array2
     */
    public static function arrayMerge(array $array1, array $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_int($key)) {//如果键为整数则 不处理只有为字符串时 再处理
                continue;
            }
            if (!isset($array2[$key])) {
                $array2[$key] = $value;
                unset($array1[$key]);
                continue;
            }
            if (self::haveChildArray($value) === false) {
                $array2[$key] = array_merge($value, $array2[$key]);
                unset($array1[$key]);
                continue;
            }

            $array2[$key] = self::arrayMerge($value, $array2[$key]);
        }
        return $array2;
    }

    /**
     * 判断一个数组是否有子数组
     */
    private static function haveChildArray(array $array)
    {
        $flag = false;
        foreach ($array as $value) {
            if (is_array($value)) {
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    private static function initSelfBasePathMap()
    {
        if (isset(self::$_config['selfBasePathMap']) && is_array(self::$_config['selfBasePathMap'])) {
            self::$selfBasePathMap = array_merge(self::$selfBasePathMap, self::$_config['selfBasePathMap']);
        }
    }

    /**
     * 创建应用
     *
     * @param $config -String
     * @return AApplication
     */
    public static function createApplication($configFile)
    {

        $instance = self:: getInstance();

        if (empty($configFile) || !file_exists($configFile)) {
            throw new Exception("The config file:\"{$configFile}\" can't null!");
        }

        include_once DIR_FRAMEWORK . 'bin' . D_S . 'AFunction.php'; // 加载函数库

        $instance::$_config = include_once $configFile;

        //初始化基础配置
        self::initSelfBasePathMap();

        // 注册__autoload()函数
        spl_autoload_register(array(
            __NAMESPACE__ . '\\' . 'ABaseApplication',
            'autoload'));

        // 错误控制
        $errorHandler = new AErrorHandler ();
        set_error_handler(array(
            $errorHandler,
            self::ERROR_HANDLER));
        set_exception_handler(array(
            $errorHandler,
            self::EXCEPTION_HANDLER));
        register_shutdown_function(array(
            $errorHandler,
            self::SHUTDOWN_HANDLER));

        return $instance;
    }

    /**
     *
     * @return string
     */
    public function getAccessToken()
    {
        return md5(TOKEN . session_id());
    }

    public function clientReturnDataOrg()
    {
        if (IS_CLIENT === false) {
            return;
        }
        $accessToken = $_POST['accessToken'];

        if (empty($accessToken)) {
            $this->clientResultData = new ClientResultData();
            $this->clientResultData->setResult(ErrorCode::$ERRORACCESSTOKEN);
            $this->clientResultData->setData($this->getAccessToken());
            //    $this->clientResultData->setSessionid();
            die(json_encode($this->clientResultData));
        }
        return $accessToken;
    }

    /**
     *
     * @return void
     */
    private function initSession()
    {

        $accessToken = $this->clientReturnDataOrg();

        // 如果有设置Session数据库缓存,否则开启Session
        if (self::$_config ['session'] == null) {
            if (IS_CLIENT !== FALSE) {
                session_id($accessToken);
            }
            session_start();
            return;
        }
        include_once DIR_FRAMEWORK . 'bin' . D_S . 'ASession.php';
    }

    /**
     * 框架启动运行方法
     *
     * @param String $class
     * @param String $method
     */
    public function run($controllerString = null, $action = null, $moduleString = null)
    {

        $_config = self::$_config;

        //开启session
        $this->initSession();


        // 获得当前请求动作是什么
        $controller = new ABaseController ();
        // $moduleString     = $controllerString = $action           = '';
        //如果不是 Afunction 中函数C控制的方法
        if ($moduleString === null && $controllerString === null && $action === null) {

            //如果不是PHP文件请求
            if ($_config ['urlManager'] ['rewriteMod'] && strrpos($_SERVER['REQUEST_URI'], '.php') === false) {

            } else {

                $moduleAction = $controller->getInput('r');
                list ($moduleString, $controllerString, $action) = self:: getRoute(empty($moduleAction) ? self::getDefaultModuleAction() : $moduleAction);
            }
        }


        if (empty($controllerString) || empty($action)) {

            throw new AHttpException(404, "你要查看的页面不存在!");
        }

        // 获得控制器
        $className = $this->getControllerNameSpace($moduleString) . '\\Controller' . ucfirst($controllerString);

        // 判断Method是否存在
        $moduleDeal = new $className($controllerString, $action, $moduleString);

        //var_dump($moduleDeal);
        $moduleDeal->init();


        self::loadAWidget($moduleDeal);
        $methodName = self:: getMethodName($action);


        // 如果方法不存在，新增动态action
        if (!method_exists($moduleDeal, $methodName)) {//&& !isset($moduleDeal->actionMaps [$methodName])
            throw new AHttpException(404, "The Method \"$methodName\" doesn't exist! in {$className}!");
        }
        $moduleDeal->$methodName();
    }

    /**
     * 设置默认访问模块
     * @return string
     */
    public static function getDefaultModule()
    {
        return 'site';
    }

    /**
     * 获得conrotller的命名空间
     * @return string
     */
    private function getControllerNameSpace($moduleString)
    {

        if (empty(self::$_config['controllerNameSpace'])) {
            throw new Exception('ther controller $_config[\'controllerNameSpace\'] is null,you need set it in config!');
        }
        if (empty($moduleString)) {
            $moduleString = self::getDefaultModule();
        }
        return self::$_config['controllerNameSpace'] . "\\{$moduleString}\\controllers";
    }

    /**
     *
     * @return string
     */
    private static function getDefaultModuleAction()
    {
        $defaultModuleAction = self::$_config ['defaultModuleAction'];
        if ($defaultModuleAction == '') {
            $defaultModuleAction = 'Site' . self::$delimiterModuleAction . 'index';
        }
        return $defaultModuleAction;
    }

    private static function loadAWidget($moduleDeal)
    {
        $widget = new AHelper();
        $widget->init($moduleDeal); //把上下文加入到组件里
    }

    /*
     * 返回应用程序根目录 @return string
     */

    public static function getBasePath()
    {
        return self::$_basePath;
    }

    /*
     * 设置应用程序根目录 @param string $path 应用程序根目录
     */

    public static function setBasePath($path)
    {
        if ((self::$_basePath = realpath($path)) === false || !is_dir(self::$_basePath)) {
            throw new Exception("{$path} 不是一个有效的目录");
        }
    }

//      private static function getDefaultModuleAction()
//      {
//          return self::$_config ['defaultModuleAction'];
//      }

    /**
     * 获取路由路径
     *
     * @return Ambigous <string, multitype:>
     */
    public static function getRoute($moduleAction)
    {
        $default = $defaultModuleAction = self::getDefaultModuleAction();
        if (!empty($moduleAction)) {
            $default = $moduleAction;
        }
        $temp = explode(self::$delimiterModuleAction, $default);

        $count = count($temp);
        $result = array();
        switch ($count) {
            case 0:
                throw new Exception("the program is error on creating Path! ");
            case 1:

                $moduleActionStringArray = explode(self::$delimiterModuleAction, $defaultModuleAction);
                list($result [1], $result [2]) = $moduleActionStringArray;
                $result[0] = array_pop($temp);
                break;

            default:
                $result [2] = array_pop($temp);
                $result [1] = array_pop($temp);
                $result [0] = implode('\\', $temp);
                break;
        }
        return $result;
    }

    /**
     * 获得SESSION内容，不传值表示返回所有的Session
     *
     * @param string $key
     * @return Ambigous <unknown, string>
     */
    public static function getSession($key = '')
    {
        $session = $_SESSION;
        return empty($key) ? $session : (isset($session[$key]) ? $session[$key] : '');
    }

    /**
     * 销毁Session
     */
    public static function sessionDestroy()
    {
        session_destroy();
    }

    /*
     * HTTP错误 @param $message 错误内容 @param $code 错误代码
     */

    public static function error($message, $code = 404)
    {

        require_once dirname(__FILE__) . D_S . 'AHttpException.php';
        throw new AHttpException($code, $message);
    }

    /**
     * 将一个对象转换成一个数组
     *
     * @param Object $object
     * @return multitype:
     */
    public static function objectToArray($object)
    {
        $result = array();
        $_array = is_object($object) ? get_object_vars($object) : $object;
        if (is_array($_array)) {
            foreach ($_array as $key => $value) {
                $result [$key] = (is_array($value) || is_object($value)) ? std_class_object_to_array($value) : $value;
            }
        }
        return $result;
    }

    /**
     * 设置Session的内容
     *
     * @param String $key
     * @param String $value
     */
    public static function setSession($key, $value)
    {
        $session = self::getSession();
        $session[$key] = $value;
        self::setSessionArray($session);
    }

    /**
     * 设置Session的内容
     *
     * @param String $key
     * @param String $value
     */
    public static function setSessionArray($sessionArray, $sessionAll = false)
    {
        if ($sessionAll === true) {
            $_SESSION = $sessionArray;
            return;
        }
        //  session_destroy();
        $_SESSION = array_merge($_SESSION, $sessionArray);
    }

    /**
     * 获取路由路径
     *
     * @return Ambigous <string, multitype:>
     */
    public static function getRouteNotNeedDefault($moduleAction)
    {
        if (!empty($moduleAction)) {
            self::$moduleAction = $moduleAction;
        }
        $temp = explode('/', self::$moduleAction);
        return $temp;
    }

    /**
     *
     * @param String $module
     * @param String $action
     */
    public static function getRouteModuleAction($module, $action = 'index')
    {
        return (empty($module) && empty($action)) ? '' : "{$module}/{$action}";
    }

    /**
     *
     * @param String $dirname
     * @param type $mode Description
     * @return boolean
     */
    public static function createDir($dirname, $mode)
    {
        if (is_dir($dirname) || mkdir($dirname, $mode)) {
            return true;
        }
        if (!self:: createDirLinux(dirname($dirname), $mode)) {
            return false;
        }
        return mkdir($dirname, $mode);
    }

    /**
     * 根据KEY 获得缓存内容
     *
     * @param String $key
     * @return Ambigous <string, mixed>|string
     */
    public static function cacheGet($key)
    {
        if (file_exists($key)) {
            $result = unserialize(file_get_contents($key));
            return isset($result ['content']) ? $result ['content'] : '';
        }
        return '';
    }

    public static function getBasePathMap($path = '')
    {
        if (!empty($path)) {
            if (!isset(self::$selfBasePathMap[$path])) {
                self::setBasePathMap($path);
            }
            return self::$selfBasePathMap['@' . $path];
        }
        return self::$selfBasePathMap;
    }

    /**
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * @param $path
     * @return string *
     */
    public static function setBasePathMap($path)
    {
        return self::$selfBasePathMap["@{$path}"] = DIR_SERVER . $path . D_S;
    }

    /**
     * 把别名转换成真实路径 @param string $alias 别名
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * @param $alias
     * @return string *
     */
    public static function getPathOfAlias($alias)
    {
        $namespaceDividString = explode('/', str_replace('\\', '/', ltrim($alias, '@')));
        $nameSpaceBasePath = array_shift($namespaceDividString);

        if (!isset(self::$selfBasePathMap['@' . $nameSpaceBasePath])) {
            self::setBasePathMap($nameSpaceBasePath);
        }
        return self::$selfBasePathMap['@' . $nameSpaceBasePath] . implode('/', $namespaceDividString);

    }

    /**
     * 自动加载类方法
     *
     * @param string $className 类名称
     * @return 当是否成功加载类返回true or false
     */
    public static function autoload($className)
    {

        if (empty($className)) {
            return true;
        }
        $flag = true;
        if (class_exists('\\' . ltrim($className, '\\'))) {
            return $flag;
        }

        $namespaceDividString = explode('/', str_replace('\\', '/', $className));
        $classNameBase = array_pop($namespaceDividString);

        if (count($namespaceDividString) < 1) {
            return $flag;
        }
        $nameSpaceBasePath = array_shift($namespaceDividString);
        if (!isset(self::$selfBasePathMap['@' . $nameSpaceBasePath])) {
            self::setBasePathMap($nameSpaceBasePath);
        }


        $pathBase = self::$selfBasePathMap['@' . $nameSpaceBasePath];


        //如果命令空间不存在或者类已加载，则不需要重新加载
        if (empty($classNameBase)) {
            return $flag;
        }

        $flagClassExists = false;

        //如果命令空间不存在
        if (!empty($classNameBase)) {
            class_exists($classNameBase) ? $flagClassExists = true : '';
        }

        $includeFile = $pathBase . implode(D_S, $namespaceDividString) . D_S . "{$classNameBase}.php";

        if (!file_exists($includeFile)) {
            throw new AHttpException(404, "the file：{$includeFile}  is not exists!");
        }
        if (file_exists($includeFile) && !$flagClassExists) {
            require_once $includeFile;
        }

        return $flag;
    }

    /**
     * 获得控制器方法名
     * @param String $method
     * @return String
     */
    public static function getMethodName($method)
    {
        return 'action' . ucfirst($method);
    }

    /**
     * 简单缓存操作
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * @param $_config
     * @return array *
     */
    public static function cache($_config)
    {
        //   $instance    =
        self:: getInstance();
        $cache_array = $_config ['cache'];
        $arr = array();
        foreach ($cache_array as $key => $val) {
            $class = ucfirst($val ['class']);
            switch ($class) {
                case 'Redis' : // redis
                    $arr [$key] = new RedisClass($val ['host'], $val ['port']);
                    break;
                case 'FileCache' : // 文件缓存
                    $fileCache = new FileCacheClass ();
                    $val ['file_name_prefix'] != '' && $fileCache->setCacheDir($val ['file_name_prefix']);
                    $val ['mode'] != '' && $fileCache->setCacheMode($val ['mode']);
                    $arr [$key] = $fileCache;
                    break;
            }
        }
        unset($_config);
        return $arr;
    }

    /**
     * 简单缓存操作
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * @return ABaseObject *
     */
    public static function base()
    {
        $instance = self:: getInstance();
        $controller = new AController ();
        $_config = $instance::$_config;
        $obj = new ABaseObject($_config);
        $obj->controller = $controller;
        $obj->request = new ARequest ();
        $obj->response = new AResponse ();
        unset($_config);
        return $obj;
    }

    /**
     * 或得本机的IP地址
     */
    public static function getServerIp()
    {
        $ip = $_SERVER['SERVER_ADDR'];

        if (isset($_SERVER['SERVER_ADDR'])) {//如果有针对本机的私有配置
            if (in_array($_SERVER['SERVER_ADDR'], array(
                '::1',
                '127.0.0.1',
                'localhost'))) {//如果是测试环境
                $ip = '127.0.0.1';
            }
        } else {//指定本机的IP地址，以便于能够运行命令行下的 PHP脚本
            $temp = require_once DIR_SERVER . 'ip_address.php';
            $ip = $temp['private'];
        }
        return $ip;
    }

    /**
     * 当前代码部署的服务器机房,保留方法
     * @return string
     */
    public static function getMachineRoom()
    {
        return 'default';
    }

}

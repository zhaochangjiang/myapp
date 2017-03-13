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
define('FRAME_MICROTIME', microtime(true));
define('FRAME_TIMESTAMP', time());

//框架抛出的异常代码
define("FRAME_THROW_EXCEPTION", 1000);

// 所用数据库类型？
defined('DATABASE_TYPE') or define('DATABASE_TYPE', 'Mysql');

// 接口令牌,用户调用接口时判断是否合法.
defined('TOKEN') or define('TOKEN', 'f#jPk9$0');
/**
 * *******************服务目录配置 start*******************
 */
// 当前系统服务器目录
defined('DIR_SERVER') or define('DIR_SERVER', dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR);
// --------------------系统配置Over-----------------------------------/
// 框架基础类所在文件位置
defined('DIR_FRAMEWORK') or define('DIR_FRAMEWORK', DIR_SERVER . 'vendor/framework' . DIRECTORY_SEPARATOR);

defined('IS_DEBUG') or define('IS_DEBUG', true);

//是否为接口访问
defined('IS_CLIENT') or define('IS_CLIENT', false);


require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AppBase.php';

/**
 * *******************服务目录配置 over*******************
 *
 * *服务目录配置over
 *
 * 系统核心类
 *
 * @author zhaocj
 */
class ABaseApplication extends AppBase
{

    private static $app;//本类实例对象

    private static $_config;//系统配置
    private static $_basePath = null;
//    public static $enableIncludePath = true;

    //命名空间与目录的对应关系
    public static $nameSpacePathMap = array(
        '@framework' => DIR_FRAMEWORK,//框架与命名空间对应关系
    );
    // private static $_basePath;
    //public static $classMap = array();


    //URL路径拼接分割符
    public static $delimiterModuleAction = '_';

    //默认引入的文件
    //    private static $_aliases = array(
    //        'system' => DIR_FRAMEWORK); // alias
    //private $clientResultData;


    // 异常错误
    const EXCEPTION_HANDLER = "handleException";    //设置异常处理类中的异常对应方法
    const ERROR_HANDLER = "handleError";            //设置异常处理类中的错误对应方法
    const SHUTDOWN_HANDLER = "handleShutdown";      //设置异常处理类中的结束对应方法


    /**
     * 实例对象
     * @return type
     */
    public static function getInstance()
    {
        if (self::$app === null) {
            self::$app = new self ();
        }
        return self::$app;
    }

    private static function mergeNameSpacePathMap()
    {
        if (is_array(self::$_config['nameSpacePathMap'])) {
            self::$nameSpacePathMap = array_merge(self::$nameSpacePathMap,
                self::$_config['nameSpacePathMap']
            );
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
        // 注册__autoload()函数
        spl_autoload_register(array(__CLASS__, 'autoload'));

        // 错误控制
        $errorHandler = new AErrorHandler ();
        set_error_handler(array($errorHandler, self::ERROR_HANDLER));
        set_exception_handler(array($errorHandler, self::EXCEPTION_HANDLER));
        register_shutdown_function(array($errorHandler, self::SHUTDOWN_HANDLER));

        if (!file_exists($configFile)) {
            throw new RuntimeException("The config file:\"{$configFile}\" can't null! at line:" . __LINE__
                . ',in file:' . __FILE__, FRAME_THROW_EXCEPTION);
        }
        self::$_config = include_once $configFile;

        //初始化基础配置合并命名空间
        self::mergeNameSpacePathMap();

        return self::getInstance();
    }

    /**
     *
     * @return string
     */
    public function getAccessToken()
    {
        return md5(TOKEN . session_id());
    }


    /**
     *
     */
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
        include_once DIR_FRAMEWORK . 'bin' . DIRECTORY_SEPARATOR . 'ASession.php';
    }


    /**
     * 框架启动运行方法
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * * @param null $controllerString
     * @param null $action
     * @param null $moduleString
     * * @throws Exception
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
            $moduleAction = '';
            //如果是URL重写的请求
            if ($_config ['urlManager'] ['rewriteMod']) {
                $dirScriptName = dirname($_SERVER['SCRIPT_NAME']);
                $baseName = ltrim(substr($_SERVER['REQUEST_URI'], strlen($dirScriptName)), '/');
                if (stripos($baseName, '?') !== false) {
                    $baseName = substr($baseName, 0, stripos($baseName, '?'));
                }
                $moduleActionLength = strlen($moduleAction);
                empty($_config ['urlManager']['extendFile']) ? '' : $moduleAction =
                    substr($baseName, 0, $moduleActionLength - strlen($_config ['urlManager']['extendFile']));
                if ($moduleAction === $baseName) {//如果是php的请求
                    $moduleAction = $controller->getInput('r');
                }
            } else {
                $moduleAction = $controller->getInput('r');
            }

            //生成路由地址
            list ($moduleString, $controllerString, $action) = self:: getRoute(empty($moduleAction) ? self::getDefaultModuleAction() : $moduleAction);
        }

        if (empty($controllerString) || empty($action)) {

            throw new AHttpException(404, "The page is not exists!");
        }

        // 获得控制器
        $className = $this->getControllerNameSpace($moduleString) . '\\Controller' . ucfirst($controllerString);

        // 判断Method是否存在
        $moduleDeal = new $className($controllerString, $action, $moduleString);

        //var_dump($moduleDeal);
        $moduleDeal->init();


        //加载插件
        self::loadAWidget($moduleDeal);

        //获得方法名称
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
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * * @param $moduleString
     * @return string * * @throws Exception
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

    public
    static function setBasePath($path)
    {
        if ((self::$_basePath = realpath($path)) === false || !is_dir(self::$_basePath)) {
            throw new RuntimeException("{$path} is not a directory! the Error is at line:" .
                __LINE__ . ', in file:' . __FILE__, FRAME_THROW_EXCEPTION);
        }
    }


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
                throw new RuntimeException("the program is error on creating Path!  the Error is at line:" .
                    __LINE__ . ', in file:' . __FILE__, FRAME_THROW_EXCEPTION);
            case 1:
                list($result [1], $result [2]) = explode(self::$delimiterModuleAction, $defaultModuleAction);
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
    public
    static function getSession($key = '')
    {
        $session = $_SESSION;
        return empty($key) ? $session : (isset($session[$key]) ? $session[$key] : '');
    }

    /**
     * 销毁Session
     */
    public
    static function sessionDestroy()
    {
        session_destroy();
    }

    /*
     * HTTP错误 @param $message 错误内容 @param $code 错误代码
     */

    public
    static function error($message, $code = 404)
    {

        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AHttpException.php';
        throw new AHttpException($code, $message);
    }

    /**
     * 将一个对象转换成一个数组
     *
     * @param Object $object
     * @return multitype:
     */
    public
    static function objectToArray($object)
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
    public
    static function setSession($key, $value)
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
    public static function setNameSpacePathMap($path)
    {
        return self::$nameSpacePathMap["@{$path}"] = DIR_SERVER . $path . DIRECTORY_SEPARATOR;
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
        $nameSpacePath = array_shift($namespaceDividString);

        if (!isset(self::$nameSpacePathMap['@' . $nameSpacePath])) {
            self::setNameSpacePathMap($nameSpacePath);
        }
        return self::$nameSpacePathMap['@' . $nameSpacePath] . implode('/', $namespaceDividString);

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
        if (!isset(self::$nameSpacePathMap['@' . $nameSpaceBasePath])) {
            self::setNameSpacePathMap($nameSpaceBasePath);
        }


        //如果命令空间不存在或者类已加载，则不需要重新加载
        if (empty($classNameBase)) {
            return $flag;
        }

        $flagClassExists = false;

        //如果命令空间不存在
        if (!empty($classNameBase)) {
            class_exists($classNameBase) ? $flagClassExists = true : '';
        }
        $includeFile = self::$nameSpacePathMap['@' . $nameSpaceBasePath] . implode(DIRECTORY_SEPARATOR, $namespaceDividString) . DIRECTORY_SEPARATOR . "{$classNameBase}.php";

        if (!file_exists($includeFile)) {

            throw new AHttpException(404, "[ ERROR ]the file：{$includeFile}  is not exists ." . PHP_EOL . "[ MESSAGE ] throw Exception at line:" . __LINE__ . ",in file:" . __FILE__ . "!");
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
    public
    static function getMethodName($method)
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
    public
    static function cache($_config)
    {
        //   $instance    =
        self:: getInstance();
        $cache_array = $_config ['cache'];
        $resultData = array();
        foreach ($cache_array as $key => $val) {
            $class = ucfirst($val ['class']);
            switch ($class) {
                case 'Redis' : // redis
                    $resultData [$key] = new RedisClass($val ['host'], $val ['port']);
                    break;
                case 'FileCache' : // 文件缓存
                    $fileCache = new FileCacheClass ();
                    $val ['file_name_prefix'] != '' && $fileCache->setCacheDir($val ['file_name_prefix']);
                    $val ['mode'] != '' && $fileCache->setCacheMode($val ['mode']);
                    $resultData [$key] = $fileCache;
                    break;
            }
        }
        unset($_config);
        return $resultData;
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
    public
    static function getServerIp()
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
    public
    static function getMachineRoom()
    {
        return 'default';
    }

    /**
     * 开始方法
     * @return mixed
     */
    public function before()
    {
        // TODO: Implement before() method.
    }

    /**
     * 结束方法
     * @return mixed
     */
    public function after()
    {
        // TODO: Implement after() method.
    }
}

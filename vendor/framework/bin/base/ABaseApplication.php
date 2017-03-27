<?php

namespace framework\bin\base;

use framework\bin\exception\AHttpException;

use framework\bin\exception\AErrorHandler;
//use framework\bin\base\AHelper;

use framework\bin\session\ASession;
use framework\bin\urlRewrite\AUrlManager;
use RuntimeException;

use ReflectionClass;

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
defined('DIR_SERVER') or define('DIR_SERVER', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DIRECTORY_SEPARATOR);
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
    // 异常错误
    const EXCEPTION_HANDLER = "handleException";    //设置异常处理类中的异常对应方法
    const ERROR_HANDLER = "handleError";            //设置异常处理类中的错误对应方法
    const SHUTDOWN_HANDLER = "handleShutdown";      //设置异常处理类中的结束对应方法

    /**
     * @var ABaseApplication
     */
    public static $app;//本类实例对象
    private static $_config;//系统配置
    private static $_basePath = null;
    protected static $defaultClassMap = array(

        //默认实例化的Session对象
        'session' => 'framework\bin\session\ASession',

        //默认实例化的URL重写对象
        'urlManager' => 'framework\bin\urlRewrite\AUrlManager',
    );

    //命名空间与目录的对应关系
    public static $nameSpacePathMap = array(
        '@framework' => DIR_FRAMEWORK,//框架与命名空间对应关系
    );

    /**
     * @var AUrlManager
     */
    public $urlManager;//URL对象管理

    /**
     * @var ASession
     */
    public $session; //用户登录信息管理

    /**
     * @var array
     */
    public $parameters;//配置参数


    /**
     * 构造函数
     *
     */
    private function __construct()
    {

    }

    /**
     * 获得系统当前的运行环境
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * @return string *
     */
    public static function getEnvironment()
    {
        $envString = 'DEV';
        $envIni = get_cfg_var('enviorment');
        if ($envIni) {
            $envString = $envIni;
        }
        return $envString;
    }

    /**
     * 初始化本类的一些属性
     * @return object
     */
    protected function _init()
    {

        //获得类的私的属性
        $reflectionClass = new ReflectionClass(__CLASS__);
        $properties = $reflectionClass->getProperties();
        $staticProperties = array_keys($reflectionClass->getStaticProperties());

        //只是实例化非Static属性
        foreach ($properties as $k => $property) {
            $propertyName = $property->getName();
            if (in_array($propertyName, $staticProperties)) {
                continue;
            }

            $this->_initObject($propertyName);
        }
        $this->setBasePath();
        return $this;
    }

    /**
     * 获得数据库配置
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     *
     * @return mixed *
     */
    public function getDatabaseConfig()
    {
        return self::$_config['database'];
    }


    /**
     * 实例对象
     * @return object
     */
    public static function getInstance()
    {
        if (self::$app === null) {
            self::$app = new self ();
        }
        return self::$app;
    }

    /**
     * 将配置文件中的命名空间和目录对应关系合并到一起
     * @return void
     */
    private static function mergeNameSpacePathMap()
    {
        if (is_array(self::$_config['nameSpacePathMap'])) {
            self::$nameSpacePathMap = array_merge(self::$nameSpacePathMap,
                self::$_config['nameSpacePathMap']
            );
        }
    }

    /**
     * 捕获异常
     */
    protected static function handlerException()
    {
        // 注册__autoload()函数
        spl_autoload_register(array(__CLASS__, 'autoload'));

        // 错误控制,将系统中的错误、异常信息监听下来。为报错系统提供详细定位
        $errorHandler = new AErrorHandler ();
        set_error_handler(array($errorHandler, self::ERROR_HANDLER));
        set_exception_handler(array($errorHandler, self::EXCEPTION_HANDLER));
        register_shutdown_function(array($errorHandler, self::SHUTDOWN_HANDLER));


        return;
    }

    /**
     * 创建应用
     *
     * @param string $configFile
     * @return AApplication
     */
    public static function createApplication($configFile)
    {
        //捕获异常，设置探针.
        self::handlerException();

        if (self::$app === null) {
            self::$app = new self ();
        }
        self::$app->_initConfig($configFile);

        //初始化基础配置合并命名空间
        self::mergeNameSpacePathMap();

        return self::$app->_init();

    }

    protected function _initConfig($configFile)
    {

        if (!file_exists($configFile)) {

            throw new RuntimeException("[ ERROR ] The config file:\"{$configFile}\" is not exists!" . PHP_EOL . "[ MESSAGE ] The error is at line:" . __LINE__
                . ',in file:' . __FILE__, FRAME_THROW_EXCEPTION);
        }

        self::$_config = include_once $configFile;
        return;
    }

    /**
     * 框架启动运行方法
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * @param null $controllerString
     * @param null $action
     * @param null $moduleString
     * @throws Exception
     */
    public function run($controllerString = null, $action = null, $moduleString = null)
    {
        // $moduleString     = $controllerString = $action           = '';
        //如果不是 AFunction 中函数C控制的方法
        if ($moduleString === null && $controllerString === null && $action === null) {

            $this->urlManager->_initModuleAction();
            $moduleAction = $this->urlManager->getModuleAction();

            //生成路由地址
            list ($moduleString, $controllerString, $action) = $this->urlManager->getRoute(empty($moduleAction) ? $this->getDefaultModuleAction() : $moduleAction);

        }

        if (empty($controllerString) || empty($action)) {

            throw new AHttpException(404, "The page is not exists!");
        }

        // 获得控制器
        $className = $this->getControllerNameSpace($moduleString) . '\\Controller' . ucfirst($controllerString);

        // 判断Method是否存在
        $moduleDeal = new $className($controllerString, $action, $moduleString);

        $moduleDeal->init();

        //加载插件
        self::loadAWidget($moduleDeal);

        //获得方法名称
        $methodName = $this->getMethodName($action);
        // 如果方法不存在，新增动态action
        if (!method_exists($moduleDeal, $methodName)) {//&& !isset($moduleDeal->actionMaps [$methodName])
            throw new AHttpException(404, "The Method \"$methodName\" doesn't exist! in {$className}!");
        }
        $moduleDeal->beforeMethod();
        $moduleDeal->$methodName();
        $moduleDeal->afterMethod();
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
    protected function getControllerNameSpace($moduleString)
    {

        if (empty(self::$_config['controllerNameSpace'])) {
            throw new RuntimeException('ther controller $_config[\'controllerNameSpace\'] is null,you need set it in config! the error is at line:' . __LINE__ . ',in file:' . __FILE__);
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
    private function getDefaultModuleAction()
    {
        $defaultModuleAction = $this->parameters->defaultModuleAction;
        if (empty($defaultModuleAction)) {
            $defaultModuleAction = $this->urlManager->getDefaultModuleAction();
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
            throw new RuntimeException("{$path} is not a directory! the Error is at line:" .
                __LINE__ . ', in file:' . __FILE__, FRAME_THROW_EXCEPTION);
        }
    }


    /**
     * HTTP错误 @param $message 错误内容 @param $code 错误代码
     */
    public static function error($message, $code = 404)
    {

        throw new AHttpException($code, $message);
    }


    /**
     * @param string $path
     * @return mixed
     */
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
     * @param $alias
     * @return string
     */
    public function getDirectoryByNamespace($alias)
    {
        $namespaceString = explode('/', str_replace('\\', '/', ltrim($alias, '@')));
        $nameSpacePath = array_shift($namespaceString);

        if (!isset(self::$nameSpacePathMap['@' . $nameSpacePath])) {
            self::setNameSpacePathMap($nameSpacePath);
        }
        return self::$nameSpacePathMap['@' . $nameSpacePath] . implode(DIRECTORY_SEPARATOR, $namespaceString);

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

            include_once dirname(__FILE__) . '/../exception/AHttpException.php';
            throw new RuntimeException(
                "[ ERROR ] The file：{$includeFile}  is not exists ."
                . PHP_EOL . "[ MESSAGE ] Throw Exception at line:"
                . __LINE__ . ",in file:" . __FILE__
                , FRAME_THROW_EXCEPTION);
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
    protected function getMethodName($method)
    {
        return 'action' . ucfirst($method);
    }




    /**
     * 当前代码部署的服务器机房,保留方法
     * @return string
     */
    protected function getMachineRoom()
    {
        return isset(self::$app->paramerters->generatorRoom) ? self::$app->paramerters->generatorRoom : 'default';
    }

    /**
     * 实例化本类的非static属性
     * @param $objectKey
     * @return  void
     */
    protected function _initObject($objectKey)
    {

        //加载类名，如果类不存在，则用默认的类指定，
        if (empty(self::$_config[$objectKey]['class']) && !empty(self::$defaultClassMap[$objectKey])) {
            self::$_config[$objectKey]['class'] = self::$defaultClassMap[$objectKey];
        }
        $className = self::$_config[$objectKey]['class'];

        if (!empty($className)) {//如果是一个类对象,
            unset(self::$_config[$objectKey]['class']);

            $this->$objectKey = new $className();

            //给类注入参数
            $this->$objectKey->setParams((array)self::$_config[$objectKey]);
            $this->$objectKey->run();
            return;
        }

        //实例化不是对象，只是参数的数据
        foreach (self::$_config[$objectKey] as $key => $value) {
            $this->$objectKey->$key = $value;
        }

    }
}

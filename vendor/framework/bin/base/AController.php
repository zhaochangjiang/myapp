<?php
/**
 * Created by PhpStorm.
 * User: karl.zhao
 * Date: 2017/3/13
 * Time: 10:03
 */

namespace framework\bin\base;

use framework\App;
use RuntimeException;
use framework\bin\utils\AUtils;
use framework\bin\http\ACurlManager;

/**
 * 系统控制器基类
 *
 * @author zhaocj
 *
 */
class AController extends ABaseController
{

    protected $lifeTime = 0; // 本页面缓存的时间分钟数

    protected $authValue = null; //

    protected $templateFile; // 本URL对应的HTML模板文件路径
    protected $data; // 本页面对应的路径
    protected $breadCrumbs = array(); // 面包屑内容
    protected $cssFile = array(); // 网页的CSS文件
    protected $jsFileBefore = array(); // 网页前边的JS文件
    protected $jsFileAfter = array(); // 网页后边的JS文件
    protected $jsStr;
    protected $params = null;


    protected $theme; // 模板风格
    protected $layout = 'main';

    // seo相关
    protected $pageTitle; // 标题
    protected $pageKeywords;
    protected $pageDescription;
    protected $actionMaps = array(); // 动态创建的action
    protected $pageCache = array(); // 配置页面缓存
    protected $loadData = array(); //设置loadViewCell数据
    protected $pageCacheClass;
    protected $pageCacheClassFragment = array();
    protected $applicationDIr = '';

    /**
     * 启动前需要做的工作
     */
    public function init()
    {
        $this->before();
    }


    /**
     * 调用方法前执行的函数
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     *
     * @return mixed *
     */
    public function beforeMethod()
    {
        $this->params = $this->getRequestParams();
    }

    /**
     * 调用方法后执行函数
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     *
     * @return mixed *
     */
    public function afterMethod()
    {
    }

    /**
     * 构造方法
     *
     * @param String $module
     * @param String $action
     */
    public function __construct($controllerString = null, $method = null, $moduleString = null)
    {
        parent:: __construct($controllerString, $method, $moduleString);


        $this->pageCache = $this->pageCache == null ? $this->pageCacheSet() : $this->pageCache;
    }

    protected function pageCacheSet()
    {
        return $this->pageCache;
    }

    // 清除页面缓存
    protected function clearPageCache()
    {
        //$cache = new PageCacheClass ( );
        $this->pageCacheClass->clearCache();
    }

    // action前
    public function before()
    {
        $pageAction = explode(',', $this->pageCache ['action']);

        if ($this->pageCache != null && in_array($this->action, $pageAction)) {
            $pageCacheClass = new PageRedisCacheClass ();
            $pageCacheClass->lifeTime = $this->pageCache ['lifeTime'];
            $pageCacheClass->init();
            $flag = $pageCacheClass->startCache();
            $this->pageCacheClass = $pageCacheClass;
            if (!$flag) {
                exit;
            }
        }
    }

    // action后
    public function after()
    {
        $pageAction = explode(',', $this->pageCache ['action']);
        if ($this->pageCache != null && in_array($this->action, $pageAction)) {
            //$pageCacheClass = new PageCacheClass ( );
            $this->pageCacheClass->endCache();
        }

        SHOW_DEBUG_TRACE && $this->debugSQL();
    }

    /*
     * 片断缓存 示例：<?php if($this->beginCache('commonQuestion',array('lifeTime'=>'24*3600'))){;?> 缓存的内容 <?php $this->endCache();}?>
     */
    protected function beginCache($id, $options = array())
    {
        $pageCacheClass = new PageRedisCacheClass ();
        $pageCacheClass->id = $id;
        $pageCacheClass->lifeTime = $options ['lifeTime'];
        $pageCacheClass->init();
        $flag = $pageCacheClass->startCache();
        $this->pageCacheClassFragment = $pageCacheClass;
        return $flag;
    }

    protected function endCache()
    {
        $this->pageCacheClassFragment->endCache();
    }

    /**
     * 判断是否登录
     * @return
     */
    protected function dealNotlogin()
    {
        $session = ABaseApplication::getSession();
        $isiframe = $this->getInput('isiframe');
        if (empty($session ['uid'])) { // 判断当前用户是否登录
// message("你还没有登录");
            !empty($isiframe) ? redirectIframe($this->getLoginUrl()) : redirect($this->getLoginUrl());
        }
        return $session;
    }


    /*
     * 魔术方法
     */
    public function __call($name, $args)
    {
        if (isset($this->actionMaps [$name])) {
            call_user_func_array($this->actionMaps [$name], $args);
        }
    }

    /*
     * 动态创建action @param $name action名称 @param $mCallable 执行调用的方法
     */
    protected function createAction($name, $mCallable)
    {
        $name = $name . 'Action';
        $this->actionMaps [$name] = is_callable($mCallable) ? $mCallable : create_function('', $mCallable);
    }

    /**
     * 获得当前路径加载的文件件列表
     *
     * @return multitype:
     */
    public function getLoadFile()
    {
        return get_included_files();
    }

    /**
     * 退出程序
     */
    public function end()
    {
        exit();
    }

    /**
     * 查看本次请求截止调用行已经执行过的SQL语句
     */
    public function debugSQL()
    {
        $object = ADatabaseMysql::$debugMessage;
        if (defined('DEBUG_SQL_NUM')) {
            $object = array_slice($object, -DEBUG_SQL_NUM, DEBUG_SQL_NUM);
        }
        $path = App:: getSystemViewPath() . DIRECTORY_SEPARATOR . 'bottom_trace.php';
        include_once $path;
    }

    /**
     *
     * @param String $password
     */
    public function encryption($password)
    {
        try {
            // return @md5 ( '~2' . md5 ( "!@{$password}!@" ) . '9' );
            return substr('d2sf' . md5($password) . md5($password) . 'g51s', -40);
        } catch (Exception $e) {
            throw $e;
        }
    }

// __get()方法用来获取私有属性
    public function __get($property)
    {
        if (isset($this->$property))
            return ($this->$property);
        return null;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * 展示错误信息页面
     *
     * @param Array $param
     * @param Integer $errorCode
     */
    protected function showError($param = array(), $errorCode = 404)
    {

    }

    public function getTemplateDir()
    {
        $templateDir = $this->applicationDIr . 'modules';
        $path = ($this->theme != null ? $templateDir . $this->theme : $templateDir) . DIRECTORY_SEPARATOR;
        $extendPathString = '';
        if ($this->moduleString) {
            $extendPathString = $this->moduleString . DIRECTORY_SEPARATOR;
        } else {
            $extendPathString = ABaseApplication::getDefaultModule() . DIRECTORY_SEPARATOR;
        }
        $path .= $extendPathString .= 'views' . DIRECTORY_SEPARATOR;
        return $path;
    }

    /**
     *
     * @param
     *            设置HTML模板文件
     *            有三种方式,为空的话加载action相对应得模板
     *            也可以加载该模块下其他模板文件（比如表单共用），还可以加载其他模块下的模板文件如data/recipe
     */
    protected function setTemplateFile($templateFile)
    {
        $this->controllerString = lcfirst($this->controllerString);

        if (!empty($templateFile)) {
            $this->templateFile = $this->getTemplateDir() . $templateFile;
            return;
        }

        $this->templateFile = $this->getTemplateDir() . $this->controllerString . DIRECTORY_SEPARATOR . $this->action . '.php';
    }


    /**
     * 调用接口工具方法
     * @param String $moduleAction
     * @param array|null $gets
     * @param array|null $posts
     * @return String
     */
    protected function httpConnectionByBase($moduleAction, $gets = array(), $posts = array())
    {
        $aCurlManager = new ACurlManager();
        $url = $this->createUrl($moduleAction, $gets, App::$app->parameters->domain['client']);

        return $aCurlManager->httpConnectionByUrl($url, $posts);
    }

    /**
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     *
     * @return mixed *
     */
    private function getTemplateFile()
    {
        return $this->templateFile;
    }

    private function _getLayout()
    {
        return $this->getCommonLayoutDirecty() . $this->layout . '.php';
    }

    /**
     * 获得公共的模块Layout
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @return type
     */
    public function getCommonLayoutDirecty()
    {
        return $this->applicationDIr . 'modules/layout' . DIRECTORY_SEPARATOR;
    }

    /**
     * 加载模板文件
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @param String $templateFile
     * @param array $params 传递参数
     */
    public function render($templateFile = null, $params = array())
    {
        $this->setTemplateFile($templateFile);
        $layout = $this->_getLayout();
        $template = $this->getTemplateFile();
        // 判断模板文件是否存在
        if (!file_exists($template)) {
            throw new RuntimeException("The template file:{$template} is not exist! at line:" . __LINE__ . ',in file：' . __FILE__, FRAME_THROW_EXCEPTION);
        }

        if (!file_exists($layout)) {
            throw new RuntimeException("The layout file:{$layout} is not exist. at line:" . __LINE__ . ',in file：' . __FILE__, FRAME_THROW_EXCEPTION);
        }

        ob_start();
        require_once $template;
        $this->contentHtml = ob_get_contents();
        ob_end_clean();
        include_once $layout;
        $this->after();
    }

    /**
     * 获得模板内容
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @return type
     */
    public function getTemplateContent()
    {
        echo $this->contentHtml;
    }

    //渲染不包括layout
    protected function display($templateFile, $params = array())
    {
        $this->setTemplateFile($templateFile);

        // 判断模板文件是否存在
        if (!file_exists($this->templateFile)) {
            throw new RuntimeException("The template file:{
                $this->templateFile} is not exist", FRAME_THROW_EXCEPTION);
        }
        extract($params);

        require_once $this->templateFile;
    }

    /**
     * 加载局部的BLOck
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @param type $templateFile
     * @param type $data
     * @return type
     */
    protected function loadView($templateFile, $data = array())
    {

        $path = $this->theme != null ? App:: getPathOfAlias('application.theme.' . $this->theme) : App:: getPathOfAlias('application.template');
        if (!empty($templateFile)) {
            $array = explode('.', $templateFile); // 如果render了模板，则解析
            $n = count($array);
            if ($n < 2) {
                $template = $path . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . "{
                $templateFile}.php";
            } else if ($n == 3) {
                $template = $path . DIRECTORY_SEPARATOR . $array[0] .
                    DIRECTORY_SEPARATOR . $array[1] . DIRECTORY_SEPARATOR . $array[2] . '.php';
            } else {
                $template = $path . DIRECTORY_SEPARATOR . $array[0] .
                    DIRECTORY_SEPARATOR . $array[1] . '.php';
            }
        }

        ob_start();
        extract($data);
        include_once $template;
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
        return;
    }

    /**
     * 渲染页面,引入模版界面
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @param type $templateFile
     * @param type $params
     */
    public function renderOver($templateFile = null, $params = array())
    {
        $this->render($templateFile, $params);
        exit;
    }

    /**
     * 设置面包屑数据
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @param $param -一维数组
     * @example array(
     *          'href'=>'',
     *          'name'=>'',
     *          'class'=>''
     *          )
     */
    protected function setBreadCrumbs($param, $isArray = false)
    {
        ($isArray === false) ? $this->breadCrumbs [] = $param : $this->breadCrumbs = array_merge($this->breadCrumbs, $param);
    }

    /**
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * 生成面包屑字符串
     * @$separator String 分隔符
     *
     * @return string
     */
    protected function getBreadCrumbs($separator = '&raquo;')
    {
        $breadCrumbString = '';
        if ($this->breadCrumbs) {
            $class = $href = '';
            foreach ($this->breadCrumbs as $value) {
                $class = (!empty($value ['class'])) ? "class=\"{$value['class']}\"" : '';
                $href = (empty($value ['href'])) ? 'javascript:;' : $value ['href'];
                $breadCrumbString .= empty($breadCrumbString) ? "<a {$class} href=\"{$href}\" title=\"{$value['name']}\">{$value['name']}</a>" : " <span class=\"yen\"> {$separator} </span> <a {$class} href=\"{$href}\" title=\"{$value['name']}\">{$value['name']}</a>";
            }
        }
// xmp($breadCrumbString);
        return $breadCrumbString;
    }

    public function setLoadData($data = array())
    {
        $this->loadData = $data;
    }

    public function getAppTemplatePath()
    {
        return $this->templateFile;
    }

    /**
     * 加载局部模块
     *
     * @param $template 模板路径
     * @param $isCache 是否要缓存
     * @param $lifeTime 缓存时间
     *            0 为无限生命
     *
     */
    public function loadViewCell($template, $isCache = false, $name = '', $lifeTime = 0)
    {
        if ($isCache) {
            $fileCache = App:: base()->fileCache;
            $path = App:: getBasePath() . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR;
            $fileCache->setCacheDir($path);
            $_key = $template . '_' . $name;
            $content = $fileCache->get($_key);
        }

        $templateFile = $this->getCommonLayoutDirecty() . $template . '.php';
        if (empty($content)) {
            ob_start();
            extract($this->loadData);
            if (!file_exists($templateFile)) {
                throw new Exception("the file：{$templateFile} is not exist!");
            }
            require_once $templateFile;
            $content = ob_get_contents();
            ob_end_clean();
            $isCache && $fileCache->set($_key, $content, $lifeTime);
        }

        echo $content;
        return;
    }

    /**
     * 验证规则内容返回
     */
    protected function rules()
    {
        require_once DIR_FRAMEWORK_LIB . 'Verify.php';
        $result = array();
        foreach ($this->authValue as $key => $value) {
            $temp = Verify:: doByType($key, $value);

            // 如果符合规则，则不加载相应的内容内
            if (true === $temp ['status']) {
                continue;
            }
            $result [] = $temp;
        }
        return $result;
    }

    /**
     *
     * @param type $className
     * @param type $params
     * @param type $nameSpace BLOCK类的命令空间
     * @return type
     * @throws Exception
     */
    protected function loadBlock($className, $params = array())
    {

        //如果没有设置命名空间，则以默认的目录问准。
        $classNameString = (empty($params['nameSpace'])) ? 'backend\modules\\' . $this->moduleString . '\\blocks\\' . $this->controllerString . '\\Block' . ucfirst($className) : $params['nameSpace'] . '\\Block' . ucfirst($className);
        $class = new $classNameString();
        if (!empty($params['renderTemplate'])) {
            $class->setRenderTemplate($params['renderTemplate']);
        }
        $class->params = $params;
        $class->setControllerObject($this);
        return $class->run();
    }

    protected function file_merger_do($arrFile, $outName, $type)
    {

        $static = App::getBasePath() . '/source/'; //静态资源在服务器上的存储路径，根据自己的情况修改
        $dir = "{$static}{$type}/"; //合成文件在服务器上的存储路径，根据自己的情况修改
        $out = "{$dir}{$outName}";
        if (!is_file($out)) {
            ob_start();
            foreach ($arrFile as $key => $file) {
                include $static . "{$type}/{$file}";
            }
            $str = ob_get_clean();
            $tmp = $dir . 'tmp';


            if ($this->runJava) {
                //java程序精简文件
                file_put_contents($tmp, $str);
                if ($type == 'js') {
                    $exec = "java -jar {$static}yuicompressor-2.4.2.jar --type js --charset utf-8 -v $tmp > $out"; //压缩JS
                } elseif ($type == 'css') {
                    $exec = "java -jar {$static}yuicompressor-2.4.2.jar --type css --charset utf-8 -v $tmp > $out"; //压缩CSS
                }
                `$exec`;
            } else {
                //php程序精简文件
                $str = preg_replace('#/\*.+?\*/#s', '', $str); //过滤注释 /* */
                $str = preg_replace('#(?<!http:)(?<!\\\\)(?<!\')(?<!")//(?<!\')(?<!").*\n#', '', $str); //过滤注释 //
                $str = preg_replace('#[\n\r\t]+#', ' ', $str); //回车 tab替换成空格
                $str = preg_replace('#\s{2,}#', ' ', $str); //两个以上空格合并为一个
                file_put_contents($out, $str);
            }
        }
        return $outName;
    }

    protected function file_merger($arrFile, $outName, $cache = false)
    {
        $url = AUtils::baseUrl() . '/source/'; //静态资源url地址，根据自己的情况修改
        $static = App::getBasePath() . '/source/'; //静态资源在服务器上的存储路径，根据自己的情况修改
        if (substr($arrFile[0], -2) == 'js') {
            $type = 'js';
        } elseif (substr($arrFile[0], -3) == 'css') {
            $type = 'css';
        }

        $dir = "{$static}{$type}/"; //合成文件在服务器上的存储路径，根据自己的情况修改
        $return = "{$url}{$type}/{$outName}";
        $out = "{$dir}{$outName}"; //
        //$time   = $_SERVER['REQUEST_TIME'];
        //当文件不存在,或者调试模式,就执行下边的程序
        //调试模式,按常规加载js,css
        if (IS_DEBUG || !IS_MERAGE) {
            $outHtml = '';

            foreach ($arrFile as $key => $file) {
                if ($type == 'js') {
                    $outHtml .= "<script type=\"text/javascript\" src=\"{$url}js/{$file}?v=" . App::$app->parameters->version . "\"></script>\n";
                } elseif ($type == 'css') {
                    $outHtml .= "<link href=\"{$url}css/{$file}?v=" . App::$app->parameters->version . "\" rel=\"stylesheet\" type=\"text/css\">\n";
                }
            }
            return $outHtml;
        } else {
            //正式环境启动压缩
            $this->file_merger_do($arrFile, $outName, $type);
        }
        if ($cache) {
            switch ($type) {
                case 'js':
                    return "<script type=\"text/javascript\" src=\"{$return}\"></script>\n";
                case 'css':
                    return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$return}\">\n";
                default:
                    break;
            }
        } else {
            switch ($type) {
                case 'js':
                    return "<script type=\"text/javascript\" src=\"{$return}?v=" . App::$app->parameters->version . "\"></script>\n";
                case 'css':
                    return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$return}?v=" . App::$app->parameters->version . "\">\n";
                default:
                    break;
            }
        }
    }

    /**
     * 获得网页头部加载的css文件代码
     *
     * @return the $cssFile
     */
    protected function getCssFile()
    {
        if (!empty($this->cssFile)) {
            return $this->file_merger($this->cssFile, 'common.css', true);
        }
    }

    /**
     * 获得网页头部加载的JS文件代码
     *
     * @return the $jsFileBefore - String
     */
    protected function getJsFileBefore()
    {
        //         $strJsBefore  = '';
        //        $_strJsBefore = '';
        if (!empty($this->jsFileBefore)) {
            return $this->file_merger($this->jsFileBefore, 'main-before.js', true);
        }
    }

    /**
     * 获得网页尾部加载的JS文件代码
     *
     * @return the $jsFileAfter
     */
    protected function getJsFileAfter()
    {
        if (!empty($this->jsFileAfter)) {
            return $this->file_merger($this->jsFileAfter, 'main-after.js', true);
        }
    }

    /**
     *
     * @param String|array $param
     * @return string
     */
    protected function setSHTML($param)
    {
        $baseUrl = baseUrl();
        if (!is_array($param)) {
            return '<script type="text/javascript" src="' . $baseUrl . "/source/js/{$param}?v={$this->version}\"></script>";
        }
        $strJsAfter = '';
        foreach ($param as $value) {
            $strJsAfter .= '<script type="text/javascript" src="' . $baseUrl . "/source/js/{$value}?v={$this->version}\"></script>\n";
        }
        return $strJsAfter;
    }

    /**
     * 设置网页头部加载的CSS文件
     *
     * @param multitype : $cssFile
     */
    protected function setCssFile($cssFile, $merge = false, $name = 'main', $type = 'css')
    {
        if ($merge) {
            $this->cssFile[] = $this->file_merger_do($cssFile, $name . '.' . $type, $type);
        } else {
            (is_array($cssFile)) ? $this->cssFile = array_merge($this->cssFile, $cssFile) : $this->cssFile [] = $cssFile;
        }
    }

    /**
     * 设置网页头部加载的JS文件
     *
     * @param multitype : $jsFileBefore
     */
    protected function setJsFileBefore($jsFileBefore, $merge = FALSE, $name = 'main-before', $type = 'js')
    {
        if ($merge) {
            $this->jsFileBefore[] = $this->file_merger_do($jsFileBefore, $name . '.' . $type, $type);
        } else {
            (is_array($jsFileBefore)) ? $this->jsFileBefore = array_merge($this->jsFileBefore, $jsFileBefore) : $this->jsFileBefore [] = $jsFileBefore;
        }
    }

    /**
     * 设置网页尾部加载的JS文件
     *
     * @param multitype : $jsFileAfter
     */
    protected function setJsFileAfter($jsFileAfter, $merge = FALSE, $name = 'main-after', $type = 'js')
    {
        if ($merge) {
            $this->jsFileAfter[] = $this->file_merger_do($jsFileAfter, $name . '.' . $type, $type);
        } else {
            (is_array($jsFileAfter)) ? $this->jsFileAfter = array_merge($this->jsFileAfter, $jsFileAfter) : $this->jsFileAfter [] = $jsFileAfter;
        }
    }

    protected function getJsStr()
    {
        return $this->jsStr;
    }

    protected function setJsStr($str)
    {
        $this->jsStr = $str;
    }

    /**
     * 校验参数
     *
     * @param Array $verifyArray
     *
     */
    protected function validate($verifyArray)
    {
        $data = array(
            'status' => 'success',
            'message' => array());
        if (empty($verifyArray)) {
            return $data;
        }
        $this->params = array();
        foreach ($verifyArray as $k => $v) {
            $getParam = $v ['getParam']; // 获得参数值
            $len = count($v ['getParam']);
            $function = $getParam [0];
            switch ($len) {
                case 1 :
                    $this->params [$k] = $this->$function($k);
                    break;
                case 2 :
                    $this->params [$k] = $this->$function($k, $getParam ['1']);
                    break;
                case 3 :
                    $this->params [$k] = $this->$function($k, $getParam ['1'], $getParam ['2']);
                    break;
                default :
                    die('$this->verifyArray content is error! ');
                    break;
            }
            $colum = isset($v ['colum']) ? $v ['colum'] : '';
            $pregArr = array();
            if (!empty($v ['validatePreg'])) {
                require_once DIR_FRAMEWORK_LIB . 'Verify.php';
                $validateName = $v ['validatePreg'];
                $pregArr = Verify::$$validateName;
                $colum = $pregArr ['name'];
            }
            $data ['message'] [$k] = array(
                'status' => true,
                'message' => '',
                'typename' => $k,
                'colum' => $colum,
                'isNull' => false);
            if (!empty($v ['validateIsNull']) && empty($this->params [$k])) { // 如果设置验证空状态
                $data ['status'] = 'failure';
                $data ['message'] [$k] = array(
                    'status' => false,
                    'message' => "请输入{$colum}！",
                    'isNull' => true);
            }

            if (!empty($v ['validatePreg']) && false !== $data ['message'] [$k] ['status']) { // 如果过需要经过正则验证
                require_once DIR_FRAMEWORK_LIB . 'Verify.php';
                if (preg_match($pregArr ['preg'], $this->params [$k])) {
                    unset($data ['message'] [$k]);
                } else {
                    $data ['status'] = 'failure';
                    $data ['message'] [$k] ['status'] = false;
                    $data ['message'] [$k] ['message'] = "请输入正确格式的{$colum}！";
                }
            } elseif (empty($v ['validatePreg'])) { // 如果过不需要经过正则验证
                unset($data ['message'] [$k]);
            }

            if (!empty($v ['otherVerifyfunction']) && false !== $data ['message'] [$k] ['status']) {
                $function = $v ['otherVerifyfunction'];
                $data ['message'] [$k] = $this->$function($this->params [$k], $v);
                if (!empty($data ['message'] [$k])) {
                    $data ['status'] = 'failure';
                } else {
                    $data ['status'] = 'success';
                }
            }
        }
        return $data;
    }

    /**
     * iframe内部页面内容展示,完事后 退出(exit)程序
     *
     * @param ResultContent $message - Arrray(
     *            'notexit'=>false,
     *            'message'=>'输入的内容',
     *            );
     */
    protected
    function outPutIframeMessage(ResultContent $message)
    {
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"></head><body>' . $message->message . $message->javascriptContent . '</body></html>';
        if (empty($message->notexit)) {
            exit();
        }
    }

    /**
     * 创建URL
     *
     * @param array $moduleActionArray
     * @param array $params
     * @param string $domain
     * @return String
     */
    public function createUrl($moduleActionArray, $params = array(), $domain = '')
    {

        // $moduleActionArray = explode('.', $moduleAction);
        if (empty($moduleActionArray[2])) {
            unset($moduleActionArray[2]);
        }
        if (empty($moduleActionArray[0]) && empty($moduleActionArray[1])) {
            unset($moduleActionArray[0], $moduleActionArray[1]);
        }
        if (empty($domain)) {
            $domain = AUtils::baseUrl();
        }
        $moduleAction = '';
        $count = count($moduleActionArray);
        switch ($count) {
            case 0:
                break;
            case 1:
                $moduleAction = array_pop($moduleActionArray);
                break;
            case 2:
                $moduleAction = lcfirst($moduleActionArray[0]) . $this->delimiterModuleAction . $moduleActionArray[1];
                break;
            case 3:
                $moduleAction = $moduleActionArray[2] . $this->delimiterModuleAction . lcfirst($moduleActionArray[0]) . $this->delimiterModuleAction . $moduleActionArray[1];
                break;
            default:

                $moduleAction = $moduleActionArray[2] . $this->delimiterModuleAction . lcfirst($moduleActionArray[0]) . $this->delimiterModuleAction . $moduleActionArray[1];
                break;
        }
        return $this->createURLPath($moduleAction, $params, $domain);
    }

    /**
     *
     * @param String $moduleAction
     * @param array $params =array(
     *            key=>value,
     *            key=>array(
     *            'url'=>'',//特殊参数如跳转的字符串
     *            'doType'=>'base64_encode'//额外参数加密采用的加密函数名称base64_encode ,不传值默认base64_encode；
     *            )
     *
     *            )
     * @param string $domain
     * @return string
     */
    private function createURLPath($moduleAction, $params = array(), $domain = '')
    {
        //self::$urlManager不是 ，类AUrlManager的实例对象，或者AUrlManager的子类对象
        if (IS_DEBUG && !is_a(App::$app->urlManager, 'framework\\bin\\urlrewrite\\AUrlManager')) {
            throw new RuntimeException(
                "The flow class is not the  framework\\bin\\AUrlManager or it's sub class."
                . PHP_EOL . var_export(App::$app->urlManager, true)
                . '.the error is at line:' . __LINE__
                . ',in file:' . __FILE__
                , FRAME_THROW_EXCEPTION);
        }
        $urlManager = App::$app->urlManager;
        $urlManager->setCreateUrlParams($moduleAction, $params, $domain);
        return $urlManager->createURLPath();
    }

    /**
     * 获得页面图片显示路径
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     * * @param $url
     * @return string *
     */
    public
    function createSourcePath($url)
    {

        return baseUrl() . "/source/{$url}";
    }

    /**
     * 获得登录链接信息
     *
     * @param $domain
     * @return String -URL格式
     */
    public function getLoginUrl($domain = '')
    {
        return $this->createUrl(App:: base()->loginUrl, null, $domain);
    }

    /**
     * 获得网站首页链接地址
     */
    public
    function getIndex($domain = '')
    {
        return $this->createUrl('Site/index', array(), $domain);
    }

    public function parse_script($urls, $path = "")
    {
        $url = md5(implode(',', $urls));
        empty($path) && $path = App:: getBasePath() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
        $js_url = $path . $url . '.js';
        if (!file_exists($js_url)) {
            if (!file_exists($path))
                mkdir($path, 0777);
            $js_content = '';
            foreach ($urls as $url) {
                $append_content = file_get_contents($url) . "\r\n";
                $packer = new JavaScriptPacker($append_content);
                $append_content = $packer->pack();
                $js_content .= $append_content;
            }
            file_put_contents($js_url, $js_content);
        }
        return $js_url;
    }

    protected
    function enmcrypt($post = array())
    {
        $this->initEmcrypt();
        // $iv = '0000000000000000';
        $post = json_encode($post);
        $block = mcrypt_get_block_size(CIPHER, MODES);
        $pad = $block - (strlen($post) % $block);
        $post .= str_repeat(chr($pad), $pad);
        return base64_encode(mcrypt_encrypt(CIPHER, MCRYPT_KEY, trim($post), MODES));
    }

    private
    function initEmcrypt()
    {
        define('MCRYPT_KEY', '_12WE*E$');
        define('CIPHER', MCRYPT_DES); // MCRYPT_RIJNDAEL_128;
        define('MODES', MCRYPT_MODE_ECB);
    }

    protected
    function demcrypt($post = '')
    {
        $this->initEmcrypt();
        $post = base64_decode($post);
        $post = mcrypt_decrypt(CIPHER, MCRYPT_KEY, $post, MODES);
        //var_dump($post);
        $block = mcrypt_get_block_size(CIPHER, MODES);
        $pad = ord($post [strlen($post) - 1]);
        if ($pad != ord('}'))
            $post = substr($post, 0, strlen($post) - $pad);
        // var_dump($post);
        return json_decode(trim($post), true);
    }

    /**
     * 获得当前请求的全部参数已一维数组的形式返回
     */
    protected function getRequestParams()
    {
        $requestParams = array_merge($_GET, $_POST);
        unset($requestParams['r'], $requestParams['actionName'], $requestParams['actionName'], $requestParams['finalUrl']);
        return $requestParams;
    }

}

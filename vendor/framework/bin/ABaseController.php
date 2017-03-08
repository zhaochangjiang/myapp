<?php

namespace framework\bin;

use framework\App;
use RuntimeException;

/**
 *
 * @author zhaocj
 */
class ABaseController
{

    public $rewriteUrl;
    protected $delimiter = '/';
    public $delimiterModuleAction = '_';
    protected $runJava = true;
    protected $contentHtml = '';
    protected $controllerString; // 本次链接所对应的Controller
    protected $action; // 本次链接Controller所对应的 $action
    protected $moduleString;
    protected $version = '1.0';

    public function __construct($controllerString = null, $action = null, $moduleString = null)
    {
        $this->controllerString = $controllerString;
        $this->action = $action;
        $this->moduleString = $moduleString;


    }

    public function redirect($url)
    {
        header("Location: {$url}");
    }

    /**
     * <p>基础的get方法，从$_GET，$_POST，$_FILE中获取传入的参数的值，当没用参数时，返回""</p>
     *
     * @return - 参数的值；没有该参数时返回空
     * @param $param string
     *            - 参数名
     */
    public function getRouteParam($param)
    {
        if (array_key_exists($param, $_POST))
            return $_POST [$param];
        if (array_key_exists($param, $_GET))
            return $_GET [$param];
        return '';
    }

    /**
     * <p>从$_GET和$_POST中获得传入的正整数类型参数</p>
     * <p>当传入的数值超过PHP_INT_MAX或者传入的不是正整数，则返回零</p>
     *
     * @return - integer类型的参数的值
     * @param $param string
     *            - 参数名
     */
    public function getInt($param)
    {
        $i = intval($this->getRouteParam($param));
        if ($i < 0)
            $i = 0;
        return $i;
    }

    /**
     * 加载公共模块
     *
     * @param $template -
     * @param string $data
     */
    public function loadViewCellCommon($template, $data = array())
    {
        if ($this->thisPagePermit ['isLocalRefresh'] === true)
            return;
        $this->setLoadData($data);
        $this->loadViewCell($template);
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
        $template = trim($template, '/');
        $_key = $template . '_' . $name;
        if ($isCache) {
            $cache = App:: base()->fileCache;
            $content = $cache->get($_key);
        }
        // 如果render了模板，则解析
        $path = App:: getPathOfAlias('application.template');
        $template = $path . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . $template . '.php';

        //如果缓存内容为空,
        if (empty($content)) {
            ob_start();

            foreach ($this->loadData as $key => $value) {
                ${$key} = $value;
            }
            if (!file_exists($template)) {
                throw new RuntimeException("the file ：{$template} is not exists!", FRAME_THROW_EXCEPTION);
            }
            require_once $template;
            $content = ob_get_contents();

            ob_end_clean();

            $isCache && $cache->set($_key, $content, $lifeTime);
        }

        echo $content;
    }

    /**
     * <p>从$_GET和$_POST中获得传入的FLOAT类型参数</p>
     * <p>当传入的数值超过PHP_INT_MAX或者传入的不是正整数，则返回零</p>
     *
     * @param $precision -小数位数
     * @param $param string - 参数名
     * @param $param is_round
     *            - enum(1,2,3):1四舍五入,2去尾加1,3去尾
     * @return - float类型的参数的值
     */
    public function getFloat($param, $precision = 2, $is_round = 1)
    {
        $i = floatval($this->getRouteParam($param));
        if ($i < 0)
            $i = 0;
        switch ($is_round) {
            case 2 :
                $i = floor($i * (pow(10, $precision)));
                $i = $i / (pow(10, $precision));
                break;
            case 3 :
                $i = ceil($i * (pow(10, $precision)));
                $i = $i / (pow(10, $precision));
                break;
            default :
                $i = number_format($i, $precision, '.', '');
                break;
        }
        return $i;
    }

    /**
     * <p>从$_GET和$_POST中获得传入的由<input type='text/password/hidden'>控件传入的参数</p>
     * <p>去掉开头和结尾的全角和半角空格，去掉任意位置出现的回车和换行，将内部N个连续的半角空白变成1个</p>
     *
     * @return - 过滤后的参数
     * @param String $param
     * @param $maxlen integer[optional]
     *            - 截取的长度，以汉字为单位，一个（2、3）字节符号等于两个单字节符号。0表示不截取。
     */
    public function getInput($param, $maxlen = 0)
    {
        $s = trim($this->getRouteParam($param));
        $s = preg_replace('/\s(?=\s)/', '', $s);
        $s = preg_replace('/[\n\r\t]/', '', $s);
        return $this->zhcut($s, $maxlen);
    }

    /**
     * <p>从$_GET和$_POST中获得传入的由<input type='text/password/hidden'>控件传入的参数</p>
     * <p>去掉开头和结尾的全角和半角空格，去掉任意位置出现的回车和换行，将内部N个连续的半角空白变成1个，防止sql注入</p>
     *
     * @return - 过滤后的参数
     * @param $sql_str string
     *            - 参数名
     */
    public function getCheck($sql_str, $maxlen = 0)
    {
        $sql_str = $this->getInput($sql_str, $maxlen);
        $chech = preg_match('/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/', $sql_str); // 进行过滤
        if ($chech) {
            echo "非法注入！";
            exit();
        } else {
            return $sql_str;
        }
    }

    /**
     * <p>取出<input type='checkbox'>传入的参数</p>
     *
     * @return - 过滤后的参数值
     * @param $param string
     *            - 参数名
     * @param $enum array
     *            - 参数值的范围，当为null是表示不限制范围
     */
    public function getArray($param, $enum = null)
    {
        $s = $this->getRouteParam($param);
        if (empty($s)) {
            return array();
        }
        if ($enum == null) {
            if (is_array($s)) {
                return $s;
            }
            return array(
                $s);
        }
        if (is_array($s)) {
            return array_intersect($enum, $s);
        }
        if (in_array($s, $enum)) {
            return array(
                $s);
        }
    }

    /**
     * <p>取出由<select>和radio传入的参数</p>
     *
     * @return - 参数值
     * @param String $param
     *            - 参数名
     * @param Array $enum
     *            - 可选数组
     * @param String $default
     *            - 缺省值
     */
    public function getEnum($param, $enum, $default = '')
    {
        $s = $this->getRouteParam($param);
        if (in_array($s, $enum))
            return $s;
        return $default;
    }

    /**
     * <p>获取textarea的内容</p>
     *
     * @return - 内容
     * @param String $param
     *            - 参数名
     * @param Object $len
     *            - 截取的长度，0时表示不截取
     */
    public function getTextarea($param, $len = 0)
    {
        $s = $this->getRouteParam($param);
        $s = preg_replace('/\r\n/', '\r\n', $s);
        $s = preg_replace('/\n/', '\r\n', $s);
        return $this->zhcut($s, $len);
    }

    /**
     * <p>截取$len长度字符串，一个长度等于一个（2、3）字节符号，等于两个单字节符号。</p>
     *
     * @return - 截取后的字符串
     * @param String $str
     *            - 要截取的字符串
     * @param Integer $len
     *            - 要截取的长度
     * @param $dot string[optional]
     *            - 在截取长度小于全长时添加的后缀
     */
    public function zhcut($str, $len, $dot = "")
    {
        return AUtils::cutStringUtf8($str, $len, $dot);
    }

    /**
     * <p>判断一个UTF8字符串在中文显示意义上的长度</p>
     *
     * @return - 长度值
     * @param String $len
     *            - 字符串
     */
    public function zhlen($s)
    {
        $s = preg_replace("/[\x{0080}-\x{ffff}]/u", "aa", $s);
        return intval(strlen($s) / 2);
    }

    /**
     * <p>大数字压缩 </p> * “1000000”转换为“100万”
     *
     * @param $number -
     *            要转换的数字
     * @return - 转换后的字符串
     * @auth hgb
     */
    public function getAhortNum($number, $need_yuan = false, $need_pre = false)
    {
        $yuan = $need_yuan ? '元' : '';
        $prefix = $need_pre ? '~' : '';
        return $number < 10000 ? $number . $yuan : ($number < 10000000 ? $prefix . sprintf("%.1f", $number / 10000) . "万{$yuan}" : '1千万以上');
    }

    /**
     * <p>获取HTML代码内容</p>
     *
     * @return 经过过滤的HTML代码
     * @param String $param
     *            参数名
     */
    public function getHtml($param)
    {
        $s = $this->getRouteParam($param);
        $conf = array(
            'output-xhtml' => true,
            'drop-empty-paras' => FALSE,
            'join-classes' => TRUE,
            'show-body-only' => TRUE);

        $str = @tidy_repair_string($s, $conf, 'utf8');
        $tree = @tidy_parse_string($str, $conf, 'utf8');
        $html = '';
        $body = @tidy_get_body($str);
        if ($body->child) {
            foreach ($body->child as $child)
                $this->dumpNode($child, $html);
        } else
            return '';
        return $html;
    }

    /**
     * 获取分页的页码值
     *
     * @param String $param
     *            参数名
     * @return integer
     */
    public function getIntPagenow($param)
    {
        $pagenow = $this->getInt($param);
        if ($pagenow < 1)
            return 1;
        return $pagenow;
    }

    /**
     * 私有函数
     *
     * @param $node Object
     * @param $s Object
     */
    private function dumpNode($node, &$s)
    {

// 查看节点名，如果是不允许的标签就直接清除
        switch ($node->name) {
            case 'script' :
            case 'style' :
            case 'textarea' :
            case 'input' :
            case 'iframe' :
            case 'form' :
                return;
                break;
            default :
        }

// 如果是文字节点
        if ($node->type == TIDY_NODETYPE_TEXT) {
            $s .= $node->value;
            return;
        }

// 不是文字节点，那么处理标签和它的属性
        $s .= '<' . $node->name;

// 检查每个属性
        if ($node->attribute) {
            foreach ($node->attribute as $name => $value) {
                /*
                 * 清理一些DOM事件，通常是on开头的， 比如onclick onmouseover等.... 或者属性值有javascript:字样的， 比如href="javascript:"的也被清除.
                 */
                if (strpos($name, 'on') === 0 || stripos(trim($value), 'javascript:') === 0) {
                    continue;
                }
// 保留安全的属性
                $s .= ' ' . $name . '="' . $value . '"';
            }
        }

// 递归检查该节点下的子节点
        if ($node->child) {
            $s .= '>';
            foreach ($node->child as $child) {
                $this->dumpNode($child, $s);
            }
// 子节点处理完毕，闭合标签
            $s .= '</' . $node->name . '>';
        } else {
            /*
             * 已经没有子节点了，将标签闭合 (事实上也可以考虑直接删除掉空的节点)
             */
            if ($node->type == TIDY_NODETYPE_START)
                $s .= '></' . $node->name . '>';
            /*
             * 对非配对标签，比如<hr/> <br/> <img/>等 直接以 />闭合之
             */
            else
                $s .= '/>';
        }
    }

    /**
     * @$rule string 路由规则
     * @$route string 规则映射的新地址
     * @$regx string 地址栏pathinfo字符串
     * @$extension stirng 伪静态拓展名
     * return bool
     */
    public function parseUrlRule($rule, $route, $regx)
    {
        $regx = rtrim($regx, '/'); //去除url反斜杠
//        $regx=urldecode($regx);
        $delimiter = array(
            '/',
            '_',
            '-');
// 把路由规则和地址,分割到数组中，然后逐项匹配
        $ruleArr = multipleExplode($delimiter, $rule);
//        xmp($ruleArr);
// 避免日期匹配
// if (preg_match_all("/\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])|\d{4}[\-](0?[1-9]|1[012])/", $regx, $m)) {
// foreach ($m [0] as $k => $v) {
// $d = explode('-', $v);
// $c = implode('*', $d);
// $regx = str_replace($v, $c, $regx);
// }
// }
//        xmp($ruleArr);
//转化成正则表达式

        if (FALSE !== strpos($rule, '.html') && FALSE !== strpos($route, ':')) {

            $rule = preg_replace("/(.+)$/i", '$1\.html', $rule);
        }


        foreach ($ruleArr as $key => $val) {

            if (strpos($val, ':') == 0) {
                if (FALSE !== strpos($val, '\\d')) {
                    $rule = str_replace($val, "(\d+)", $rule);
                }

                if (FALSE !== strpos($val, '\\w')) {
                    $rule = str_replace($val, "(\w+)", $rule);
                }

                if (FALSE !== strpos($val, '\\AZ')) {
                    $rule = str_replace($val, "([A-Za-z]+)", $rule);
                }

                if (FALSE !== strpos($val, '\\AD')) {
                    $rule = str_replace($val, "([A-Za-z0-9]+)", $rule);
                }


                if (FALSE !== strpos($val, '\\BD')) {
                    $rule = str_replace($val, "([a-z0-9]+)", $rule);
                }

                if (FALSE !== strpos($val, '\\CD')) {
                    $rule = str_replace($val, "([A-Z0-9]+)", $rule);
                }


                if ($pos = strpos($val, '^')) {
                    $mk = substr($val, $pos + 1);

                    $rule = str_replace($val, "({$mk})", $rule);
                }
            }
            if (strpos($val, '^') === FALSE) {
                $rule = preg_replace("/{$val}/", '(.+)', $rule, 1);
            }
        }

//        $rulex = $rule;

        $rule = preg_replace(array(
            '/:/',
            '/\//'), array(
            '',
            '\/'), $rule);

//        echo $route . $rule . '</br>';


        $rule = "/^{$rule}$/i";
//        echo $regx . '|' . $rule . '</br>';
//根据rule形成的正则去填充
        if (preg_match($rule, $regx, $m)) {
//             echo $regx .'|'. $rule . '</br>';
            unset($m[0]);
            $regxArr = array_values($m);
            $match = true;
        }


// $route以数组的格式传递，则取第一个
//        xmp($regxArr);
        $url = $route;
// 匹配检测
        foreach ($ruleArr as $key => $value) {

            if (strpos($value, ':') === 0) {
                if ($pos = strpos($value, '^')) {//排除
                    $stripArr = explode('|', trim(strstr($value, '^'), '^'));
                    if (!in_array($regxArr [$key], $stripArr)) {
                        $match = false;
                        break;
                    }
                }
// 静态项不区分大小写
            } elseif (strcasecmp($value, $regxArr [$key]) !== 0) {
                $match = false;
                break;
            }
        }

//        var_dump($match);
// 匹配成功
        if ($match) {
// 把动态变量写入到数组$matches 中，同时去除静态匹配项
            foreach ($ruleArr as $key => $value) {
                if (strpos($value, ':') === FALSE) {
                    unset($regxArr[$key]);
                }
            }


// 获取数组中的值，目的是配合子模式进行替换
            $values = array_values($regxArr);

//            xmp($values);
// 正则匹配替换,正则需要用'e'作为修饰符

            $url = preg_replace('/:(\d+)/e', '$values[\\1-1]', $url);
//$url = urldecode($url);
// 解析url 格式: 分组/模块/操作?key1=value1&key2=value2
            if (strpos($url, '?') !== false) {

// 分组/模块/操作?key1=value1&key2=value2
                $arr = parse_url($url);
                $paths = explode('/', $arr ['path']);
                parse_str($arr ['query'], $queryArr);
            } elseif (strpos($url, '/') !== false) // 分组/模块/操作)
                $paths = explode('/', $url);
            else // key1=value1&key2=value2
                parse_str($url, $queryArr);


//            xmp($queryArr);
//            xmp($paths);
// 获取 分组 模块 操作
            if (!empty($paths)) {
                $var ['moduleName'] = array_shift($paths);
                $var ['actionName'] = array_shift($paths);
                $var ['actionName'] = preg_replace('/(\w+)(\.[a-zA-Z0-9]+)$/i', '\\1', $var ['actionName']);
                foreach ($paths as $k => $v) {

                    $next = $paths[$k + 1];
                    if ($v != '' && $next != '' && $k % 2 == 0) {
                        if ($pos = strpos($v, '%5B%5D')) {
                            $v = substr($v, 0, $pos);
                            $var[$v][] = $paths[$k + 1];
                        } else {

                            $var[$v] = $paths[$k + 1];
                        }
                    }
                }
                unset($paths);
            }
//            xmp($var);
// 合并的到GET数组中，方便全局调用
            $_GET = array_merge($_GET, $var);

// 合并参数
            if (isset($queryArr))
                $_GET = array_merge($_GET, $queryArr);


// 匹配url中剩余的参数
//            preg_replace('/(\w+)[\/|_|-]([^,\/]+)/e', '$tempArr[\'\\1\']=\'\\2\'', implode('/', $regxArr));
//
//            unset($tempArr[$var ['moduleName']]);
//
//            if (!empty($tempArr))
//                $_GET = array_merge($_GET, $tempArr);


            $_GET ['finalUrl'] = urldecode($url);


            foreach ($_GET as $k => $v) {

                if (!is_array($v)) {
                    $_GET[$k] = urldecode($v);
                }
            }
// 保证$_REQUEST 也能访问
            $_REQUEST = array_merge($_GET, $_POST);
// 结果
//            print_r($_GET);

            return true;
        }
        return $match;
    }


}

/**
 * 系统控制器基类
 *
 * @author zhaocj
 *
 */
class AController extends ABaseController
{

    protected $lifeTime = 0; // 本页面缓存的时间分钟数
    protected $model = null; // 本Controller对应的Model
    protected $authValue = null; //
    protected $importLocation = 'inside'; // 调用系统接口位置
// enum('inside'：内部调用，'outside':外部调用);
    protected $templateFile; // 本URL对应的HTML模板文件路径
    protected $data; // 本页面对应的路径
    protected $breadCrumbs = array(); // 面包屑内容
    protected $cssFile = array(); // 网页的CSS文件
    protected $jsFileBefore = array(); // 网页前边的JS文件
    protected $jsFileAfter = array(); // 网页后边的JS文件
    protected $jsStr;
    protected $params = null;
    protected static $urlManager;
    protected $permitAllModule;
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

        /* 路由模式start* */
        self::$urlManager = App:: base()->urlManager;
        $this->before();
        /* 路由模式end* */
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
    protected function before()
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
    protected function after()
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
     * 检查模块和action权限
     */

    protected function checkModule()
    {
        if (NEEDAUTH === false) {
            return true;
        }
        $m = strtolower($this->module);
        $a = strtolower($this->action);
        $module = array();
        if (empty($this->permitAllModule)) {
            return false;
        }
        foreach ($this->permitAllModule as $key => $value) {
            $module[strtolower($key)] = $value;
        }
        $actionArr = $module [$m];

        if ($actionArr && is_array($actionArr)) {
            if (in_array('*', $actionArr)) {
                return true;
            }
            if (in_array($a, $actionArr)) {
                return true;
            }
        }
        return false;
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
     * 抓取和提交数据,如果就加密验证失败，则再请求一次
     * @param
     *            target    调用的m和a参数
     * @param
     *            gets    url中的其他get参数
     * @param
     *            posts url中的post参数
     */
    protected function httpConnection($target, $gets, $posts = array())
    {
        $connectUrl = App:: base()->importApi [$target];

        if (empty($connectUrl)) {
            exit("你请求的URL地址：{$target}错误!");
        }

        $data = $this->_grabimport($connectUrl, $gets, $posts);

        if ($data ['error'] == '2001') {
            ABaseApplication::setSession('session_code', $data ['session_id']);
            return $this->_grabimport($connectUrl, $gets, $posts);
        }
        return $data;
    }

    /**
     * 调用接口工具方法
     * @param String $moduleAction
     * @param String $action
     * @param Array|null $gets
     * @param Array|null $posts
     * @return String
     */
    protected function httpConnectionByBase($moduleAction, $gets = array(), $posts = array())
    {
        return $this->httpConnectionByUrl($this->createUrl($moduleAction, $gets, App::base()->params['domain']['client']), $posts);
    }

    /**
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @param
     *            String
     *            target    调用的m和a参数
     * @param
     *            gets    url中的其他get参数
     * @param
     *            posts url中的post参数
     */
    protected function httpConnectionByUrl($connectUrl, $gets, $posts = array())
    {

        if (empty($connectUrl)) {
            throw new Exception("the param what you give \$connectUrl is null!");
        }

        $data = $this->_grabimport($connectUrl, $gets, $posts);

        if (isset($data ['code']) && $data ['code'] == '100') {
            App::setSession('accessToken', $data['data']);
            //    $_SESSION ['session_code'] = $data ['session_id'];
            $data = $this->_grabimport($connectUrl, $gets, $posts);
            return $data;
        }
        if ($data ['code'] == '200') {
            return $data;
        }
        throw new Exception("Error Description:the client return is wrong!" . PHP_EOL . PHP_EOL . PHP_EOL .
            'URL:' . $connectUrl . PHP_EOL . PHP_EOL .
            (empty($posts) ? '' : '$_POST:' . var_export($posts, true) . PHP_EOL . PHP_EOL) .
            (empty($gets) ? '' : '$_GET:' . var_export($gets, true) . PHP_EOL . PHP_EOL) .
            'return data:' . var_export($data, true));
    }

    /**
     * 获取远程端口的值
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @param String $target
     * @param Array $gets
     * @param Array $posts
     * @return mixed
     */
    private function _grabimport($target_url, $gets, $posts = array(), $headers = array())
    {


        // 处理get参数
        foreach ($gets as $k => $v) {
            $target_url .= "&$k=$v";
        }

        $posts['accessToken'] = App::getSession('accessToken');
        // 加入加密code
        // debug($target_url.'<br />');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target_url);

        curl_setopt($ch, CURLOPT_HEADER, 0);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        // 模拟浏览器cookie，提交session_id,不能url rewrite
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
        $data = curl_exec($ch);

        curl_close($ch);

        $result = json_decode($data, true);
        if (empty($result)) {
            return $data;
        }
        return $result;
    }

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

    public
    function setLoadData($data = array())
    {
        $this->loadData = $data;
    }

    public
    function getAppTemplatePath()
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
    public
    function loadViewCell($template, $isCache = false, $name = '', $lifeTime = 0)
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
    protected
    function rules()
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
                    $outHtml .= "<script type=\"text/javascript\" src=\"{$url}js/{$file}?v=" . App::base()->version . "\"></script>\n";
                } elseif ($type == 'css') {
                    $outHtml .= "<link href=\"{$url}css/{$file}?v=" . App::base()->version . "\" rel=\"stylesheet\" type=\"text/css\">\n";
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
                    return "<script type=\"text/javascript\" src=\"{$return}?v=" . App::base()->version . "\"></script>\n";
                case 'css':
                    return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$return}?v=" . App::base()->version . "\">\n";
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
        if (empty($domain)) {
            $domain = AUtils::baseUrl();
        }
        if (empty($moduleActionArray[0]) && empty($moduleActionArray[1])) {
            unset($moduleActionArray[0], $moduleActionArray[1]);
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
        if (empty(self::$urlManager)) {
            self::$urlManager = App::base()->getInstance('urlManager');
            if (!is_a(self::$urlManager, 'AUrlManager')) {
                throw new RuntimeException("The flow class is not the  AUrlManager or it's sub class." . PHP_EOL . var_export(self::$urlManager
                        , true), FRAME_THROW_EXCEPTION);
            }
        }

        self::$urlManager->setCreateUrlParams($moduleAction, $params, $domain);

        return self::$urlManager->createURLPath();
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
    public
    function getLoginUrl($domain = '')
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

    public
    function parse_script($urls, $path = "")
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
    protected
    function getRequestParams()
    {
        $requestParams = array_merge($_GET, $_POST);
        unset($requestParams['r'], $requestParams['actionName'], $requestParams['actionName'], $requestParams['finalUrl']);
        return $requestParams;
    }

}

<?php

function js_alert($msg)
{
    echo "<script>alert('{$msg}');</script>";
}

function js_console($msg)
{
    echo "<script>console.log('{$msg}');</script>";
}

/**
 * 实例化一个数据模型
 * //改：一般实例化一个数据模型，只需要模块名称，故我把$module参数提前,在模型中我们定义好$linkName,如果分库，则可以加上$linkName
 * @param string $module 模块名称
 * @param  $linkName
 * @return ADatabaseMysql
 */
function M($module = null, $linkName = '')
{
    if (empty($module)) {
        return new AModel($linkName);
    } else {

        $modelName = "{$module}Model";
        return new $modelName($linkName);
    }
}

/*
 * 实例化AModel
 * M方法 普遍只填写模块名称，如果单纯的只要数据库操作 就要M('','xxxx');故写个D方法
 */

function D($linkName = '')
{
    return new AModel($linkName);
}

/**
 * 实例化一个控制器
 * @param  $module
 * @param  $action
 * @return AController
 */
function C($module = '', $action = '', $params = array(), $needExit = true)
{
    $_POST = $params;
    $_GET = array();
    //获得当前请求动作是什么
    $app = ABaseApplication::getInstance();
    global $config;
    $app->config = $config;
    $app->run($module, $action);
    if ($needExit === true) {
        exit;
    } else {
        unset($app);
        return;
    }
}

/**
 * 创建一个文件夹
 * @param  $dirname -String
 * @return boolean
 */
function mkdirByOs($dirname)
{
    if (empty($dirname)) {
        throw new Exception("生成的日志文件名不能为空！");
        exit;
    }
    $dirname = dirname($dirname);
    if (!file_exists($dirname)) {
        if (PHP_OS === 'WINNT') {
            mkdir($dirname, 0777);
        } else {
            shell_exec("mkdir -p {$dirname}");
        }
    }
    if (!file_exists($dirname)) {
        //AApplication::error("文件夹创建{$dirname}不成功！");
        return false;
    } else {

        return true;
    }
}

/**
 * 跳转和alert message
 */
function message($msg)
{
    echo '	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                            </head>
                            <body>
                                    <script type="text/javascript" charset="utf-8">alert("' . $msg . '")</script>
                            </body>
                    </html> ';
}


function redirectIframe($href)
{
    echo "<script type='text/javascript'>parent.location='$href'</script>";
    exit;
}

/**
 * 获得网站的URL地址
 *
 * @return string
 */
function baseUrl()
{
    return getDomain() . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
}

/**
 * 获得当前的域名
 *
 * @return string
 */
function getDomain()
{
    /* 协议 */
    $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';

    /* 域名或IP地址 */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } else {
        /* 端口 */
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = ':' . $_SERVER['SERVER_PORT'];

            if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
                $port = '';
            }
        } else {
            $port = '';
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'] . $port;
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }

    return $protocol . $host;
}

if (!function_exists('checkUrl')) {

    function checkUrl($weburl)
    {
        return preg_match("/^http|https:\/\/[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)$/i", $weburl);
    }

}
/*
 * 生成系统可用的链接
 */
if (!function_exists('createUrl')) {

    function createUrl($controllerAction, $params = array(), $domain = '')
    {

        $route = new AController;
        return $route->createUrl($controllerAction, $params, $domain);
    }

}

/**
 *
 * @param String $string
 * @param Integer $cutlength
 * @return String
 */
function cutStringUtf8($string, $cutlength, $suffix = '')
{
    $returnstr = '';
    $i = $n = $word_length = 0;
    $str_length = strlen($string); //字符串的字节数
    while (($i <= $str_length)) {
        $word_length += 1;
        $temp_str = substr($string, $i, 1);
        $ascnum = Ord($temp_str); //得到字符串中第$i位字符的ascii码
        if ($ascnum >= 224) {//如果ASCII位高与224，
            $n < $cutlength ? $returnstr .= substr($string, $i, 3) : ''; //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i = $i + 3; //实际Byte计为3
            $n++; //字串长度计1
            continue;
        }
        if ($ascnum >= 192) {//如果ASCII位高与192，
            $n < $cutlength ? $returnstr .= substr($string, $i, 2) : ''; //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i = $i + 2; //实际Byte计为2
            $n++; //字串长度计1
            continue;
        }
        if ($ascnum >= 65 && $ascnum <= 90) { //如果是大写字母，
            $n < $cutlength ? $returnstr .= substr($string, $i, 1) : '';
            $i = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，大写字母计成一个高位字符
            continue;
        } else {//其他情况下，包括小写字母和半角标点符号，
            $n < $cutlength ? $returnstr .= substr($string, $i, 1) : '';
            $i = $i + 1; //实际的Byte数计1个
            $n = $n + 0.5; //小写字母和半角标点等与半个高位字符宽...
        }
    }
    $word_length = $word_length - 1;
    return $word_length > $cutlength ? "{$returnstr}{$suffix}" : $returnstr;
}

/*
 * 当前url
 */

function currentUrl()
{
    // global $baseurl;
    if (empty($baseurl)) {
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
        $baseurl = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://') . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
    }
    return $baseurl;
}

/*
 * 多个分隔符分割字符串
 * @param array $delimiters 分割符
 * @param string $string 要分割的字符串
 */

function multipleExplode($delimiters = array(), $string = '')
{
    $mainDelim = $delimiters[count($delimiters) - 1]; // dernier

    array_pop($delimiters);


    foreach ($delimiters as $delimiter) {
        $string = str_replace($delimiter, $mainDelim, $string);
    }

    $result = explode($mainDelim, $string);

    return $result;
}

/**
 * 客户端返回格式严格修正
 */
function formatData($data)
{
    if (!isset($data['status']) || !$data['status'])
        $data['status'] = false;
    if (!isset($data['message']) || !$data['message'])
        $data['message'] = '';
    if (!isset($data['error']) || !$data['error'])
        $data['error'] = '';
    if (!isset($data['data']) || !$data['data'])
        $data['data'] = array();
    return $data;
}

/**
 * 字符串特殊字符转义函数
 * Enter description here ...
 * @param unknown_type $string
 */
function maddslashes(&$string, $quote = false)
{
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    if (!MAGIC_QUOTES_GPC) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = maddslashes($val);
            }
        } else {
            if (!$quote)
                $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
            else
                $string = htmlentities($string, ENT_COMPAT, 'UTF-8');
            //$string = addslashes($string);
        }
    }
    return $string;
}

/**
 * 获得二维数组的一个键的值，并返回相应的结果
 *
 * @param $key -
 *            String 二维数组的一条数据中的一个键的名称 如:array(array('test'=>'')) 中的test
 * @param $array -
 *            二维数组 如：array(array('test'=>''))
 * @param $type -
 *            Enum ('Array','String') 设置返回结果的格式默认一维数组
 */
function getOneDimensionalarraysValue($key, $array, $type = 'Array')
{
    $result = null;
    $isArray = is_array($key);
    if ($isArray !== true)
        $key = array(
            $key
        );
    switch ($type) {
        case 'array':
            foreach ($key as $n)
                $result[$n] = array();
            if ($array)
                foreach ($array as $k => $value) {
                    foreach ($key as $ke => $v) {
                        if (empty($value[$v]) || (!empty($result[$key]) && in_array($value[$v], $result[$v])))
                            continue; // 去掉空的 // 去掉重复的数据
// $arrayTemp [] = $value [$v];
                        $result[$v][] = $value[$v];
                    }
                }
            break;
        case 'String':
            $arrayTemp = array();
            // 初始化结果集
            foreach ($key as $n)
                $result[$n] = '';
            if ($array)
                foreach ($array as $k => $value) {
                    foreach ($key as $ke => $v) {
                        if (empty($value[$v]) || (!empty($arrayTemp[$v]) && in_array($value[$v], $arrayTemp[$v])))
                            continue;
                        $arrayTemp[$v][] = $value[$v];
                        $result[$v] .= empty($result[$v]) ? "{$value[$v]}" : ",{$value[$v]}";
                    }
                }
            break;
        default:
            throw new Exception(
                'The method getOneDimensionalarraysValue what you give \$type is Wrong!');
        //   break ;
    }
    return $result;
}

/**
 *
 * @param $primaryArray -
 *            Array 主要的数据源，
 * @param $primaryArraykey -
 *            String 主数据源中的一条数据的某个键值
 * @param $accessoryArray -
 *            Array 从数据源
 * @param
 *            $accessoryArrayKey-从数据源中的一个数据
 * @return multitype:
 */
function joinArrayByKey($primaryArray, $primaryArraykey, $accessoryArray,
                        $accessoryArrayKey)
{
    $temp = array();
    $defaultArray = array();
    if ($accessoryArray) {
        foreach ($accessoryArray as $key => $value)
            $temp[$value[$accessoryArrayKey]] = $value;
        $tmp = array_pop($accessoryArray);
        foreach ($tmp as $key => $value) {
            $defaultArray[$key] = null;
        }
    }
    $result = array();
    if ($primaryArray) {
        foreach ($primaryArray as $key => $value) {
            if (!isset($temp[$value[$primaryArraykey]]))
                $temp[$value[$primaryArraykey]] = $defaultArray;
            $primaryArray[$key] = array_merge($primaryArray[$key], $temp[$value[$primaryArraykey]]);
        }
    }
    return $primaryArray;
}

//获得真实ip
function getRealIp()
{
    $ip = false;
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = FALSE;
        }
        for ($i = 0; $i < count($ips); $i++) {
            if (!eregi("^(10|172\.16|192\.168)\.", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

/**
 * 发送一个json响应.
 *
 * @param mixed $data json数据
 * @param int $code HTTP 状态码
 * @param bool $encode 是否要json序列化
 */
function json($data, $code = 200, $encode = true)
{
    $json = ($encode) ? json_encode($data) : $data;
    $response = new Response(false);
    $response
        ->status($code)
        ->header('Content-Type', 'application/json')
        ->write($json)
        ->send();
}

/**
 * 写文件日志
 * @param type $file
 * @param type $msg
 */
function infoLog($file, $msg)
{
    require_once DIR_FRAMEWORK . 'lib/log4php/main/php/Logger.php';
    Logger::configure(array(
        'appenders' => array(
            'default' => array(
                'class' => 'LoggerAppenderDailyFile',
                'layout' => array(
                    'class' => 'LoggerLayoutTTCC',
                ),
                'params' => array(
                    'datePattern' => 'Y-m-d',
                    'file' => App::base()->basePath . '/log/' . $file . '-%s.log',
                ),
            ),
        ),
        'rootLogger' => array(
            'appenders' => array(
                'default'),
        ),
    ));
    $logger = Logger::getRootLogger();
    $logger->debug($msg);
}

function debugLog($file, $msg)
{
    //if (IS_DEPLOY)return;
    require_once dirname(__FILE__) . '/log4php/main/php/Logger.php';
    Logger::configure(array(
        'appenders' => array(
            'default' => array(
                'class' => 'LoggerAppenderDailyFile',
                'layout' => array(
                    'class' => 'LoggerLayoutTTCC',
                ),
                'params' => array(
                    'datePattern' => 'Y-m-d',
                    'file' => ROOT_DIR . 'data/' . $file . '-%s.log',
                ),
            ),
        ),
        'rootLogger' => array(
            'appenders' => array(
                'default'),
        ),
    ));
    $logger = Logger::getRootLogger();
    $logger->debug($msg);
}

function debug($var)
{
    if (IS_DEBUG) {
        //echo "<br />\n";
        if (is_array($var) || is_object($var))
            var_dump($var);
        else
            echo $var;
        echo "<br />\n";
        //ob_flush();
        flush();
    }
}

/**
 * 发送一个JSONP响应
 *
 * @param mixed $data json数据
 * @param string $param CallBack 名字
 * @param int $code HTTP 状态码
 * @param bool $encode 是否要json序列化
 */
function jsonp($data, $param = 'jsonp', $code = 200, $encode = true)
{
    $json = ($encode) ? json_encode($data) : $data;
    $request = new Request();
    $response = new Response(false);
    $callback = $request->query[$param];

    $response
        ->status($code)
        ->header('Content-Type', 'application/javascript')
        ->write($callback . '(' . $json . ');')
        ->send();
}

/**
 * 设定cookie
 */
if (!function_exists('set_cookie')) {

    function set_cookie($name = '', $value = '', $expire = '', $domain = '',
                        $path = '/', $prefix = '', $secure = FALSE)
    {
        $request = new Request();
        $request->set_cookie($name, $value, $expire, $domain, $path, $prefix, $secure);
    }

}

/**
 * get一个cookie的值
 */
if (!function_exists('get_cookie')) {

    function get_cookie($index = '')
    {
        $request = new Request();
        $cookie = App::base()->cookie;
        $prefix = '';

        if (!isset($request->cookies[$index]) && $cookie['prefix'] != '') {
            $prefix = $cookie['prefix'];
        }

        return $request->cookies[$prefix . $index];
    }

}


/**
 * 删除cookie
 */
if (!function_exists('delete_cookie')) {

    function delete_cookie($name = '', $domain = '', $path = '/', $prefix = '')
    {
        set_cookie($name, '', '', $domain, $path, $prefix);
    }

}
//获取树
if (!function_exists('getTree')) {

    function getTree($categorys)
    {
        $id = 0;
        $level = 0;
        $categoryObjs = array();
        $tree = array();
        $childrenNodes = array();
        foreach ($categorys as $cate) {
            $obj = new stdClass();
            $obj->root = $cate;
            $id = $cate['id'];
            $level = $cate['parent_id'];
            $obj->children = array();
            $categoryObjs[$id] = $obj;
            if ($level) {
                $childrenNodes[] = $obj;
            } else {
                $tree[] = $obj;
            }
        }

        foreach ($childrenNodes as $node) {
            $cate = $node->root;
            $id = $cate['id'];
            $level = $cate['parent_id'];
            $categoryObjs[$level]->children[] = $node;
        }

        return $tree;
    }

}
//二维数组排序
if (!function_exists('multi_array_sort')) {

    function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
    {
        if (is_array($multi_array)) {
            foreach ($multi_array as $row_array) {
                if (is_array($row_array)) {
                    $key_array[] = $row_array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_array, $sort, $multi_array);
        return $multi_array;
    }

}
if (!function_exists('page_array')) {

    /**
     * 数组分页函数  核心函数  array_slice
     * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中
     * $count   每页多少条数据
     * $page   当前第几页
     * $array   查询出来的所有数组
     * order 0 - 不变     1- 反序
     */
    function page_array($count, $page, $array, $order)
    {
        global $countpage; #定全局变量
        $page = (empty($page)) ? '1' : $page; #判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; #计算每次分页的开始位置
        if ($order == 1) {
            $array = array_reverse($array);
        }
        $totals = count($array);
        $countpage = ceil($totals / $count); #计算总页面数
        $pagedata = array();
        $pagedata = array_slice($array, $start, $count);
        return $pagedata;  #返回查询数据
    }

}
?>

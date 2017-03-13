<?php

namespace framework\bin;

use framework\App;
use RuntimeException;

/**
 *
 * @author zhaocj
 */
class ABaseController extends AppBase
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

            //已经没有子节点了，将标签闭合 (事实上也可以考虑直接删除掉空的节点)
            //对非配对标签，比如<hr/> <br/> <img/>等 直接以 />闭合之
            $s .= ($node->type == TIDY_NODETYPE_START) ? '></' . $node->name . '>' : '/>';

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


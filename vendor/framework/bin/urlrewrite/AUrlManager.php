<?php
/**
 * Created by PhpStorm.
 * User: karl.zhao
 * Date: 2017/3/6
 * Time: 16:45
 */

namespace framework\bin\urlrewrite;

use framework\bin\base\AppBase;
use framework\bin\base\AController;

//use framework\bin\utils\AUtils;

class AUrlManager extends AppBase
{
    protected $rewriteMod = false;//是否重写
    protected $extendFile = '.html';//扩展名
    protected $domain = ''; //域名前缀,默认当前模块域名
    protected $moduleAction = '';
    protected $otherParams = array();
    protected $noReWrite = false;
    protected $routeRule;//路由规则
    protected $actionParamsSeparator = '/';//r和 实际参数之间分隔符;
    protected $delimiterModuleAction = '_';
    protected $delimiter = '_'; //详细参数间的分割符

    public static function getInstance()
    {
        return new self();
    }

    /**
     * @return boolean
     */
    public function isRewriteMod()
    {
        return $this->rewriteMod;
    }

    /**
     * @param boolean $rewriteMod
     */
    public function setRewriteMod($rewriteMod)
    {
        $this->rewriteMod = $rewriteMod;
    }


    /**
     * 获得模块的路由
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     *
     */
    public function _initModuleAction()
    {
        // 获得当前请求动作是什么
        $controller = new AController();

        //如果是URL重写的请求
        if ($this->rewriteMod) {
            $dirScriptName = dirname($_SERVER['SCRIPT_NAME']);

            $baseName = ltrim(substr($_SERVER['REQUEST_URI'], strlen($dirScriptName)), '/');

            if (stripos($baseName, '?') !== false) {
                $baseName = substr($baseName, 0, stripos($baseName, '?'));
            }

            $moduleActionLength = strlen($baseName);
            $actionParamsSeparatorLocate = stripos($baseName, $this->actionParamsSeparator);

            //判断 basename ="passport_login/t_123123";的情况处理
            if (false !== $actionParamsSeparatorLocate) {
                $this->moduleAction =
                    substr($baseName, 0, $actionParamsSeparatorLocate);//$moduleActionLength - strlen($this->extendFile));
            } else {
                $this->moduleAction = substr($baseName, 0, stripos($baseName, $this->extendFile));
            }

            if ($this->moduleAction === $baseName) {//如果是php的请求
                $this->moduleAction = $controller->getInput('r');
            }
        } else {
            $this->moduleAction = $controller->getInput('r');
        }
    }

    /**
     * @param $moduleAction
     * @param $params
     * @param $domain
     */
    public function setCreateUrlParams($moduleAction, $params, $domain)
    {
        $this->setDomain($domain);
        $this->setOtherParams($params);
        $this->setModuleAction($moduleAction);

    }

    public function setOtherParams($otherParams)
    {
        $this->otherParams = $otherParams;
    }

    /**
     * @return string
     */
    public function getExtendFile()
    {
        return $this->extendFile;
    }

    /**
     * @param string $extendFile
     */
    public function setExtendFile($extendFile)
    {
        $this->extendFile = $extendFile;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    public function createURLPath()
    {

        // 如果没有启rewrite模式
        if (!$this->rewriteMod || $this->noReWrite) {

            return $this->_notRewrite();
        }
        return $this->_reWrite();
    }

    /**
     * 如果是重写模式
     * @return string
     */
    private function _reWrite()
    {
        //xmp($domain);
        // 如果开启了rewrite模式
        $ruleStr = $this->moduleAction;

        $urlStr = empty($moduleAction) ? $this->domain : "{$this->domain}/" . $moduleAction; // 取消urlencode

        // apache下打不开
        if (!is_array($this->otherParams)) {
            return $urlStr;
        }

        $urlStr .= '/' . $this->moduleAction . $this->actionParamsSeparator;
        $ruleStr .= '/';

        foreach ($this->otherParams as $k => $v) {
            if ($v === '') {
                continue;
            }

            // 处理特殊字符
            if (!is_array($v)) {
                $urlStr .= $k . $this->delimiter . urlencode($v) . $this->delimiter;
                $ruleStr .= $k . $this->delimiter . urlencode($v) . $this->delimiter;
                continue;
            } elseif ($v ['url']) {
                empty($v ['doType']) ? $v ['doType'] = 'base64_encode' : '';
                $urlStr .= $k . $this->delimiter . urlencode($v ['doType']($v ['url'])) . $this->delimiter;
                $ruleStr .= $k . $this->delimiter . urlencode($v ['doType']($v ['url'])) . $this->delimiter;
                continue;
            }

            foreach ($v as $v1) {
                $urlStr .= $k . '[]' . $this->delimiter . $v1 . $this->delimiter;
                $ruleStr .= $k . '[]' . $this->delimiter . $v1 . $this->delimiter;
            }
        }
        $urlStr = substr($urlStr, 0, -1);
        $ruleStr = substr($ruleStr, 0, -1);

        foreach ((array)$this->routeRule as $key => $value) {
            if ($this->parseUrlRuleRewrite($key, $value, $ruleStr)) {
                break;
            }
        }
        //如果需要写$this->extendFile参数
        if ($this->rewriteUrl !== $this->moduleAction) {
            $urlStr .=
                (empty($this->moduleAction) ?
                    '' :
                    $this->extendFile);
        }

        return $urlStr;
    }

    /**
     * 如果没有开启重写模式
     * @return string
     */
    private function _notRewrite()
    {
        $urlStr = empty($this->moduleAction) ? $this->domain
            : "{$this->domain}/index.php?r=" . urlencode($this->moduleAction);

        if (!is_array($this->otherParams)) {
            return $urlStr;
        }
        foreach ($this->params as $k => $v) {
            $urlStr .= "&{$k}=" . urlencode($v);
        }
        return $urlStr;
    }


    /**
     * 获取路由路径
     *
     * @return Ambigous <string, multitype:>
     */
    public function getRoute($moduleAction)
    {
        $default = $defaultModuleAction = self::getDefaultModuleAction();
        if (!empty($moduleAction)) {
            $default = $moduleAction;
        }
        $temp = explode($this->delimiterModuleAction, $default);

        $count = count($temp);
        $result = array();
        switch ($count) {
            case 0:
                throw new RuntimeException("the program is error on creating Path!  the Error is at line:" .
                    __LINE__ . ', in file:' . __FILE__, FRAME_THROW_EXCEPTION);
            case 1:
                list($result [1], $result [2]) = explode($this->delimiterModuleAction, $defaultModuleAction);
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
     *
     * @param  string $controller
     * @param string $method
     * @param string $module
     * @return string
     */
    public function createUrlModuleAction($controller, $method, $module = '')
    {
        $moduleAction = '';
        if (!empty($module)) {
            $moduleAction .= "{$module}_";
        }
        $moduleAction .= "{$controller}_{$method}";
        return $moduleAction;
    }

    public function getDefaultModuleAction()
    {
        return $this->createUrlModuleAction('Site', 'index');
    }

    /**
     *
     * @param String $module
     * @param String $action
     * @return string
     */
    public static function getRouteModuleAction($module, $action = 'index')
    {
        return (empty($module) && empty($action)) ? '' : "{$module}/{$action}";
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
     * 按照URL重写规则 重写
     * @param $rule
     * @param $route
     * @param $regx
     * @return bool
     */
    public function parseUrlRuleRewrite($rule, $route, $regx)
    {

        $delimiter = $this->delimiter;

        // 把路由规则和地址,分割到数组中，然后逐项匹配
        $ruleArr = multipleExplode(array(
            '/',
            '_',
            '-'), $rule);


        $regxArr = explode($delimiter, $regx);
        $routeArr = explode($delimiter, $route);
        $newRegArr = $regxArr;
        if (FALSE !== strpos($route, ':')) {
            foreach ($routeArr as $key => $value) {

                if (!in_array($value, $ruleArr) && strpos($value, ':') === FALSE) {
                    unset($newRegArr[$key]);
                }
            }
            $newRegArr = array_values($newRegArr);
            foreach ($ruleArr as $key => $value) {
                if (!in_array($value, $newRegArr) && strpos($value, ':') === FALSE) {
                    unset($ruleArr[$key]);
                }
            }
        } else {
            if (strcasecmp($regx, $route) == 0) {
                $match = true;
                $this->rewriteUrl = $rule;
            }
            return $match;
        }


        $a1 = array(
            "/(:\d+)/",
            "/\//"
        );
        $a2 = array(
            ".+",
            "\/"
        );
        $rulex = preg_replace($a1, $a2, $route);
        $rulex = "/^{$rulex}$/i";


        if (!preg_match($rulex, $regx)) {

            $match = false;
            return $match;
        }


        $match = true;
// 匹配检测
        $omg = array();
        if (count($ruleArr) > count($newRegArr)) {
            $match = false;
        } else {

            foreach ($ruleArr as $key => $value) {

                if (strpos($value, ':') === 0) {


                    if (FALSE !== strpos($value, '\\d')) {
                        $value = str_replace("\\d", "", $value);
                        $rule = str_replace("\\d", "", $rule);
                    }

                    if (FALSE !== strpos($value, '\\w')) {
                        $value = str_replace("\\w", "", $value);
                        $rule = str_replace("\\w", "", $rule);
                    }


                    if (FALSE !== strpos($value, '\\AZ')) {
                        $value = str_replace("\\AZ", "", $value);
                        $rule = str_replace("\\AZ", "", $rule);
                    }


                    if (FALSE !== strpos($value, '\\AD')) {
                        $value = str_replace("\\AD", "", $value);
                        $rule = str_replace("\\AD", "", $rule);
                    }

                    if (FALSE !== strpos($value, '\\BD')) {
                        $value = str_replace("\\BD", "", $value);
                        $rule = str_replace("\\BD", "", $rule);
                    }


                    if (FALSE !== strpos($value, '\\CD')) {
                        $value = str_replace("\\CD", "", $value);
                        $rule = str_replace("\\CD", "", $rule);
                    }


                    $omg[$key] = '/' . $value . '/';

                    if ($pos = strpos($value, '^')) {//排除
                        $mk = substr($value, 0, $pos);
                        $omg[$key] = '/' . $mk . '/';
                        $rule = str_replace($value, $mk, $rule);
                        $stripArr = explode('|', trim(strstr($value, '^'), '^'));
                        if (!in_array($newRegArr[$key], $stripArr)) {
                            $match = false;
                            break;
                        }
                    }
                    if ($pos = strpos($value, '.')) {
                        $omg[$key] = '/' . substr($value, 0, $pos) . '/';
                    }

                    if (($pos = strpos($value, "\\d"))) {

                        if (!preg_match("/(\d+)$/i", $newRegArr[$key])) {
                            $match = false;
                            break;
                        }

                        $mk = substr($value, 0, $pos);

                        $rule = str_replace('\\d', '', $rule);
                        $omg[$key] = '/' . $mk . '/';
                    }

                    // 静态项不区分大小写
                } elseif (strcasecmp($value, $newRegArr[$key]) !== 0) {
                    $match = false;
                    break;
                }
            }
        }
        // 匹配成功

        if ($match) {

            foreach ($routeArr as $key => $value) {
                if (strpos($value, ':') === FALSE) {
                    unset($regxArr[$key]);
                }
            }

            $this->rewriteUrl = preg_replace(array_values($omg), array_values($regxArr), $rule);
        }

        return $match;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain = '')
    {
        $this->domain = (empty($domain)) ? baseUrl() : $domain;
    }

    /**
     * @return string
     */
    public function getModuleAction()
    {
        return $this->moduleAction;
    }

    /**
     * @param string $moduleAction
     */
    public function setModuleAction($moduleAction)
    {
        $this->moduleAction = $moduleAction;
    }

    /**
     * @return array
     */
    public function getOtherParams()
    {
        return $this->otherParams;
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: karl.zhao
 * Date: 2017/3/6
 * Time: 16:45
 */

namespace framework\bin;


class AUrlManager
{
    var $rewriteMod = false;//是否重写
    var $extendFile = '.html';
    var $domain = '';
    var $moduleAction = '';
    var $params = array();
    var $noReWrite = false;
    var $routeRule;//路由规则

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
     * @param $moduleAction
     * @param $params
     * @param $domain
     */
    public function setCreateUrlParams($moduleAction, $params, $domain)
    {
        $this->setDomain($domain);
        $this->setParams($params);
        $this->setModuleAction($moduleAction);
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
        $delimiter = $this->delimiter;
        $ruleStr = $this->moduleAction;

        $urlStr = empty($moduleAction) ? $this->domain : "{$this->domain}/" . $moduleAction; // 取消urlencode

        // apache下打不开
        if (!is_array($this->params)) {
            return $urlStr;
        }

        $urlStr .= '/';
        $ruleStr .= '/';
        foreach ($this->params as $k => $v) {
            if ($v === '') {
                continue;
            }

            // 处理特殊字符
            if (is_array($v) && $v ['url']) {
                empty($v ['doType']) ? $v ['doType'] = 'base64_encode' : '';
                $urlStr .= $k . $delimiter . urlencode($v ['doType']($v ['url'])) . $delimiter;
                //xmp($urlStr);
                $ruleStr .= $k . $delimiter . urlencode($v ['doType']($v ['url'])) . $delimiter;
                continue;
            }
            if (!is_array($v)) {
                $urlStr .= $k . $delimiter . urlencode($v) . $delimiter;
                $ruleStr .= $k . $delimiter . urlencode($v) . $delimiter;
                continue;
            }
            foreach ($v as $v1) {
                $urlStr .= $k . '[]' . $delimiter . $v1 . $delimiter;
                $ruleStr .= $k . '[]' . $delimiter . $v1 . $delimiter;
            }
        }

        $urlStr = substr($urlStr, 0, -1);
        $ruleStr = substr($ruleStr, 0, -1);
        foreach ((array)$this->routeRule as $key => $value) {
            if ($this->parseUrlRuleRewrite($key, $value, $ruleStr)) {
                break;
            }
        }
        if ($this->rewriteUrl !== $this->moduleAction) {
            $urlStr = $this->domain . $this->delimiterModuleAction . $this->rewriteUrl .'/'. $this->moduleAction . $this->extendFile;
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

        if (!is_array($this->params)) {
            return $urlStr;
        }
        foreach ($this->params as $k => $v) {
            $urlStr .= "&{$k}=" . urlencode($v);
        }
        return $urlStr;
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
//            echo $rule;
//            xmp($omg);
//            xmp($regxArr);
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
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }


}
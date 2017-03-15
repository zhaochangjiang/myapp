<?php
/**
 * CURL操作类
 * Created by PhpStorm.
 * @Author: karl.zhao<zhaocj2009@hotmail.com>
 * @Date: 2017/3/15
 * @Time: 21:59
 */
namespace framework\bin\http;

use RuntimeException;

class ACurlManager
{

    /**
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @param String $connectUrl 调用的m和a参数
     * @param $gets     url中的其他get参数
     * @param  array $posts url中的post参数
     * @return mixed * *
     * @throws Exception
     */
    public function httpConnectionByUrl($connectUrl, $gets, $posts = array())
    {
        if (empty($connectUrl)) {
            throw new Exception("the param what you give \$connectUrl is null!");
        }
        $data = $this->_grabimport($connectUrl, $gets, $posts);

        if (isset($data ['code']) && $data ['code'] == '100') {
            App::setSession('accessToken', $data['data']);
            $data = $this->_grabimport($connectUrl, $gets, $posts);
            return $data;
        }
        if ($data ['code'] == '200') {
            return $data;
        }

        throw new RuntimeException("Error Description:the client return is wrong!" . PHP_EOL . PHP_EOL . PHP_EOL .
            'URL:' . $connectUrl . PHP_EOL . PHP_EOL .
            (empty($posts) ? '' : '$_POST:' . var_export($posts, true) . PHP_EOL . PHP_EOL) .
            (empty($gets) ? '' : '$_GET:' . var_export($gets, true) . PHP_EOL . PHP_EOL) .
            'return data:' . var_export($data, true), FRAME_THROW_EXCEPTION);
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

}
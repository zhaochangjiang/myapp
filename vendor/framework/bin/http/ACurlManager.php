<?php
/**
 * CURL操作类
 * Created by PhpStorm.
 * @Author: karl.zhao<zhaocj2009@hotmail.com>
 * @Date: 2017/3/15
 * @Time: 21:59
 */

namespace framework\bin\http;

use framework\bin\utils\ADesEncrypt;
use \RuntimeException;
use framework\App;

class ACurlManager
{


    /**
     * 抓取和提交数据,如果就加密验证失败，则再请求一次
     * @param string $connectUrl 调用的m和a参数
     * @param array gets    url中的其他get参数
     * @param array posts url中的post参数
     * @return  object
     */
    public function httpConnection($connectUrl, $gets, $posts = array())
    {
        if (empty($connectUrl)) {
            throw new RuntimeException("The param what you give \$connectUrl is null! the error is at line:"
                . __LINE__ . ',in file' . __FILE__, FRAME_THROW_EXCEPTION);
        }

        $data = $this->_grabImport($connectUrl, $gets, $posts);

        print_r($data);
        exit;
        if (100 == intval($data ['code'])) {

            $clientSessionId = session_id();

            //生成token
            $accessToken = ADesEncrypt::encrypt($clientSessionId);

            App::$app->session->setSession('accessToken', $accessToken);

            $data = $this->_grabImport($connectUrl, $gets, $posts);

            return $data;
        }
        if (200 == intval($data ['code'])) {
            return $data;
        }

        throw new RuntimeException("Error Description:the client return is wrong!" . PHP_EOL . PHP_EOL . PHP_EOL .
            '[URL]:' . $connectUrl . PHP_EOL . PHP_EOL .
            (empty($posts) ? '' : '[$_POST]:' . var_export($posts, true) . PHP_EOL . PHP_EOL) .
            (empty($gets) ? '' : '[$_GET]:' . var_export($gets, true) . PHP_EOL . PHP_EOL) .
            '[return]:' . PHP_EOL . var_export($data, true), FRAME_THROW_EXCEPTION);
    }

    /**
     * 获取远程端口的值
     * @author Karl.zhao <zhaocj2009@126.com>
     * @since 2016/09/20
     * @param String $targetUrl
     * @param array $gets
     * @param array $posts
     * @param  array $headers
     * @return mixed
     */
    private function _grabImport($targetUrl, $gets, $posts = array(), $headers = array())
    {

        // 处理get参数
        foreach ($gets as $k => $v) {
            $targetUrl .= "&$k=$v";
        }
        $accessToken = App::$app->session->getSession('accessToken');
        if (!empty($accessToken)) {
            $posts['accessToken'] = $accessToken;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);
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
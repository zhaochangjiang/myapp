<?php
namespace framework\lib;
class PageRedisCacheClass extends AComponent
{

    /**
     * $lifetime : 缓存文件有效期,单位为秒
     * $cacheid : 缓存文件路径,包含文件名
     */
    public $lifeTime;
    public $hashId;
    public $id;
    private $data;
    public $redis;

    /**
     * 析构函数,检查缓存目录是否有效,默认赋值
     */
    function __construct()
    {
        $this->redis = App::base()->redis;
        $this->redis->select(2);


        //print_r($this->redis);
//        xmp($this->data);
    }

    public function init()
    {
        $this->hashId = !empty($this->id) ? 'FragmentCaching' : 'PageCaching';
        $this->id = !empty($this->id) ? 'FragmentCaching' . md5($this->id) : 'PageCaching' . $this->getcacheid();
        $this->data = $this->redis->hget($this->hashId, $this->id, true);
    }

    /**
     * 检查缓存是否有效
     */
    private function isvalid()
    {

        $data = $this->data;
        if (!$data['content'])
            return false;
        if (TIMESTAMP - $data['creattime'] > $this->lifeTime)
            return false;
        return true;
    }

    /**
     * 写入缓存
     * $mode == 0 , 以浏览器缓存的方式取得页面内容
     */
    public function endCache($mode = 0, $content = '')
    {

        switch ($mode) {
            case 0:
                $content = ob_get_contents();
                break;
            default:
                break;
        }
        ob_end_flush();
        try {
//            $nocacheMe = array();
//            if (preg_match_all("/<nocache>(.*)<\/nocache>/isU", $content, $m))
//            {
//                $nocacheMe = $m[0];
//            }
//
//            foreach ($nocacheMe as &$v)
//            {
//                $v = str_replace('</nocache>', '<\/nocache>', $v);
//                $v = "/{$v}/";
//            }
//            $content = preg_replace($nocacheMe, $nocache, $content);
//
//            xmp($nocacheMe);
            $this->redis->hset($this->hashId, $this->id, array('content' => $content, 'creattime' => TIMESTAMP), true);

            //$this->redis->expire($this->id, time() + $this->lifeTime);
        } catch (Exception $e) {
            $this->error('写入缓存失败!');
        }
    }

    /**
     * 加载缓存
     * exit() 载入缓存后终止原页面程序的执行,缓存无效则运行原页面程序生成缓存
     * ob_start() 开启浏览器缓存用于在页面结尾处取得页面内容
     */
    public function startCache()
    {

        if ($this->isvalid()) {
            echo $this->data['content'];
            return FALSE;
        } else {
            ob_start();
            return true;
        }
    }

    /**
     * 清除缓存
     */
    public function cleanCache()
    {
        try {
            //$this->redis->hdel($this->id, array('content', 'creattime'));
            $this->redis->del("PageCaching");
        } catch (Exception $e) {
            $this->error('清除缓存失败!');
        }
    }

    /**
     * 取得缓存文件路径
     */
    private function getcacheid()
    {
        return md5($_SERVER['REQUEST_URI']);
    }

    /**
     * 取得当前页面完整url
     */
    private function geturl()
    {
        $url = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        } else {
            $url = $_SERVER['Php_SELF'];
            $url .= empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING'];
        }
        return $url;
    }

    /**
     * 输出错误信息
     */
    private function error($str)
    {
        echo '<div style="color:red;">' . $str . '</div>';
    }

}

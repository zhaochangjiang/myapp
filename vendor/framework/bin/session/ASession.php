<?php
namespace framework\bin\session;

use client\common\ClientResultData;
use framework\bin\dataFormat\AReturn;
use framework\bin\base\AppBase;
use framework\bin\dataFormat\ErrorCode;
use framework\bin\http\ARequestParameter;
use framework\bin\utils\ADesEncrypt;


/**
 *
 * @author zhaocj
 *
 */
class ASession extends AppBase
{

    var $configSession;
    var $memoryStorage;
    var $model;
    var $memcachePrufix = 'SE_';


    private static $session;

    /**
     * session打开
     * @param  string $savePath
     * @param  string $sessionName
     * @return bool
     */
    function session_open($savePath, $sessionName)
    {

//        $session_model = D($this->configSession['dblink']);
//        $str_sql = " CREATE TABLE IF NOT EXISTS `user_session`( `sid` varchar(50) NOT NULL COMMENT '用户Session_ID', `expire` int(10) NOT NULL COMMENT '最近一次使用本站时间', `ip` varchar(50) NOT NULL, `data` text NOT NULL COMMENT 'Session内容', PRIMARY KEY (`sid`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//        $session_model->query($str_sql);

        return true;
    }

    /**
     * 销毁Session
     */
    public static function sessionDestroy()
    {
        session_destroy();
    }

    /**
     * 获得SESSION内容，不传值表示返回所有的Session
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @param string $key
     * @param string $key
     * @return string *
     */
    public function getSession($key = '')
    {
        $session = $_SESSION;
        return empty($key) ? $session : (isset($session[$key]) ? $session[$key] : '');
    }

    /**
     * 设置Session的内容
     *
     * @param String $key
     * @param String $value
     */
    public static function setSessionArray($sessionArray, $sessionAll = false)
    {
        if ($sessionAll === true) {
            $_SESSION = $sessionArray;
            return;
        }
        //  session_destroy();
        $_SESSION = array_merge($_SESSION, $sessionArray);
    }

    /**
     * 设置Session的内容
     * @param String $key
     * @param String
     * @return boolean
     */
    public static function setSession($key, $value)
    {
        $session = self::getSession();
        $session[$key] = $value;
        self::setSessionArray($session);

        return true;
    }

    /**
     * session关闭
     *
     * @return void
     */
    function session_close()
    {
        return true;
    }

    /**
     * 读取session
     *
     * @param
     *            String    session_id
     * @return session中存储的数据
     */
    function session_read($key)
    {
        $dataSession = $this->model->findOneSession($key);
        return $dataSession ['data'];
    }


    /**
     * 写入session
     *
     * @param
     *            String
     *            session的id
     * @param $value -
     *            保存在session的数据
     */
    function session_write($key, $value)
    {
        $feild = array(
            'sid' => $key,
            'expire' => TIMESTAMP,
            'ip' => getRealIp(),
            'data' => $value
        );
        $this->model->replaceSession($feild);
        return $value;
    }

    /**
     * 销毁session
     *
     * @param String $key
     *            -
     *            session id
     * @return void
     */
    function session_destroy($key)
    {

        $this->model->deleteSession($key);
        return true;
    }

    public function run()
    {
        $this->_init();
    }

    /**
     * 垃圾回收，销毁过期的session
     * crontab例程销毁session
     * @param  $maxLifetime
     * @return  bool
     */
    function session_gc($maxLifetime)
    {
        $this->model->deleteBatch(array(
            'expire' => array(
                'doType' => '<',
                'value' => TIMESTAMP - $maxLifetime)
        ));
        return true;
    }


    /**
     *
     */
    protected function _init()
    {

        /**
         * [S]开始session*
         */
        //$this->model = M($this->configSession ['model']);
        //$this->model->setConfigSession($this->configSession);

        //设置色session id的名字
        ini_set('session.name', empty($this->configSession ['sessionName']) ? 'sid' : $this->configSession ['sessionName']);

        //不使用 GET/POST 变量方式
        //  ini_set('session.use_trans_sid',0);
        //设置垃圾回收最大生存时间
        ini_set('session.gc_maxlifetime', 2592000);//86400 * 3
        //使用 COOKIE 保存 SESSION ID 的方式
        ini_set('session.use_cookies', 1);
        ini_set('session.cookie_path', '/');

        //多主机共享保存 SESSION ID 的 COOKIE,注意此处域名为一级域名
        ini_set('session.cookie_domain', empty($this->configSession['domain']) ? ARequestParameter::getSingleton()->getServerByKey('HTTP_HOST') : $this->configSession['domain']);
        ini_set('session.cookie_lifetime', empty($this->configSession['lifetime']) ? 1800 : $this->configSession['lifetime']);


        // 如果有设置Session数据库缓存,否则开启Session
        if (empty($this->configSession ['session'])) {
            //开始会话
            $this->sessionStart();
            return;
        }

        //将 session.save_handler 设置为 user，而不是默认的 files
        session_module_name('user');
        //$session = $this;
        session_set_save_handler(
            [self, 'session_open'],
            [self, 'session_close'],
            [self, 'session_read'],
            [self, 'session_write'],
            [self, 'session_destroy'],
            [self, 'session_gc']
        );

        //开始会话
        $this->sessionStart();

    }

    private function sessionStart()
    {
        session_start();

        //初始化Session Id
        if (IS_CLIENT !== FALSE) {

            $this->requestReturnData();
        }
    }

    /**
     *
     * @return string
     */
//    public function getAccessToken()
//    {
//        return md5(TOKEN . session_id());
//    }

    /**
     * @return mixed|string
     */
    public function requestReturnData()
    {
        //如果不是接口调试
        if (IS_CLIENT === false) {
            return '';
        }


        $request = ARequestParameter::getSingleton();

        $accessToken = $request->getRequestByKey('accessToken');;
        if (empty($accessToken)) {

            $return = new AReturn();
            $return->setResult(ErrorCode::$ACCESS_TOKEN_NULL);
            $return->setMessage("this error is at line:" . __LINE__ . ", in file:" . __FILE__ . ',the $_Request content is  :'
                . PHP_EOL . '--------------------------' . PHP_EOL . var_export($request->getRequest(), true)
                . PHP_EOL . '--------------------------' . PHP
            );
            die(json_encode($return));
        }

        $sid = session_id();
        //检测令牌是否错误
        $token = ADesEncrypt::encrypt($sid);
        if ($accessToken !== $token) {
            $return = new AReturn();
            $return->setResult(ErrorCode::$ACCESS_TOKEN_ERROR);
            $return->setMessage("[MESSAGE]this error is at line:" . __LINE__ . ", in file:" . __FILE__ . ',the $_Request content is  :'
                . PHP_EOL . '--------------------------' . PHP_EOL . var_export($request->getRequest(), true)
                . PHP_EOL . '--------------------------'
                . PHP_EOL . '[ACCESS_TOKEN]:' . var_export($accessToken, true)
                . PHP_EOL . '[ENCRYPT_TOKEN]:' . $token
                . PHP_EOL . '[SESSION_ID]:' . $sid

            );
            die(json_encode($return));
        }
        return '';
    }

    /**
     * @return ASession
     */
    public static function getInstance()
    {
        if (empty(self::$session)) {
            self::$session = new ASession();
        }
        return self::$session;
    }

}



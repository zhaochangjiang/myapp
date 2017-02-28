<?php
namespace framework\bin;


/**
 *
 * @author zhaocj
 *        
 */
class ASession
{

    var $configSession;
    var $memoryStorage;
    var $model;
    var $memcachePrufix = 'SE_';

    /**
     * session打开
     *
     * @return void
     */
    function session_open($save_path,$session_name)
    {

//        $session_model = D($this->configSession['dblink']);
//        $str_sql = " CREATE TABLE IF NOT EXISTS `user_session`( `sid` varchar(50) NOT NULL COMMENT '用户Session_ID', `expire` int(10) NOT NULL COMMENT '最近一次使用本站时间', `ip` varchar(50) NOT NULL, `data` text NOT NULL COMMENT 'Session内容', PRIMARY KEY (`sid`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//        $session_model->query($str_sql);

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
     *        	String	session_id
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
     *        	String
     *        	session的id
     * @param $value -
     *        	保存在session的数据
     */
    function session_write($key,$value)
    {
        $feild = array(
            'sid'   =>$key,
            'expire'=>TIMESTAMP,
            'ip'    =>getRealIp(),
            'data'  =>$value
        );
        $this->model->replaceSession($feild);
        return $value;
    }

    /**
     * 销毁session
     *
     * @param String $key
     *        	-
     *        	session id
     * @return void
     */
    function session_destroy($key)
    {

        $this->model->deleteSession($key);
        return true;
    }

    /**
     * 垃圾回收，销毁过期的session
     * crontab例程销毁session
     */
    function session_gc($maxlifetime)
    {
        $this->model->deleteBatch(array(
            'expire'=>array(
                'doType'=>'<',
                'value' =>TIMESTAMP-$maxlifetime)
        ));
        return true;
    }

    private function __construct($session)
    {
        /**
         * [S]开始session*
         */
        $this->configSession = $session;
        $this->model         = M($session ['model']);
        $this->model->setConfigSession($session);
        //设置色session id的名字
        ini_set('session.name',$this->configSession ['sessionName']);
        //不使用 GET/POST 变量方式
        //  ini_set('session.use_trans_sid',0);
        //设置垃圾回收最大生存时间
        ini_set('session.gc_maxlifetime',86400*3);
        //使用 COOKIE 保存 SESSION ID 的方式
        ini_set('session.use_cookies',1);
        ini_set('session.cookie_path','/');
        //多主机共享保存 SESSION ID 的 COOKIE,注意此处域名为一级域名
        ini_set('session.cookie_domain',$this->configSession['domain']);
        ini_set('session.cookie_lifetime',$this->configSession['lifetime']);
        //将 session.save_handler 设置为 user，而不是默认的 files
        session_module_name('user');
        //$session = $this;
        session_set_save_handler(array(
            $this,
            'session_open'),array(
            $this,
            'session_close'),array(
            $this,
            'session_read'),array(
            $this,
            'session_write'),array(
            $this,
            'session_destroy'),array(
            $this,
            'session_gc'));
        session_start();
    }

    public static function getInstance()
    {
        new ASession(App::base()->session);
    }

}

//加载Session信息
ASession::getInstance();

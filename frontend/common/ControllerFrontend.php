<?php

namespace frontend\common;

use framework\App;
use framework\bin\base\AController;

/**
 * 网站前台基类
 *
 * @author zhaocj
 */
class ControllerFrontend extends AController
{

    public $applicationDIr;

    public function init()
    {
        parent::init();
        $this->applicationDIr = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
    }

    /**
     * 创建地址
     * @param String $module
     * @param String $action
     * @param array $params
     * @param String $domain
     * @return type
     */
    public function urlCreate($module, $action, $params = array(), $domain = '')
    {
        if (empty($action)) {
            return $this->createUrl($module, $params, $domain);
        }
        return $this->createUrl("{$module}{$this->delimiterModuleAction}{$action}", $params, $domain);
    }

    /**
     * 调用系统接口的工具连接
     * @author karl.zhao<zhaocj2009@126.com>
     * @param String $moduleAction
     * @param Array|null $gets
     * @param Array|null $posts
     * @return String
     */
    protected function httpConnectionByBase($moduleAction, $gets = array(), $posts = array())
    {
        return parent::httpConnectionByBase($moduleAction, $gets, $posts);
    }

    /**
     * @author kar.zhao<zhaocj2009@126.com>
     * @since 2016/09/20
     * 获取默认的登录地址
     * @return type
     */
    protected function getDefaultLoginGoto()
    {
        return App::base()->params['domain']['userProfile'];
    }

    public $version = '1.0';
    public $cssFile = array(
        'bootstrap.css',
        'font-awesome.css',
        'magnific-popup.css',
        'datepicker3.css',
        'theme.css',
        'skins/default.css',
        'theme-custom.css',
    );
    public $jsFileBefore = array(
        'jquery.min.js',
        //     'jquery-ui-1.10.3.min.js',
        'bootstrap.min.js',
        'modernizr.js'
    );
    public $jsFileAfter = array(
        'jquery-browser-mobile/jquery.browser.mobile.js',
        'nanoscroller.js',
        'bootstrap-datepicker.js',
        'magnific-popup.js',
        'jquery-placeholder/jquery.placeholder.js',
        'theme.js',
        'theme.custom.js',
        'theme.init.js',
//      'raphael-min.js',
//      'plugins/morris/morris.min.js',
//      'plugins/sparkline/jquery.sparkline.min.js',
//      'plugins/jvectormap/jquery-jvectormap-1.2.2.min.js',
//      'plugins/jvectormap/jquery-jvectormap-world-mill-en.js',
//      'plugins/fullcalendar/fullcalendar.min.js',
//      'plugins/jqueryKnob/jquery.knob.js',
//      'plugins/daterangepicker/daterangepicker.js',
//      'plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',
//      'plugins/iCheck/icheck.min.js',
//      'AdminLTE/app.js',
//      'AdminLTE/dashboard.js', 
    );

}

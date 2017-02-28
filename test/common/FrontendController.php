<?php

  namespace test\common;

  namespace test\common;

  /**
   * 网站前台基类
   *
   * @author zhaocj
   */
  class FrontendController extends \framework\bin\AController
  {

      public $version      = '1.0';
      public $cssFile      = array(
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
      public $jsFileAfter  = array(
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
  
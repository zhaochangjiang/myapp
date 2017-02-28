<?php

  namespace backend\common;

  use framework\bin\AController;
  use communal\models\admin\permit\ModelPermit;
  use framework\App;
  use communal\models\admin\permit\ModelPermitGroup;

  /**
   * Description of BackendController
   *
   * @author zhaocj
   */
  abstract class ControllerBackend extends AController
  {

      public $permitList;
      public $applicationDIr;
      public $pageSmallTitle;
      public $version = '1.12';
      public $cssFile = array(
          'bootstrap.min.css',
          'font-awesome.min.css',
          'ionicons.min.css',
          'morris/morris.css',
          'jvectormap/jquery-jvectormap-1.2.2.css',
          'fullcalendar/fullcalendar.css',
          'daterangepicker/daterangepicker-bs3.css',
          'bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
          'AdminLTE.css',
          'fileinput/fileinput.css',
      );
      public $jsFileBefore = array(
          'jquery.min.js',
          'jquery-ui-1.10.3.min.js',
          'bootstrap.min.js',
          'fileinput/fileinput.js',
          'fileinput/fileinput_locale_zh.js',
      );
      public $jsFileAfter = array(
          'raphael-min.js',
     //     'plugins/morris/morris.min.js',
          'plugins/sparkline/jquery.sparkline.min.js',
          'plugins/jvectormap/jquery-jvectormap-1.2.2.min.js',
          'plugins/jvectormap/jquery-jvectormap-world-mill-en.js',
          'plugins/fullcalendar/fullcalendar.min.js',
          'plugins/jqueryKnob/jquery.knob.js',
          'plugins/daterangepicker/daterangepicker.js',
          'plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',
          'plugins/iCheck/icheck.min.js',
          'AdminLTE/app.js',
        'AdminLTE/dashboard.js',
      );

      public function init()
      {
          parent::init();
          $this->data['session'] = App::getSession();

          //验证是否登录
          $this->validateLogin();

          $this->initApplicationDIr();

          $this->data['avater'] = $this->getAvater($this->data['session']);

          //权限验证
          $this->permitInit();
      }

      /**
       * 登录验证
       */
      public function validateLogin()
      {
          if (empty($this->data['session']))
          {
              $this->redirect($this->createUrl(array(
                          'passport',
                          'login'), array(), App::base()->params['domain']['web']));
          }
      }

      /**
       * 
       * @return void
       */
      protected function initApplicationDIr()
      {
          if (!empty($this->applicationDIr))
          {
              return;
          }
          $this->applicationDIr = dirname(dirname(__FILE__)) . D_S;
      }

      
      /**
       * 
       * @param type $params
       * @return type
       */
      protected function getAdminModuleActionArray($params)
      {
          $moduleActionArray=array();
          if (!empty($params['controller']) && !empty($params['action']))
          {
              $moduleActionArray[]=$params['controller'];
              $moduleActionArray[]=$params['action'];
          }
          if (!empty($params['module']))
          {
              $moduleActionArray[] = $params['module'];
          }
          return $moduleActionArray;
      }

      public function isSuperAdmin()
      {
          return false;
      }

      /**
       * 判断当前页面是否有权限
       * @param type $controller
       * @param type $moduleControllerActionArray
       * @return boolean
       */
      protected function havePermit($moduleControllerActionArray)
      {
          if ($this->isSuperAdmin())
          {
              return true;
          }
          foreach ((array) $this->permitList['childPermit'] as $value)
          {
              $dataArray = array(
                  $value['controller'],
                  $value['action'],
                  $value['module']
              );
              $diffArray = array_diff($moduleControllerActionArray, $dataArray);
              if (empty($diffArray))
              {
                  return true;
              }
          }
          return false;
      }

      protected function outputHtml($param, $default = '')
      {
          if (empty($param))
          {
              return htmlentities($default);
          }
          return htmlentities($param);
      }

      /**
       * 
       * @return type
       */
      protected function base64encodeCurrentUrl()
      {
          return \framework\bin\AUtils::base64encodeCurrentUrl();
      }

      /**
       * 生成面包屑字符串
       * @$separator String 分隔符
       *
       * @return string
       */
      protected function getBreadCrumbs($separator = '&raquo;')
      {
          $breadCrumbString = ' <ol class="breadcrumb">';


//          <li><a href = "#"><i class = "fa fa-dashboard"></i> Home</a></li>
//          <li><a href = "#">Tables</a></li>
//          <li class = "active">Simple</li>
          $count = count($this->breadCrumbs);
          if ($count)
          {
              $class = $href = '';
              $i = 0;


              foreach ($this->breadCrumbs as $value)
              {
                  $i++;
                  $class = (!empty($value ['class']) ) ? "{$value['class']}" : '';
                  $href = ( empty($value ['href']) ) ? 'javascript:;' : $value ['href'];
                  $classli = ($i == $count) ? 'active' : '';
                  $breadCrumbString .=$i == 1 ? "<li class=\"{$classli}\"><a class=\"{$class}\" href=\"{$href}\" title=\"{$value['name']}\"><i class=\"fa fa-dashboard\"></i>{$value['name']}</a>" : "<li  class=\"{$classli}\"><a class=\"{$class}\"  href=\"{$href}\" title=\"{$value['name']}\">{$value['name']}</a></li>";
              }
          }
          $breadCrumbString.='</ol>';
// xmp($breadCrumbString);
          return $breadCrumbString;
      }

      /**
       * 
       * @param type $module
       * @param type $action
       * @param type $params
       * @param type $domain
       * @return type
       */
//      public function urlCreate($module, $action, $params = array(),
//                                $domain = '')
//      {
//          if (empty($action))
//          {
//              return $this->createUrl($module, $params, $domain);
//          }
//          return $this->createUrl("{$module}{$this->delimiterModuleAction}{$action}", $params, $domain);
//      }

      /**
       * 权限初始化
       */
      public function permitInit()
      {
          $permitModel = new ModelPermit();
          $this->permitList = $permitModel->getShowPermit($this->moduleString, $this->controllerString, $this->action);
          //如果不是超级管理员
          if (!$this->authSuperAdmin())
          {
              $permitGroup = new ModelPermitGroup();
              $this->permitList = $permitGroup->permitListNotSuperAdmin($this->permitList, $this->_getSession());
          }
      }

      private function _getSession()
      {
          return App::getSession();
      }

      /**
       * 是否超级管理员
       * @return boolean
       */
      private function authSuperAdmin()
      {
          $session = $this->_getSession();

          if ($session['have_admin_permit'])
          {
              return true;
          }
          return false;
      }

      /**
       * 获得头像
       * @param type $user
       * @return string
       */
      public function getAvater($user)
      {

          if ($user['gender'] === 'female')
          {
              return 'source/img/avatar2.png';
          }
          return 'source/img/avatar.png';
      }

  }
  
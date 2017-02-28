<?php

  namespace backend\modules\data\controllers;

  use backend\common\ControllerBackend;
  use communal\models\data\category\ModelCategory;
  use backend\common\Pager;
  use frontend\common\FrontendResultContent;
  use Exception;

  class ControllerCategory extends ControllerBackend
  {

      public function init()
      {
          $this->setBreadCrumbs(array(
              'name' => '系统配置'));
          parent::init();
      }

      private function _getModel()
      {
          if (empty($this->model))
          {
              $this->model = new ModelCategory();
          }
          return $this->model;
      }

      /**
       * 
       */
      public function actionList()
      {
//          $string= \communal\common\CommunalTools::createGuid();
//          echo $string;
          $this->params = $this->getRequestParams();
          $model = $this->_getModel();
          $this->data['data'] = $model->getList($this->params);

          $this->data['pageObject'] = new Pager($this->data['data']['count']);
          $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];

          //    xmp($this->data['data']);
          $this->pageTitle = '类型';
          $this->setBreadCrumbs(array(
              'name' => $this->pageTitle,
              'href' => currentUrl()
          ));
          $this->render();
      }

      /**
       * 
       * @param type $upidArray
       * @return type
       */
      private function _dealUpId($upidArray)
      {
          foreach ((array) $upidArray as $key => $value)
          {
              if (empty($value))
              {
                  unset($upidArray[$key]);
              }
          }
          return array_pop($upidArray);
      }

      public function actionIframeEdit()
      {

          $this->params = $this->getRequestParams();

          $this->data['goto'] = base64_decode($this->params['goto']);
          $jsString = $result = '';


          $this->params['higher_up_id'] = $this->_dealUpId($this->params['uppid']);

          $resultData = FrontendResultContent::getInstanceAnother();
          $model = $this->_getModel();
          $sku = $model->getSku($this->params);

          $data = array(
              'category_label' => $this->params['category_label'],
              'sku' => (string) $sku,
              'higher_up_id' => (string) $this->params['higher_up_id']
          );

          switch ($this->params['dotype'])
          {
              case 'update':

                  try
                  {
                      if (empty($this->params['category_id']))
                      {
                          throw new Exception('您没有选择要编辑的数据!');
                      }
                      $model->updateData(
                              $data, array(
                          'category_id' => $this->params['category_id']
                              )
                      );
                  }
                  catch (Exception $ex)
                  {
                      $message = $ex->getMessage();
                      if (!empty($message))
                      {
                          $jsString.='parent.showerror("错误信息：' . $message . '");';
                          $resultData->setJavascriptContent($jsString);
                          $this->outPutIframeMessage($resultData);
                      }
                  }

                  break;
              case 'add':
                  if (empty($this->params['category_label']))
                  {
                      $jsString.='parent.showerror("请填写权限名");';
                      $resultData->setJavascriptContent($jsString);
                      $this->outPutIframeMessage($resultData);
                  }
                  $result = $model->addData($data);
                  break;
              default:
                  throw new Exception('操作错误，请联系管理员!');
          }

          if (empty($result))
          {
              $jsString.='parent.showerror("' . $result->message . '");';
              $resultData->setJavascriptContent($jsString);
              $this->outPutIframeMessage($resultData);
          }
          $resultData->setJavascriptContent('', $this->data['goto']);
          $this->outPutIframeMessage($resultData);
      }

      public function actionDelete()
      {

          $this->params = $this->getRequestParams();
          $this->_getModel()->deleteData($this->params);
          exit('ok');
      }

      /**
       * 
       */
      public function actionEdit()
      {


          $this->params = $this->getRequestParams();

          $model = $this->_getModel();

          $this->data['data'] = $model->fetchOne(array(
              'category_id' => $this->params['category_id']));

          $this->data['goto'] = $this->params['goto'];
          $this->data['doType'] = $this->params['type'];
          $this->data['data']['uppid'] = $this->params['uppid'];


          $this->pageTitle = '类型编辑';
          $this->pageSmallTitle = '权限编辑';

          $breadCrumb['name'] = $this->pageTitle;
          $breadCrumb['href'] = $this->createUrl(array(
              $this->controllerString,
              'list',
              $this->moduleString));
          $this->setBreadCrumbs($breadCrumb);
          $breadCrumb['name'] = $this->pageTitle;
          $breadCrumb['href'] = currentUrl();
          $this->setBreadCrumbs($breadCrumb);
          $this->render();
          //          xmp($this->data['data']);
      }

  }
  
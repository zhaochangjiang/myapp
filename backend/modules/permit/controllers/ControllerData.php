<?php

  namespace backend\modules\permit\controllers;

  use backend\common\ControllerBackend;
  use framework\App;
  use communal\models\admin\permit\ModelPermitGroup;
  use communal\models\admin\permit\ModelPermit;
  use backend\common\Pager;
  use frontend\common\FrontendResultContent;
  use Exception;

  class ControllerData extends ControllerBackend
  {

      public function init()
      {
          $this->setBreadCrumbs(array(
              'name' => '系统配置'));
          parent::init();
      }

      public function actionGetchildpermit()
      {
          $this->params = $this->getRequestParams();
          $modelPermitGroup = new ModelPermitGroup();
          $data = $modelPermitGroup->getChildList($this->params['id']);
          echo $resultData = json_encode($data);
      }

      /**
       * 
       */
      public function actionList()
      {
          $this->params = $this->getRequestParams();

          $permitModel = new ModelPermit();
          $this->data['data'] = $permitModel->getList($this->params);
          $this->data['listPermit'] = $permitModel->getListPermitDataByNowPermitId($this->params);

          $this->data['pageObject'] = new Pager($this->data['data']['count']);
          $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];


          $this->pageTitle = '权限配置';
          $this->setBreadCrumbs(array(
              'name' => $this->pageTitle,
              'href' => currentUrl()
          ));
          $this->render();
      }

      /**
       * 
       * @param type $param
       * @return type
       */
      private function dealUpPermitId($param)
      {

          foreach ($param as $key => $value)
          {
              if (empty($value))
              {
                  unset($param[$key]);
              }
          }
          return array_pop($param);
      }

      /**
       * 
       * @throws Exception
       */
      public function actionIframeEdit()
      {

          $this->params = $this->getRequestParams();
          $this->params['uppid'] = $this->dealUpPermitId($this->params['uppid']);

          $this->data['goto'] = base64_decode($this->params['goto']);
          $jsString = $result = '';

          $resultData = FrontendResultContent::getInstanceAnother();
          $permitModel = new ModelPermit();

          $data = array(
              'uppermit_id' => (int) $this->params['uppid'],
              'action' => $this->params['action'],
              'controller' => $this->params['controller'],
              'module' => $this->params['module'],
              'csscode' => $this->params['csscode'],
              'name' => $this->params['name'],
              'obyid' => (int) $this->params['obyid']
          );

          switch ($this->params['dotype'])
          {
              case 'update':

                  if (empty($this->params['id']))
                  {
                      $jsString.='parent.showerror("您没有选择要编辑的数据!");';
                      $resultData->setJavascriptContent($jsString);
                      $this->outPutIframeMessage($resultData);
                  }

                  $result = $permitModel->updateData(
                          $data, array(
                      'id' => $this->params['id']
                          )
                  );
                  break;
              case 'add':
                  if (empty($this->params['name']))
                  {
                      $jsString.='parent.showerror("请填写权限名");';
                      $resultData->setJavascriptContent($jsString);
                      $this->outPutIframeMessage($resultData);
                  }
                  $result = $permitModel->addData($data);
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

          $permitModel = new ModelPermit();

          $result = $permitModel->deleteData($this->params);
          if ($result->code != 200)
          {
              die('操作失败!');
          }
          exit('ok');
      }

      /**
       * 
       */
      public function actionEdit()
      {

          $params = $this->getRequestParams();

          $permitModel = new ModelPermit();

          $this->data['data'] = $permitModel->fetchOne($params);

          //xmp( $this->data['data'] );
          $this->data['goto'] = $params['goto'];
          $this->data['doType'] = $params['type'];
          $this->data['data']['uppid'] = $params['uppid'];


          $this->pageTitle = '权限配置';
          $this->pageSmallTitle = '权限编辑';
          $this->setBreadCrumbs(array(
              array(
                  'name' => $this->pageTitle,
                  'href' => $this->createUrl(array(
                      $this->controllerString,
                      'list',
                      $this->moduleString))
              ),
              array(
                  'name' => $this->pageSmallTitle,
                  'href' => currentUrl()
              )
                  ), true);
          $this->render();
      }

  }
  
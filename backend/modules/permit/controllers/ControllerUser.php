<?php

namespace backend\modules\permit\controllers;

use backend\common\ControllerBackend;
use communal\models\admin\permit\ModelAdminUser;
use communal\models\admin\permit\ModelGroupUser;
use backend\common\Pager;
use frontend\common\FrontendResultContent;
use Exception;

class ControllerUser extends ControllerBackend
{

    public function init()
    {
        $this->setBreadCrumbs(array(
            'name' => '权限设置'));
        parent::init();
    }

    /**
     *
     * @return ModelAdminUser
     */
    private function _getModel()
    {
        if (empty($this->model)) {
            return $this->model = new ModelAdminUser();
        }
        return $this->model;
    }

    public function actionList()
    {
        $this->params = $this->getRequestParams();

        $model = $this->_getModel();
        $this->data['data'] = $model->getList($this->params);

        $this->data['pageObject'] = new Pager($this->data['data']['count']);
        $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];

        $this->pageTitle = '权限设置';
        $this->pageSmallTitle = '后台用户';
        $this->setBreadCrumbs(array(
            'name' => $this->pageSmallTitle,
            'href' => currentUrl()
        ));
        $this->render();
    }

    public function actionDelete()
    {

        $this->params = $this->getRequestParams();

        $params['uid'] = $this->params['uid'];
        $model = $this->_getModel();

        $result = $model->deleteData($params);

        if ($result->code != 200) {
            die('操作失败!');
        }
        exit('ok');
    }

    public function actionEdit()
    {
        $this->params = $this->getRequestParams();
        $this->data['goto'] = $this->params['goto'];

        $model = $this->_getModel();

        $this->data['data'] = $model->fetchOne(array(
            'uid' => $this->params['uid']));

        $tmp = (new ModelGroupUser())->getRow(array(
            'admin_userid' => $this->data['data']['uid']));
        $this->data['data']['id'] = $tmp['group_id'];

        $this->pageTitle = '权限设置';
        $this->pageSmallTitle = '后台用户编辑';

        $breadCrumb['name'] = '后台用户';
        $breadCrumb['href'] = $this->createUrl(array(
            $this->controllerString,
            'list',
            $this->moduleString));
        $this->setBreadCrumbs($breadCrumb);
        $breadCrumb['name'] = $this->pageSmallTitle;
        $breadCrumb['href'] = currentUrl();
        $this->setBreadCrumbs($breadCrumb);
        $this->render();
    }

    /**
     *
     * @param type $param
     * @return type
     */
    private function _dealUpId($param)
    {

        foreach ($param as $key => $value) {
            if (empty($value)) {
                unset($param[$key]);
            }
        }
        return array_pop($param);
    }

    public function actionGetchild()
    {

    }

    /**
     *
     * @throws Exception
     */
    public function actionIframeEdit()
    {

        $this->params = $this->getRequestParams();

        $this->params['uppid'] = $this->_dealUpId($this->params['uppid']);
        $this->data['goto'] = base64_decode($this->params['goto']);


        $jsString = $result = '';

        $resultData = FrontendResultContent::getInstanceAnother();
        $model = $this->_getModel();

        $modelGroupUser = new ModelGroupUser();

        $data = array(
            'super_admin' => (string)$this->params['super_admin'],
            'name' => (string)$this->params['name']
        );

        switch ($this->params['dotype']) {
            case 'update':

                if (empty($this->params['uid'])) {
                    $jsString .= 'parent.showerror("您没有选择要编辑的数据!");';
                    $resultData->setJavascriptContent($jsString);
                    $this->outPutIframeMessage($resultData);
                }

                $modelGroupUser->replaceData(
                    array(
                        'admin_userid' => (int)$this->params['uid'],
                        'group_id' => (int)$this->params['uppid']
                    )
                );
                $result = $model->updateData(
                    $data, array(
                        'uid' => (int)$this->params['uid']
                    )
                );
                break;
            case 'add':
                if (empty($this->params['name'])) {
                    $jsString .= 'parent.showerror("请填组名");';
                    $resultData->setJavascriptContent($jsString);
                    $this->outPutIframeMessage($resultData);
                }

                $id = $model->addData($data);
                $modelGroupUser->replaceData(
                    array(
                        'admin_userid' => $id,
                        'group_id' => (int)$this->params['uppid']
                    )
                );
                break;
            default:
                throw new Exception('操作错误，请联系管理员!');
        }

        if (empty($result)) {
            $jsString .= 'parent.showerror("' . $result->message . '");';
            $resultData->setJavascriptContent($jsString);
            $this->outPutIframeMessage($resultData);
        }
        $resultData->setJavascriptContent('', $this->data['goto']);
        $this->outPutIframeMessage($resultData);
    }

}
  
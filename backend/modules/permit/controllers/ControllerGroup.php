<?php

namespace backend\modules\permit\controllers;

use backend\common\ControllerBackend;
use communal\models\admin\permit\ModelGroup;
use backend\common\Pager;
use frontend\common\FrontendResultContent;
use Exception;
use communal\models\admin\permit\ModelGroupUser;
use communal\models\admin\permit\ModelPermitGroup;
use communal\models\admin\permit\ModelPermit;
use framework\bin\utils\AUtils;

class ControllerGroup extends ControllerBackend
{

    public function init()
    {

        $this->setBreadCrumbs(array(
            'name' => '权限设置'));
        parent::init();
    }

    /**
     *
     */
    public function actionSetpermit()
    {

        $this->params = $this->getRequestParams();

        $modelPermitGroup = new ModelPermitGroup();
        $modelPermit = new ModelPermit();

        $this->data['data']['list'] = $modelPermit->getChildListByUppid((int)$this->params['uppid']);

        $this->data['data'] = $modelPermitGroup->getPermitShow($this->data['data'], $this->params['id']);

        $modelGroup = new ModelGroup();
        $this->data['group'] = $modelGroup->getOneData($this->params['id']);


        $this->pageTitle = '权限组';
        $this->pageSmallTitle = '(' . $this->data['group']['name'] . ')权限设置';
        $this->setBreadCrumbs(array(
            array(
                'name' => $this->pageTitle,
                'href' => base64_decode($this->params['goto'])
            ),
            array(
                'name' => $this->pageSmallTitle,
                'href' =>AUtils:: currentUrl()
            )
        ), true);
        $this->render();
    }

    public function actionAjaxsetpermit()
    {
        $this->params = $this->getRequestParams();

        $modelPermitGroup = new ModelPermitGroup();

        $modelPermitGroup->setAjaxsetpermit($this->params);
        echo 'ok';
        exit;
    }

    public function actionAjaxsetbatchpermit()
    {

        $this->params = $this->getRequestParams();
        $modelPermitGroup = new ModelPermitGroup();
        $modelPermitGroup->setAjaxsetbatchpermit($this->params);
        echo 'ok';
        exit;
    }

    public function actionList()
    {
        $this->params = $this->getRequestParams();

        $permitModel = new ModelGroup();
        $this->data['data'] = $permitModel->getList($this->params);
        $this->data['data']['list'] = $this->getUserAccount($this->data['data']['list']);

        $this->data['pageObject'] = new Pager($this->data['data']['count']);
        $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];


        $this->pageTitle = '权限设置';
        $this->pageSmallTitle = '权限组';
        $this->setBreadCrumbs(array(
            array(
                'name' => $this->pageSmallTitle,
                'href' => AUtils::currentUrl()
            )
        ), true);
        $this->render();
    }

    private function getUserAccount($data)
    {
        //stop($data);
        $ids = array();
        foreach ($data as $value) {
            if (!empty($value['id']) && !in_array($value['id'], $ids)) {
                $ids[] = $value['id'];
            }
        }

        $modelPermitGroup = new ModelGroupUser();
        $fetchData = $modelPermitGroup->getUserAccountCount($ids);

        $countAccountById = array();
        foreach ($fetchData as $key => $value) {
            $countAccountById[$value['id']] = $value;
        }

        foreach ($data as $key => $value) {
            $data[$key]['adminUserCount'] = isset($countAccountById[$value['id']]) ? $countAccountById[$value['id']]['adminUserCount'] : 0;
        }
        return $data;
    }

    /**
     *
     */
    public function actionGroupuser()
    {
        $this->params = $this->getRequestParams();

        $permitModel = new ModelGroupUser();
        $this->data['data'] = $permitModel->getList($this->params);
        $this->data['data']['list'] = $permitModel->getUserNameByUid($this->data['data']['list']);
        $this->data['pageObject'] = new Pager($this->data['data']['count']);
        $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];

        $this->pageTitle = '权限设置';
        $this->pageSmallTitle = '权限组用户';
        $this->setBreadCrumbs(array(
            array(
                'name' => '权限组',
                'href' => $this->createUrl(array(
                    $this->controllerString,
                    'list',
                    $this->moduleString))
            ),
            array(
                'name' => $this->pageSmallTitle,
                'href' => AUtils::currentUrl()
            )
        ), true);

        $this->render();
    }

    public function actionDeletegroupuser()
    {

        $this->params = $this->getRequestParams();

        $modelGroupUser = new ModelGroupUser();
        $result = $modelGroupUser->deleteData(array(
            'admin_userid' => $this->params['admin_userid'],
            'group_id' => $this->params['group_id']
        ));
        exit('ok');
    }

    /**
     *
     * @param type $param
     * @return type
     */
    private function dealUpId($param)
    {

        foreach ($param as $key => $value) {
            if (empty($value)) {
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

        $this->params['uppid'] = $this->dealUpId($this->params['uppid']);
        $this->data['goto'] = base64_decode($this->params['goto']);


        $jsString = $result = '';

        $resultData = FrontendResultContent::getInstanceAnother();
        $modelGroup = new ModelGroup();

        $data = array(
            'up_groupid' => (int)$this->params['uppid'],
            'super_admin' => (string)$this->params['super_admin'],
            'name' => (string)$this->params['name']
        );
        switch ($this->params['dotype']) {
            case 'update':

                if (empty($this->params['id'])) {
                    $jsString .= 'parent.showerror("您没有选择要编辑的数据!");';
                    $resultData->setJavascriptContent($jsString);
                    $this->outPutIframeMessage($resultData);
                }

                $result = $modelGroup->updateData(
                    $data, array(
                        'id' => (int)$this->params['id']
                    )
                );
                break;
            case 'add':
                if (empty($this->params['name'])) {
                    $jsString .= 'parent.showerror("请填组名");';
                    $resultData->setJavascriptContent($jsString);
                    $this->outPutIframeMessage($resultData);
                }
                $result = $modelGroup->addData($data);
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

    /**
     *
     */
    public function actionDelete()
    {

        $this->params = $this->getRequestParams();

        $modelGroup = new ModelGroup();
        $modelPermitGroup = new ModelPermitGroup();
        $modelGroup->startAffair();
        $modelPermitGroup->startAffair();
        $result = $modelGroup->deleteData($this->params);

        $modelPermitGroup->deletePermitGroupById($this->params['id']);

        $modelPermitGroup->commit();
        $modelGroup->commit();
        if ($result->code != 200) {
            die('操作失败!');
        }
        exit('ok');
    }

    /**
     *
     */
    public function actionGetchild()
    {
        $this->params = $this->getRequestParams();
        $modelGroup = new ModelGroup();
        $data = $modelGroup->getChildList($this->params['id']);
        echo $resultData = json_encode($data);
    }

    /**
     *
     */
    public function actionEdit()
    {
        $params = $this->getRequestParams();
        $this->data['doType'] = $params['type'];

        $permitModel = new ModelGroup();

        $this->data['data'] = $permitModel->fetchOne($params);

        $this->data['goto'] = $params['goto'];

        $this->pageTitle = '用户组';
        $this->pageSmallTitle = '用户组编辑';
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
  
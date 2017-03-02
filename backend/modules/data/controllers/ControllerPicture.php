<?php

namespace backend\modules\data\controllers;

use backend\common\ControllerBackend;
use backend\common\Pager;
use communal\models\data\picture\ModelPictureCategory;
use communal\models\data\picture\ModelPicture;

/**
 * Description of PictureController
 *
 * @author zhaocj
 */
class ControllerPicture extends ControllerBackend
{

    public function actionCategoryList()
    {

        $this->params = $this->getRequestParams();

        $model = new ModelPictureCategory();
        $this->data['data'] = $model->getList($this->params);

        $this->data['pageObject'] = new Pager($this->data['data']['count']);
        $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];

        $this->pageTitle = '基础数据';
        $this->pageSmallTitle = '图片';
        $this->setBreadCrumbs(array(
            'name' => $this->pageSmallTitle,
            'href' => currentUrl()
        ));
        $this->render();
    }

    public function actionCategoryDelete()
    {

        $this->params = $this->getRequestParams();

        $params['uid'] = $this->params['uid'];
        $model = new ModelPictureCategory();

        $result = $model->deleteData($params);

        if ($result->code != 200) {
            die('操作失败!');
        }
        exit('ok');
    }

    public function actionCategoryEdit()
    {
        $params = $this->getRequestParams();
        $this->data['goto'] = $params['goto'];


        $model = new ModelPictureCategory();

        $this->data['data'] = $model->fetchOne($params);


        $this->pageTitle = '权限设置';
        $this->pageSmallTitle = '后台用户编辑';
        $this->setBreadCrumbs(array(
            array(
                'name' => '后台用户',
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

    public function actionList()
    {

        $this->params = $this->getRequestParams();

        $model = new ModelPicture();
        $this->data['data'] = $model->getList($this->params);

        $this->data['pageObject'] = new Pager($this->data['data']['count']);
        $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];

        $this->pageTitle = '基础数据';
        $this->pageSmallTitle = '图片';
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
        $model = new ModelPicture();

        $result = $model->deleteData($params);

        if ($result->code != 200) {
            die('操作失败!');
        }
        exit('ok');
    }

    public function actionEdit()
    {
        $params = $this->getRequestParams();
        $this->data['goto'] = $params['goto'];


        $model = new ModelPicture();

        $this->data['data'] = $model->fetchOne($params);


        $this->pageTitle = '权限设置';
        $this->pageSmallTitle = '后台用户编辑';
        $this->setBreadCrumbs(array(
            array(
                'name' => '后台用户',
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
  
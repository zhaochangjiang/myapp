<?php

namespace backend\modules\system\controllers;

use backend\common\ControllerBackend;
use backend\common\Pager;
use communal\models\data\picture\ModelPictureCategory;
use framework\bin\utils\AUtils;
use frontend\common\FrontendResultContent;

/**
 * Description of PictureCategoryController
 *
 * @author zhaocj
 */
class ControllerPictureCategory extends ControllerBackend
{

    public function actionList()
    {

        $this->params = $this->getRequestParams();

        $permitModel = new ModelPictureCategory();
        $this->data['data'] = $permitModel->getList($this->params);

        $this->data['pageObject'] = new Pager($this->data['data']['count']);
        $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];

        $this->pageTitle = '图片类型';
        $this->pageSmallTitle = '配置列表';
        $this->setBreadCrumbs(array(
            'name' => $this->pageSmallTitle,
            'href' => AUtils::currentUrl()
        ));
        $this->render();
    }

    public function actionDelete()
    {

        $this->params = $this->getRequestParams();

        $params['id'] = $this->params['id'];
        $permitModel = new ModelPictureCategory();

        $result = $permitModel->deleteData($params);

        if ($result->code != 200) {
            die('操作失败!');
        }
        exit('ok');
    }

    /**
     *
     * @throws Exception
     */
    public function actionIframeEdit()
    {

        $this->params = $this->getRequestParams();


        $this->data['goto'] = base64_decode($this->params['goto']);
        $jsString = $result = '';

        $resultData = FrontendResultContent::getInstanceAnother();
        $model = new ModelPictureCategory();


        $data = array(
            'picure_categoryname' => $this->params['picure_categoryname'],
            'picure_categorykey' => $this->params['picure_categorykey'],
            'picure_savepath' => $this->params['picure_savepath']
        );
        switch ($this->params['dotype']) {
            case 'update':

                if (empty($this->params['picure_category_id'])) {
                    $jsString .= 'parent.showerror("您没有选择要编辑的数据!");';
                    $resultData->setJavascriptContent($jsString);
                    $this->outPutIframeMessage($resultData);
                }

                $result = $model->updateData(
                    $data, array(
                        'picure_category_id' => $this->params['picure_category_id']
                    )
                );
                break;
            case 'add':
                if (empty($this->params['picure_categoryname'])) {
                    $jsString .= 'parent.showerror("请填类型名");';
                    $resultData->setJavascriptContent($jsString);
                    $this->outPutIframeMessage($resultData);
                }
                $result = $model->addData($data);
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

    public function actionEdit()
    {


        $params = $this->getRequestParams();
        $this->data['goto'] = $params['goto'];


        $this->data['doType'] = $params['doType'];

        $modelAdminUser = new ModelPictureCategory();

        $this->data['data'] = $modelAdminUser->fetchOne($params);

        $this->pageTitle = '图片类型';
        $this->pageSmallTitle = '配置';
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
                'href' => AUtils::currentUrl()
            )
        ), true);

        $this->render();
    }

}
  
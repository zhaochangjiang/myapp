<?php

namespace backend\modules\data\controllers;

use backend\common\ControllerBackend;
use backend\common\Pager;
use communal\models\data\ingredient\ModelIngredient;
use backend\common\BreadCrumb;
use backend\common\ReusltData;
use framework\bin\AUtils;
use Exception;
/**
 *  食材管理
 *
 * @author zhaocj
 */
class ControllerIngredient extends ControllerBackend
{

    public function actionUpload()
    {

        $data = $_FILES;
        echo json_encode($data);
    }

    /**
     *
     * @param array $picIdArray
     * @return type
     */
    public function getPrivewPicture(array $picIdArray)
    {
        return
            array(
                array(
                    'pictureViewLocate' => 'http://img1.gtimg.com/sports/pics/hv1/177/113/2087/135736167.jpg',
                    'pictureViewConfig' => array(
                        'caption' => '图片名称',
                        'width' => '120px',
                        'url' => 'http://localhost/avatar/delete', //删除图片地址
                        'key' => '100',
                        'extra' => array(
                            'id' => 100)
                    )
                )
            );
    }

    /**
     * 获得数据列表页面
     */
    public function actionList()
    {

        $this->params = $this->getRequestParams();

        $model = $this->_getModel();
        $this->data['data'] = $model->getList($this->params);

        $this->data['pageObject'] = new Pager($this->data['data']['count']);
        $this->data['pageObject']->pageSize = $this->data['data']['pageSize'];

        $this->pageTitle = '基础数据';
        $this->pageSmallTitle = '食材';

        $this->setBreadCrumbs(array(
            'name' => $this->pageSmallTitle,
            'href' => AUtils::currentUrl()
        ));
        $this->render();
    }

    /**
     *
     */
    public function actionDelete()
    {

        $this->params = $this->getRequestParams();

        $params['id'] = $this->params['id'];
        $model = $this->_getModel();

        $result = $model->deleteData($params);

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

        $resultData = ReusltData::getInstance();
        $modelGroup = new ModelGroup();

        $data = array(
            'ingredient_thumbnail' => $this->params['ingredient_thumbnail'],
            'ingredient_name' => $this->params['ingredient_name'],
            'ingredient_foodalias' => $this->params['ingredient_foodalias'],
            'ingredient_keyword' => $this->params['ingredient_keyword'],
            'ingredient_desc' => $this->params['ingredient_desc'],
            'ingredient_images' => $this->params['ingredient_images'],
            'ingredient_description' => $this->params['ingredient_description'],
        );
        switch ($this->params['type']) {
            case 'update':

                if (empty($this->params['id'])) {
                    $jsString = 'parent.showerror("您没有选择要编辑的数据!");';
                    $resultData->setJavascriptContent($jsString);
                    $this->outPutIframeMessage($resultData);
                }
                $data['update_time'] = date('Y-m-d H:i:s', time());
                $result = $modelGroup->updateData(
                    $data, array(
                        'id' => $this->params['id']
                    )
                );
                break;
            case 'add':
                if (empty($this->params['name'])) {
                    $jsString .= 'parent.showerror("请填组名");';
                    $resultData->setJavascriptContent($jsString);
                    $this->outPutIframeMessage($resultData);
                }
                $data['id'] = \communal\common\CommunalTools::createGuid();
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
     * 生成本类mOdel
     * @author Karl.zhao <zhaochangjiang@example.com>
     * @return type
     */
    private function _getModel()
    {
        if ($this->model) {
            return $this->model;
        }
        return $this->model = new ModelIngredient();
    }


    /**
     * 编辑数据页面
     */
    public function actionEdit()
    {
        $params = $this->getRequestParams();
        $this->data['goto'] = $params['goto'];

        $model = $this->_getModel();
        throw new Exception("dsfsdf");
        $this->data['data'] = $model->fetchOne($params);

        $this->data['type'] = $params['type'];


        $breadCrumb['name'] = $this->pageTitle = '基础数据';
        $breadCrumb['href'] = $this->createUrl(array(
            $this->controllerString,
            'list',
            $this->moduleString));
        $this->setBreadCrumbs($breadCrumb);
        $breadCrumb['name'] = $this->pageSmallTitle = '添加食材';
        $breadCrumb['href'] = AUtils::currentUrl();
        $this->setBreadCrumbs($breadCrumb);
        $this->render();
    }

}
  
<?php

namespace backend\modules\system\controllers;

use backend\common\ControllerBackend;
use communal\lib\SystemSwitch;

/**
 * Description of SwitchController
 *
 * @author zhaocj
 */
class ControllerSwitch extends ControllerBackend
{

    /**
     * 获得网站开关
     */
    public function actionWebsite()
    {
        $systemSwitch = new SystemSwitch();
        $data = $systemSwitch->getAllShowSwitch();
        $this->data = $this->formatTreeData($data);
        $this->pageTitle = '系统控制';
        $this->pageSmallTitle = '系统开关';

        $this->setBreadCrumbs(array(
            'name' => $this->pageSmallTitle,
            'href' => currentUrl()
        ));
        $this->render();
    }

    /**
     * 格式化数据,变成数组格式
     * @param type $data
     */
    private function formatTreeData($data)
    {
        if (empty($data)) {
            return array();
        }
        $step = 0;
        $result = array();
        $i = 0;
        $maxSize = 0;
        while (true) {
            if (empty($data)) {
                break;
            }
            $tmp = $this->getChildNode($data, $result, $step);
            list($topNodeList[$i]['data'], $data, $topNodeList[$i]['size'], $step) = $tmp;
            $result = $topNodeList[$i]['data'];
            if ($maxSize < $topNodeList[$i]['size']) {
                $maxSize = $topNodeList[$i]['size'];
            }
            $i++;
            if ($i > 20) {
                break;
            }
        }
        return array('maxSize' => $maxSize, 'nodeList' => $topNodeList);
    }

    private function getChildNode($data, $result, $step)
    {
        if (empty($data)) {
            return array();
        }
        $resultData = array();
        $step = $step + 1;
        if (empty($result)) {
            foreach ($data as $key => $value) {
                if (empty($value['heigher_level_id'])) {
                    $value['step'] = $step;
                    $resultData[$value['id']] = $value;
                    unset($data[$key]);
                }
            }
        } else {
            foreach ($result as $key => $value) {
                foreach ($data as $k => $val) {
                    if ($val['heigher_level_id'] === $value['id']) {
                        $val['step'] = $step;
                        $resultData[$val['id']] = $val;
                        unset($data[$k]);
                    }
                }
            }
        }
        return array($resultData, $data, count($resultData), $step);
    }

}

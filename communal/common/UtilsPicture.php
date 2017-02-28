<?php

namespace communal\common;

use communal\models\data\picture\ModelPicture;
use communal\models\data\picture\ModelPictureCategory;

/**
 * Description of PictureUtils
 *
 * @author zhaocj
 */
class UtilsPicture {

    private $_ids;
    private $_category;
    private $_data;

    public function setData($data) {
        if(empty($data['id'])){
            throw new Exception();
        }
        $this->_data[$data['id']] = $data;
    }

    private function _initId() {
        return UtilsCommunalTools::createGuid();
    }

    /**
     * 生成图片的数量
     * @param int $count
     */
    public function initData(array $suffix) {


        foreach ($suffix as $value) {
            $guid = $this->_initId();
            $this->_data[$guid] = array('suffix' => $value, 'id' => $guid);
        }
    }

}

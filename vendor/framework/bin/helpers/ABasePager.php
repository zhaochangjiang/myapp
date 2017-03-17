<?php

namespace framework\bin\helpers;

use framework\bin\base\AHelper;

/**
 * 分页类主类,继承自Awidget
 * 所有分页改写必须继承他
 * @author heypigg
 */
abstract class ABasePager extends AHelper
{

    public $totalNum;
    public $pagePrefix = 'page';
    public $showFirstLast = FALSE;
    public $currentPage; //当前页码
    public $pageSize;
    public $ajaxPage = false; //是否启用ajax
    public $ajaxTarget; //ajax最外层元素ID或者clas对象，注意是对象
    public $buttonLabel = array(
        'firstLabel' => '首页',
        'lastLabel' => '尾页',
        'prevLabel' => '上一页',
        'nextLabel' => '下一页');
    public $extend404 = true; //页码超出了是否显示404页面
    public static $moduleControllerAction;

    public function setExtend404($extend404)
    {
        $this->extend404 = $extend404;
    }

    public function __construct($count, $setExtend404)
    {
        parent::__construct();
        $this->totalNum = $count;
        $this->extend404 = $setExtend404;
        $this->init();
    }

    public function init()
    {


        $this->currentPage = $this->getIntPagenow($this->pagePrefix);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    protected function getModuleControllerActionArray($requestParam)
    {

        if (!self::$moduleControllerAction) {
            self::$moduleControllerAction = explode($this->delimiterModuleAction, $requestParam ['r']);
            $moduleString = array_shift(self::$moduleControllerAction);
            array_push(self::$moduleControllerAction, $moduleString);
        }
        return self::$moduleControllerAction;
    }

    //put your code here
    protected function getPageUrl($pageIndex, $requestParam, $localRefresh)
    {

        if ($this->ajaxPage) {
            $requestParam['ajaxPage'] = 1;
        }
        $actionArray = self::getModuleControllerActionArray($requestParam);
        unset($requestParam ['r']);

        $requestParam [$this->pagePrefix] = $pageIndex;

        $str_url = $this->createUrl($actionArray, $requestParam);

        if ($this->ajaxPage) {
            $str_url = $this->createUrl($actionArray, $requestParam);
            $clickEvent = "{$localRefresh}(\"{$str_url}\",this,{$this->ajaxTarget});return;";
        } else {
            $clickEvent = "{$localRefresh}(\"{$str_url}\");return;";
        }
        return $localRefresh === FALSE ? ' href="' . $str_url . '"' : " onclick='{$clickEvent}' href='javascript:void(0)' ";
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     *
     * @return string
     */
    public function getLimit()
    {
        $pageMax = ceil($this->totalNum / $this->pageSize);
        if ($pageMax < 1) {
            $pageMax = 1;
        }
        if ($this->extend404 && $this->currentPage > $pageMax) {
            App::error('没有此分页');
        }
        return ($this->currentPage - 1) * $this->pageSize . ",{$this->pageSize}";
    }

}
  
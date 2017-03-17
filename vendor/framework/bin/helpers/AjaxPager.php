<?php

/*
 * ajax分页
 */

class AjaxPager extends AWidget
{

    public $total;
    public $pageSize;
    public $ajaxJsMethod = "ajaxPage";
    public $ajaxTarget = "list";
    public $page;
    public $tips = "数据查询中...";

    protected function getPager()
    {
        $pager = new Pager($this->total);
        $pager->params = array('localRefresh' => $this->ajaxJsMethod);
        $pager->pageSize = $this->pageSize;
        $pager->ajaxTarget = $this->ajaxTarget;
        $pager->ajaxPage = true;
        return $pager->run();
    }

    protected function ajaxRun($obj)
    {


        $this->context->setJsStr(" function ajaxPage(url,obj,target)
    {
        target.html('<div style=\"text-align:center;margin:150px 0;font-size:18px;font-weight:bold;\">{$this->tips}<div>');
        $.get(url, function(html) {
           target.html(html);
        });
    }"); //通用分页js方法
        $ajaxPage = $this->getInput('ajaxPage');
        if ($ajaxPage == 1) {
            exit($obj->run());
        }
    }

    public function getLimit()
    {

        $this->page = !empty($this->page) ? $this->page : 1;
        return ($this->page - 1) * $this->pageSize . ",{$this->pageSize}";
    }

}

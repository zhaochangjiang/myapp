<?php

namespace framework\helpers;

use framework\helpers\ABasePager;

/*
   * To change this license header, choose License Headers in Project Properties. To change this template file, choose Tools | Templates and open the template in the editor.
   */

/**
 * 改进分页类
 *
 * @author heypigg
 */
class Pager extends ABasePager
{

    /**
     * 页面当前最大值超出了制定值，是否显示404页面 默认显示
     * @param type $count
     * @param type $setExtend404
     */
    public function __construct($count, $setExtend404 = true)
    {
        parent:: __construct($count, $setExtend404);
    }

    public function run()
    {
        $this->pageSize = $this->getPageSize();
        $this->params = $this->getParams();

        $len = $this->params['buttonNum'] ? $this->params['buttonNum'] : 8; // 数字分页个数
        $requestParam = $this->params['requestParam'] ? $this->params['requestParam'] : array_merge($_POST, $_GET);
        $localRefresh = $this->params['localRefresh'] ? $this->params['localRefresh'] : FALSE; // boolean 是否局部刷新？-如果不为false 则表示为局部刷新，传真为跳转链接JS函数的函数名称 loadcontent
        $showFirstLast = $this->params['showFirstLast'] ? $this->params['showFirstLast'] : $this->showFirstLast; // 是否显示首尾页
        $this->pagePrefix = $this->params['pagePrefix'] ? $this->params['pagePrefix'] : $this->pagePrefix; // 分页url前缀
        $buttonLabel = $this->params['buttonLabel'] ? $this->params['buttonLabel'] : $this->buttonLabel; // 分页按钮文字
        parent::init();

        if ($this->currentPage < 1)
            $this->currentPage = 1;
        $pageMax = ceil($this->totalNum / $this->pageSize);
        if ($this->currentPage > $pageMax)
            $pageNow = $pageMax;

        $pageNow = $this->currentPage;

        if ($pageMax <= 1) {
            // $pageString = '';
            // $showFirstLast ? $pageString = '<a title="首页">' . $buttonLabel['firstLabel'] . '</a>&nbsp;<a title="上一页">' . $buttonLabel['prevLabel'] . '</a>&nbsp;<a class="active" >1</a>&nbsp;<a title="下一页" >' . $buttonLabel['nextLabel'] . '</a>&nbsp;<a title="末页" >' . $buttonLabel['lastLabel'] . '</a>&nbsp;' : $pageString = '<a title="上一页">' . $buttonLabel['prevLabel'] . '</a>&nbsp;<a class="active" >1</a>&nbsp;<a title="下一页" >' . $buttonLabel['nextLabel'] . '</a>&nbsp;';
        } else {
            $k = floor($len / 2);
            if ($pageNow == 1) {
                $showFirstLast ? $pageString .= '<a title="首页" >' . $buttonLabel['firstLabel'] . '</a>&nbsp;<a title="上一页">' . $buttonLabel['prevLabel'] . '</a>&nbsp;' : $pageString .= '<a title="上一页">' . $buttonLabel['prevLabel'] . '</a>&nbsp;';
            } else {
                $showFirstLast ? $pageString .= '<a title="首页" ' . $this->getPageUrl(1, $requestParam, $localRefresh, $this->pagePrefix) . ' >' . $buttonLabel['firstLabel'] . '</a>&nbsp;<a title="上一页" ' . $this->getPageUrl($pageNow - 1, $requestParam, $localRefresh, $this->pagePrefix) . '>' . $buttonLabel['prevLabel'] . '</a>&nbsp;' : $pageString .= '<a title="上一页" ' . $this->getPageUrl($pageNow - 1, $requestParam, $localRefresh, $this->pagePrefix) . '>' . $buttonLabel['prevLabel'] . '</a>&nbsp;';
            }
            if ($pageMax <= $len || $pageNow <= $k) {
                $pagetotal = $pageMax <= $len ? $pageMax : $len;
                for ($i = 0; $i < $pagetotal; $i++) {
                    ($i + 1) == $pageNow ? $pageString .= '<a class="active">' . $pageNow . '</a>' : $pageString .= '<a ' . $this->getPageUrl($i + 1, $requestParam, $localRefresh, $this->pagePrefix) . '>' . ($i + 1) . '</a>';
                }
            } elseif ($pageNow > $pageMax - $k) {
                if ($pageNow > $pageMax - $k) {
                    for ($i = $pageMax - $len + 1; $i <= $pageMax; $i++)
                        $pageNow == $i ? $pageString .= '<a class="active" >' . $i . '</a>' : $pageString .= '<a ' . $this->getPageUrl($i, $requestParam, $localRefresh, $this->pagePrefix) . '>' . $i . '</a>';
                }
            } else {
                $from = $pageNow - $k;
                for ($i = $from; $i < ($from + $len); $i++) {
                    $pageNow == $i ? $pageString .= '<a class="active" >' . $i . '</a>' : $pageString .= '<a ' . $this->getPageUrl($i, $requestParam, $localRefresh, $this->pagePrefix) . '>' . $i . '</a>';
                }
            }
            if ($pageNow == $pageMax) {
                $showFirstLast ? $pageString .= '&nbsp;<a title="下一页">' . $buttonLabel['nextLabel'] . '</a>&nbsp;<a title="末页" >' . $buttonLabel['lastLabel'] . '</a>' : $pageString .= '<a title="下一页">' . $buttonLabel['nextLabel'] . '</a>';
            } else {
                $showFirstLast ? $pageString .= '&nbsp;<a title="下一页" ' . $this->getPageUrl($pageNow + 1, $requestParam, $localRefresh, $this->pagePrefix) . '>' . $buttonLabel['nextLabel'] . '</a>&nbsp;<a title="末页" ' . $this->getPageUrl($pageMax, $requestParam, $localRefresh, $this->pagePrefix) . ' >' . $buttonLabel['lastLabel'] . '</a>' : $pageString .= '<a title="下一页" ' . $this->getPageUrl($pageNow + 1, $requestParam, $localRefresh, $this->pagePrefix) . '>' . $buttonLabel['nextLabel'] . '</a>';
            }
        }
        return $pageString;
    }

}
  
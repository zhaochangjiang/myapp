<?php

namespace backend\common;

use framework\bin\helpers\ABasePager;

/**
 * 改进分页类
 *
 * @author karl.zhao<zhaocj2009@126.com>
 *
 */
class Pager extends ABasePager
{

    /**
     * 页面当前最大值超出了制定值，是否显示404页面 默认显示
     * @param int $count
     * @param boolean $setExtend404
     */
    public function __construct($count, $setExtend404 = true)
    {
        parent:: __construct($count, $setExtend404);
    }

    /**
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @Date: ${DATE}
     * @Time: ${TIME}
     *
     * @return array *
     */
    protected function getTagList()
    {
        return array(
            array(
                'label' => $this->buttonLabel['firstLabel'],
                'aTitle' => '首页',
            ),
            array(
                'label' => $this->buttonLabel['prevLabel'],
                'aTitle' => '上一页',
            ),
            array(
                'label' => 1,
            ),
            array(
                'label' => $this->buttonLabel['nextLabel'],
                'aTitle' => '下一页',
            ),
            array(
                'label' => $this->buttonLabel['lastLabel'],
                'aTitle' => '末页',
            )
        );
    }

    public function run()
    {

        $this->pageSize = $this->getPageSize();
        $this->params = $this->getParams();
        $len = $this->params['buttonNum'] ? $this->params['buttonNum'] : 8; // 数字分页个数
        $requestParam = $this->params['requestParam'] ? $this->params['requestParam'] : array_merge($_GET, $_POST);
        $localRefresh = $this->params['localRefresh'] ? $this->params['localRefresh'] : FALSE; // boolean 是否局部刷新？-如果不为false 则表示为局部刷新，传真为跳转链接JS函数的函数名称 loadcontent
        $showFirstLast = $this->params['showFirstLast'] ? $this->params['showFirstLast'] : $this->showFirstLast; // 是否显示首尾页
        $this->pagePrefix = $this->params['pagePrefix'] ? $this->params['pagePrefix'] : $this->pagePrefix; // 分页url前缀
        if ($this->params['buttonLabel']) {
            $this->buttonLabel = $this->params['buttonLabel'];
        }
        parent::init();
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }
        $pageMax = ceil($this->totalNum / $this->pageSize);
        if ($this->currentPage > $pageMax) {
            $pageNow = $pageMax;
        }

        $pageNow = $this->currentPage;
        $pageString = '';
        if ($pageMax > 1) {

            $k = floor($len / 2);
            if ($pageNow == 1) {
                $showFirstLast ? $pageString .= '<li><a title="首页" >' . $this->buttonLabel['firstLabel'] . '</a></li>&nbsp;<li><a title="上一页">' . $this->buttonLabel['prevLabel'] . '</a></li>&nbsp;' : $pageString .= '<li><a title="上一页">' . $this->buttonLabel['prevLabel'] . '</a></li>&nbsp;';
            } else {
                $showFirstLast ? $pageString .= '<li><a title="首页" ' . $this->getPageUrl(1, $requestParam, $localRefresh) . ' >' . $this->buttonLabel['firstLabel'] . '</a></li>&nbsp;<li><a title="上一页" ' . $this->getPageUrl($pageNow - 1, $requestParam, $localRefresh) . '>' . $this->buttonLabel['prevLabel'] . '</a></li>&nbsp;' : $pageString .= '<li><a title="上一页" ' . $this->getPageUrl($pageNow - 1, $requestParam, $localRefresh) . '>' . $this->buttonLabel['prevLabel'] . '</a></li>&nbsp;';
            }
            if ($pageMax <= $len || $pageNow <= $k) {
                $pagetotal = $pageMax <= $len ? $pageMax : $len;
                for ($i = 0; $i < $pagetotal; $i++) {
                    ($i + 1) == $pageNow ? $pageString .= '<li  class="active"><a>' . $pageNow . '</a></li>' : $pageString .= '<li><a ' . $this->getPageUrl($i + 1, $requestParam, $localRefresh) . '>' . ($i + 1) . '</a></li>';
                }
            } elseif ($pageNow > $pageMax - $k) {
                if ($pageNow > $pageMax - $k) {
                    for ($i = $pageMax - $len + 1; $i <= $pageMax; $i++)
                        $pageNow == $i ? $pageString .= '<li  class="active" ><a>' . $i . '</a></li>' : $pageString .= '<li><a ' . $this->getPageUrl($i, $requestParam, $localRefresh) . '>' . $i . '</a></li>';
                }
            } else {
                $from = $pageNow - $k;
                for ($i = $from; $i < ($from + $len); $i++) {
                    $pageNow == $i ? $pageString .= '<li  class="active"><a >' . $i . '</a></li>' : $pageString .= '<li><a ' . $this->getPageUrl($i, $requestParam, $localRefresh) . '>' . $i . '</a></li>';
                }
            }
            if ($pageNow == $pageMax) {
                $showFirstLast ? $pageString .= '&nbsp;<li><a title="下一页">' . $this->buttonLabel['nextLabel'] . '</a></li>&nbsp;<li><a title="末页" >' . $this->buttonLabel['lastLabel'] . '</a></li>' : $pageString .= '<li><a title="下一页">' . $this->buttonLabel['nextLabel'] . '</a></li>';
            } else {
                $showFirstLast ? $pageString .= '&nbsp;<li><a title="下一页" ' . $this->getPageUrl($pageNow + 1, $requestParam, $localRefresh) . '>' . $this->buttonLabel['nextLabel'] . '</a></li>&nbsp;<li><a title="末页" ' . $this->getPageUrl($pageMax, $requestParam, $localRefresh) . ' >' . $this->buttonLabel['lastLabel'] . '</a></li>' : $pageString .= '<li><a title="下一页" ' . $this->getPageUrl($pageNow + 1, $requestParam, $localRefresh) . '>' . $this->buttonLabel['nextLabel'] . '</a></li>';
            }
        }
        // xmp($pageString);

        return '<ul class="pagination pagination-sm no-margin pull-right">' . $pageString . '</ul>';
    }

    private function initATag($tagList)
    {
        $string = '';
        foreach ($tagList as $params) {
            $string .= '<li class="' . $params['liClass'] . '"><a class="' . $params['aClass'] . '" title="' . $params['aTitle'] . '">' . $params['label'] . '</a></li>';
        }
        return $string;
    }

}
  
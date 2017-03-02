<?php

namespace communal\common;

/**
 * Description of BootStrapFileUploadParams
 *
 * @author zhaocj
 */
class UtilsBootStrapFileUpload
{

    public $showUpload = false; //是否显示上传按钮
    public $showCaption = false; //是否显示删除按钮
    public $showRemove = false; //是否显示删除按钮
    public $browseClass = "btn btn-primary"; //
    public $fileType = "btn btn-primary"; //
    public $allowedFileExtensions = ['jpg',
        'jpeg',
        'png',
        'gif']; //
    public $language = 'zh';
    public $uploadUrl = '';
    public $initialPreviewShowDelete;
    public $success = null;
    public $initialPreview = null;
    public $initialPreviewConfig = null;

    /**
     * 批量设置预览图片属性
     * @param type $pictureViewLocateArray
     * @param type $pictureViewConfigArray
     */
    public function setInitialPreview($pictureViewLocateArray,
                                      $pictureViewConfigArray = null)
    {
        $this->initialPreview = $pictureViewLocateArray;
        if (!empty($pictureViewConfigArray)) {
            $this->initialPreviewConfig = $pictureViewConfigArray;
        }
    }

    /**
     * 设置预览图片属性
     * @param type $pictureViewLocate
     * @param type $pictureViewConfig
     */
    public function setInitialEveryPreview($pictureViewLocate,
                                           $pictureViewConfig = null)
    {
        $this->initialPreview[] = $pictureViewLocate;
        if (!empty($pictureViewConfig)) {
            $this->initialPreviewConfig[] = $pictureViewConfig;
        }
    }

    function getSuccess()
    {
        return $this->success;
    }

    function setSuccess($success)
    {
        $this->success = $success;
    }

//success: function (data, status) {
//                if (typeof (data.error) != 'undefined') {
//                    if (data.error) {
//                        //print error
//                        alert(data.error);
//                    } else {
//                        //clear
//                        $('#img img').attr('src', url + 'cache/' + data.msg);
//                    }
//                }
//            }
    function getInitialPreviewShowDelete()
    {
        return $this->initialPreviewShowDelete;
    }

    function setInitialPreviewShowDelete($initialPreviewShowDelete)
    {
        $this->initialPreviewShowDelete = $initialPreviewShowDelete;
    }

    function getUploadUrl()
    {
        return $this->uploadUrl;
    }

    function setUploadUrl($uploadUrl)
    {
        $this->uploadUrl = $uploadUrl;
    }

    function getShowUpload()
    {
        return $this->showUpload;
    }

    function getShowCaption()
    {
        return $this->showCaption;
    }

    function getShowRemove()
    {
        return $this->showRemove;
    }

    function getBrowseClass()
    {
        return $this->browseClass;
    }

    function getFileType()
    {
        return $this->fileType;
    }

    function getAllowedFileExtensions()
    {
        return $this->allowedFileExtensions;
    }

    function setShowUpload($showUpload)
    {
        $this->showUpload = $showUpload;
    }

    function setShowCaption($showCaption)
    {
        $this->showCaption = $showCaption;
    }

    function setShowRemove($showRemove)
    {
        $this->showRemove = $showRemove;
    }

    function setBrowseClass($browseClass)
    {
        $this->browseClass = $browseClass;
    }

    function setFileType($fileType)
    {
        $this->fileType = $fileType;
    }

    function setAllowedFileExtensions($allowedFileExtensions)
    {
        $this->allowedFileExtensions = $allowedFileExtensions;
    }

    function getLanguage()
    {
        return $this->language;
    }

    function setLanguage($language)
    {
        $this->language = $language;
    }

}
  
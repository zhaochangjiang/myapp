<?php

namespace communal\activeRecord\data;

/**
 * @author zhaocj
 */
class Picture
{

    private $picture_id;
    private $name;
    private $del_flag;
    private $picture_category_id;
    private $upload_time;
    private $suffix;

    function getPicture_id()
    {
        return $this->picture_id;
    }


    function getName()
    {
        return $this->name;
    }

    function getDel_flag()
    {
        return $this->del_flag;
    }

    function getPicture_category_id()
    {
        return $this->picture_category_id;
    }

    function getUpload_time()
    {
        return $this->upload_time;
    }

    function getSuffix()
    {
        return $this->suffix;
    }

    function setPicture_id(string $picture_id)
    {
        $this->picture_id = $picture_id;
    }

    function setName(string $name)
    {
        $this->name = $name;
    }

    function setDel_flag(int $del_flag)
    {
        $this->del_flag = $del_flag;
    }

    function setPicture_category_id(int $picture_category_id)
    {
        $this->picture_category_id = $picture_category_id;
    }

    function setUpload_time(int $upload_time)
    {
        $this->upload_time = $upload_time;
    }

    function setSuffix(string $suffix)
    {
        $this->suffix = $suffix;
    }

}
  
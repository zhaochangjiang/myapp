<?php
/**
 * Created by PhpStorm.
 * @Author: karl.zhao<zhaocj2009@hotmail.com>
 * @Date: 2017/3/15
 * @Time: 23:24
 */
namespace framework\bin\dataFormat;


class AReturn
{

    public $code = 200;//返回的状态码 (建议:尽量不要用0表示，因为PHP对于0 空字符串 null区分的问题)
    public $message = '';//中文描述
    public $data = null;//返回的数据

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }


    /**
     * 通过数组设置本类的属性值
     * @author karl.zhao<zhaocj2009@hotmail.com>
     * @param array $result
     * @return  void
     * @throws  RuntimeException
     */
    public function setResult($result)
    {
        foreach ($result as $key => $value) {
            $function = 'set' . ucfirst($key);
            if (method_exists($this, $function)) {
                $this->$function($value);
            } else {
                throw new RuntimeException("the Params is error is at line:" . __LINE__
                    . ',in file:' . __FILE__, FRAME_THROW_EXCEPTION);
            }
        }
        return;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


}
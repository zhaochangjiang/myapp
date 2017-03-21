<?php

namespace client\common;

use RuntimeException;

/**
 * Description of ClientResultData
 *
 * @author zhaocj
 */
class ResultClient
{

    public $code = 200;
    public $message = '';
    public $data = null;
    public $sessionId = '';

    /**
     * 通过数组设置本类的属性值
     * @param array $result
     * @return  void
     * @throws  \RuntimeException
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
    }

    public static function getInstance()
    {
        return new ClientResultData();
    }

    function getSessionId()
    {
        return $this->sessionId;
    }

    function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    function getCode()
    {

        return $this->code;
    }

    function getMessage()
    {
        return $this->message;
    }

    function getData()
    {
        return $this->data;
    }

    function setCode($code)
    {
        $this->code = $code;
    }

    function setMessage($message)
    {
        $this->message = $message;
    }

    function setData($data)
    {
        $this->data = $data;
    }


}
  
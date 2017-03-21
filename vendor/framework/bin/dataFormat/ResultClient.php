<?php

namespace framework\bin\dataFormat;

use RuntimeException;

/**
 * Description of ClientResultData
 *
 * @author zhaocj
 */
class ResultClient
{

    protected $code = 200;
    protected $message = '';
    protected $data = null;
    protected $sessionId = '';

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

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function getCode()
    {

        return $this->code;
    }

    function getMessage()
    {
        return $this->message;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setData($data)
    {
        $this->data = $data;
    }


}
  
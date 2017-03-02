<?php

namespace framework\bin;

use framework\bin\AHttpExceptionInterface;
use RuntimeException;

/**
 * 处理HTTP异常
 */
class AHttpException extends RuntimeException
    implements AHttpExceptionInterface
{

    private $statusCode;
    private $headers;

    public function __construct($statusCode, $message = null,
                                Exception $previous = null,
                                array $headers = array(), $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        parent:: __construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

}
  
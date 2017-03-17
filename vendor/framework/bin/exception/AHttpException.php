<?php

namespace framework\bin\exception;

use RuntimeException;

/**
 * 处理HTTP异常
 */
class AHttpException extends RuntimeException // implements AHttpExceptionInterface
{

    private $statusCode;
    private $headers;

    public function __construct($statusCode, $message = null, Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->setStatusCode($statusCode);
        $this->setHeaders($headers);
        parent:: __construct($message, $code, $previous);
    }

    /**
     * @param string $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
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
  
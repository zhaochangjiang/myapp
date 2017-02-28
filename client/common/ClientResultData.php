<?php

  namespace client\common;

  use ArrayObject;

  /**
   * Description of ClientResultData
   *
   * @author zhaocj
   */
  class ClientResultData
  {

      public $code      = 200;
      public $message   = '';
      public $data      = null;
      public $sessionid = '';

      public static function getInstance()
      {
          return new ClientResultData();
      }

      function getSessionid()
      {
          return $this->sessionid;
      }

      function setSessionid($sessionid)
      {
          $this->sessionid = $sessionid;
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

      /**
       * 
       * @param type $result
       */
      public function setResult($result)
      {
          foreach ($result as $key => $value)
          {
              //    xmp($key);
              $function = 'set' . ucfirst($key);
              $this->$function($value);
          }
      }

  }
  
<?php

  namespace communal\common;

  /**
   * Description of ClientResultData
   *
   * @author zhaocj
   */
  class UtilsResultData
  {

      public $code    = 200;
      public $message;
      public $data    = null;

      public static function getInstance()
      {
          return new self();
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
  
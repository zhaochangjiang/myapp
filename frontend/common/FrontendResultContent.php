<?php

  namespace frontend\common;

  use client\common\ClientResultData;

  /**
   * IFRAME页面输出结果格式
   */
  class FrontendResultContent extends ClientResultData
  {

      var $javascriptContent = '';
      var $notexit           = false;
      var $message           = '';
      private static $_instance;

      private function __construct()
      {
          
      }

      public static function getInstance()
      {
          if (self::$_instance === null)
          {
              self::$_instance = new self ( );
          }
          return self::$_instance;
      }

      public static function getInstanceAnother()
      {

          return new self ( );
      }

      /**
       *
       * @return the $status
       */
      public function getStatus()
      {
          return $this->status;
      }

      /**
       *
       * @return the $message
       */
      public function getMessage()
      {
          return $this->message;
      }

      /**
       *
       * @return the $notexit
       */
      public function getNotexit()
      {
          return $this->notexit;
      }

      /**
       *
       * @param boolean $notexit            
       */
      public function setNotexit($notexit)
      {
          $this->notexit = $notexit;
      }

      /**
       *
       * @param string $status            
       */
      public function setStatus($status)
      {
          $this->status = $status;
      }

      /**
       *
       * @param multitype: $message            
       */
      public function setMessage($message)
      {
          $this->message .= $message;
      }

      /**
       *
       * @return the $javascriptContent
       */
      public function getJavascriptContent()
      {
          return $this->javascriptContent;
      }

      /**
       *
       * @param string $javascriptContent            
       */
      public function setJavascriptContent($javascriptContent, $goto = '')
      {
          if (!empty($goto))
          {

              $js = "parent.location.href='$goto';";
          }
          $javascriptContent       = $javascriptContent . $js;
          $this->javascriptContent = "<script type=\"text/javascript\">{$javascriptContent}</script>";
      }

  }
  
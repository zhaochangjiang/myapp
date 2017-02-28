<?php

  namespace communal\common;

  use framework\App;

  /**
   * Description of ATools
   *
   * @author zhaocj
   */
  class UtilsCommunalTools
  {

      /**
       * 
       * @param type $namespace
       * @return type
       */
      public static function createGuid()
      {
          $data = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . time() . rand();
          return sha1($data);
      }

  }
  
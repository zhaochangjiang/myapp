<?php

  namespace communal\common;

  /**
   * Description of FormFormat
   *
   * @author zhaocj
   */
  class UtilsFormFormat
  {

      public static function open($action, $options = array())
      {
          $attributeString = '';
          isset($options['method']) ? '' : $options['method'] = 'post';
          isset($options['target']) ? '' : $options['target'] = 'iframe-s';
          foreach ((array) $options as $key => $value)
          {
              $attributeString.=" {$key}=\"{$value}\" ";
          }

          echo ' <form action="' . $action . '" ' . $attributeString . '><iframe style="display:' . (empty($options['iframeDisplay']) ? 'none' : 'block') . '" name="' . $options['target'] . '"></iframe>';
      }

      public static function close()
      {
          echo '</form>';
      }

  }
  
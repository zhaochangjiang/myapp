<?php

  namespace backend\modules\data\blocks\category;

  use framework\bin\Ablocker;

  /**
   * Description of CategorySearch
   *
   * @author zhaocj
   */
  class BlockSearch extends Ablocker
  {

      public function run()
      {
          $this->render();
      }

  }
  
<?php

namespace framework;

use framework\bin\base\ABaseApplication;


require(dirname(__FILE__) . '/bin/base/ABaseApplication.php');

/**
 * 框架入口
 * 目的是为了简化ABaseApplication 静态调用的时候直接可以App::xxx()
 *
 * @author heypigg
 */
class App extends ABaseApplication
{

}

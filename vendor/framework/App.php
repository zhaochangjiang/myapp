<?php

namespace framework;

use framework\bin\ABaseApplication;



/**
 * 框架入口
 * 目的是为了简化ABaseApplication 静态调用的时候直接可以App::xxx()
 *
 * @author heypigg
 */
require(dirname(__FILE__) . '/bin/ABaseApplication.php');

class App extends ABaseApplication
{
    
}

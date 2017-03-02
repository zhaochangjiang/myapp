<?php

use framework\App; ?>
<div class="row">
    <div class="col-md-8">
        <div class="main-error mb-xlg">
            <h2 class="error-code text-dark text-center text-semibold m-none">404 <i class="fa fa-file"></i></h2>
            <p class="error-explanation text-center">对不起你要查看的页面不存在!</p>
        </div>
    </div>
    <div class="col-md-4">
        <h4 class="text">您是不是想去如下页面?</h4>
        <ul class="nav nav-list primary">
            <li>
                <a href="<?php echo $this->createUrl("user/index", null, App::base()->params['domain']['userProfile']); ?>"><i
                        class="fa fa-caret-right text-dark"></i>&nbsp;用户中心</a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl("site/index"); ?>"><i class="fa fa-caret-right text-dark"></i>&nbsp;首页</a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl("help/index"); ?>"><i class="fa fa-caret-right text-dark"></i>&nbsp;
                    帮助页面</a>
            </li>
        </ul>
    </div>
</div>

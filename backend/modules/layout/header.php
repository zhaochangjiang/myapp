<?php
use framework\App;
?>
<header class="header">
    <a href="<?php echo $this->createUrl(array()); ?>" class="logo">
        <?php echo App::$app->parameters->sitename . ' 后台管理中心'; ?>
    </a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <?php
                $permitlist = $this->permitList['header'];
                unset($this->permitList['header']);

                //   stop($permitlist[0]);
                ?>
                <?php
                foreach ($permitlist as $key => $value):
                    echo '<li class="dropdown user user-menu ' . ($value['active'] ? ' open' : '') . '">';
                    $baseUrlParams = $this->getAdminModuleActionArray($value);
                    echo '<a href = "' . $this->createUrl($baseUrlParams) . '" class = "dropdown-toggle" >';
                    if (!empty($value['csscode'])) {
                        echo '<i class="glyphicon ' . $value['csscode'] . '"></i>';
                    }
                    ?>
                    <span><?php echo $value['name']; ?></span>
                    </a>
                    </li>
                    <?php
                endforeach;
                ?>

                <li class="dropdown user user-menu">
                    <a href="javacript:;" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo $this->data['session']['username'] ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="<?php echo $this->data['avater'] ?>" class="img-circle" alt="User Image"/>
                            <p>
                                <?php echo $this->data['session']['username'] ?>
                                <small><?php echo date('H:i'); ?></small>
                            </p>
                        </li>
                        <li class="user-body">
                            <div class="col-xs-12 text-center">
                                <a href="<?php echo $this->createUrl([]); ?>" target="_blank">首页</a>
                            </div>
                        </li>

                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="<?php
                                echo $this->createUrl(['User', 'setting'], [], App::$app->parameters->domain['userProfile']);
                                ?>" class="btn btn-default btn-flat">个人设置</a>
                            </div>
                            <div class="pull-right">
                                <a href="<?php echo $this->createUrl(['Passport', 'logout'], null, App::$app->parameters->domain['web']); ?>"
                                   class="btn btn-default btn-flat">退出</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>


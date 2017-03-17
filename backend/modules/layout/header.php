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

                <!--     
               <li class="dropdown messages-menu">
                   <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                       <i class="fa fa-envelope"></i>
                       <span class="label label-success">4</span>
                   </a>
                   <ul class="dropdown-menu">
                       <li class="header">You have 4 messages</li>
                       <li>
                           <ul class="menu">
                               <li>
                                   <a href="#">
                                       <div class="pull-left">
                                           <img src="source/img/avatar3.png" class="img-circle" alt="User Image"/>
                                       </div>
                                       <h4>
                                           Support Team
                                           <small><i class="fa fa-clock-o"></i> 5 mins</small>
                                       </h4>
                                       <p>Why not buy a new awesome theme?</p>
                                   </a>
                               </li>
                               <li>
                                   <a href="#">
                                       <div class="pull-left">
                                           <img src="source/img/avatar2.png" class="img-circle" alt="user image"/>
                                       </div>
                                       <h4>
                                           AdminLTE Design Team
                                           <small><i class="fa fa-clock-o"></i> 2 hours</small>
                                       </h4>
                                       <p>Why not buy a new awesome theme?</p>
                                   </a>
                               </li>
                               <li>
                                   <a href="#">
                                       <div class="pull-left">
                                           <img src="source/img/avatar.png" class="img-circle" alt="user image"/>
                                       </div>
                                       <h4>
                                           Developers
                                           <small><i class="fa fa-clock-o"></i> Today</small>
                                       </h4>
                                       <p>Why not buy a new awesome theme?</p>
                                   </a>
                               </li>
                               <li>
                                   <a href="#">
                                       <div class="pull-left">
                                           <img src="source/img/avatar2.png" class="img-circle" alt="user image"/>
                                       </div>
                                       <h4>
                                           Sales Department
                                           <small><i class="fa fa-clock-o"></i> Yesterday</small>
                                       </h4>
                                       <p>Why not buy a new awesome theme?</p>
                                   </a>
                               </li>
                               <li>
                                   <a href="#">
                                       <div class="pull-left">
                                           <img src="source/img/avatar.png" class="img-circle" alt="user image"/>
                                       </div>
                                       <h4>
                                           Reviewers
                                           <small><i class="fa fa-clock-o"></i> 2 days</small>
                                       </h4>
                                       <p>Why not buy a new awesome theme?</p>
                                   </a>
                               </li>
                           </ul>
                       </li>
                       <li class="footer"><a href="#">See All Messages</a></li>
                   </ul>
               </li>
              
                
                
                
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-warning"></i>
                        <span class="label label-warning">10</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 10 notifications</li>
                        <li>
                            <ul class="menu">
                                <li>
                                    <a href="#">
                                        <i class="ion ion-ios7-people info"></i> 5 new members joined today
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-warning danger"></i> Very long description here that may not fit into the page and may cause design problems
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users warning"></i> 5 new members joined
                                    </a>
                                </li>

                                <li>
                                    <a href="#">
                                        <i class="ion ion-ios7-cart success"></i> 25 sales made
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="ion ion-ios7-person danger"></i> You changed your username
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="footer"><a href="#">View all</a></li>
                    </ul>
                </li>
                
                
                
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-tasks"></i>
                        <span class="label label-danger">9</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 9 tasks</li>
                        <li>
                            <ul class="menu">
                                <li>
                                    <a href="#">
                                        <h3>
                                            Design some buttons
                                            <small class="pull-right">20%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                                <span class="sr-only">20% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <h3>
                                            Create a nice theme
                                            <small class="pull-right">40%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-green" style="width: 40%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                                <span class="sr-only">40% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <h3>
                                            Some task I need to do
                                            <small class="pull-right">60%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-red" style="width: 60%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                                <span class="sr-only">60% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <h3>
                                            Make beautiful transitions
                                            <small class="pull-right">80%</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-yellow" style="width: 80%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                                <span class="sr-only">80% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="footer">
                            <a href="#">View all tasks</a>
                        </li>
                    </ul>
                </li>
                -->
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
                                <a href="<?php echo $this->createUrl(); ?>" target="_blank">首页</a>
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


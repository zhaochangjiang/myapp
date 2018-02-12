<?php

use frontend\common\DataManager;

?>
<!doctype html>
<html class="fixed">
<head>
    <!-- Basic -->
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML5 Admin Template"/>
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <?php
    echo $this->getCssFile();
    echo $this->getJsFileBefore();
    ?>
    <!-- Web Fonts
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    -->
</head>
<body class="home blog">
<nav class="navbar navbar-default">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <h1><a class="navbar-brand" href="/">
                    <img src="<?php echo DataManager::getLogoUrl(); ?>">
                </a>
            </h1>
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <div class="">
                <ul class="nav navbar-nav navbar-right">
                    <li class="current_page_item"><a href="/" title="首页">首页</a></li>
                    <li class="page_item page-item-2"><a href="">示例页面</a></li>
                </ul>
            </div>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
<div class="clearfix"></div>
<div class="page-title-section">
    <div class="overlay">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="page-title"><h1>世界，您好！</h1></div>
                </div>
                <div class="col-md-6">
                    <ul class="page-breadcrumb">
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>

<?php $this->getTemplateContent(); ?>
<div class="clearfix"></div>
<div class="footer-copyright-section">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="footer-copyright">
                    No copyright information has been saved yet.
                </div>
            </div>
            <div class="col-md-4">
                <ul class="footer-contact-social">
                    <li class="facebook"><a href="#" target="_blank"><i class="fa fa-facebook"></i></a></li>
                    <li class="twitter"><a href="#" target="_blank"><i class="fa fa-twitter"></i></a></li>
                    <li class="linkedin"><a href="#" target="_blank"><i class="fa fa-linkedin"></i></a></li>
                    <li class="googleplus"><a href="#" target="_blank"><i class="fa fa-google-plus"></i></a></li>
                    <li class="skype"><a href="#" target="_blank"><i class="fa fa-skype"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- end: page -->
    <?php echo $this->getJsFileAfter(); ?>
</div>
</body>
</html>

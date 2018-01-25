<?php
use framework\App;
use framework\bin\utils\AUtils;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo App::$app->parameters->sitename . ' 后台管理中心' ?></title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <?php
    echo $this->getCssFile();
    echo $this->getJsFileBefore();
    ?>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body class="skin-blue">
<?php $this->loadViewCellCommon("header"); ?>

<div class="wrapper row-offcanvas row-offcanvas-left">
    <?php $this->loadViewCellCommon("left"); ?>
    <aside class="right-side">
        <section class="content-header">
            <h1>
                <?php echo $this->pageTitle; ?>
                <small><?php echo $this->pageSmallTitle; ?></small>
            </h1>
            <?php echo $this->getBreadcrumbs(); ?>
        </section>
        <section class="content">
            <?php echo $this->contentHtml; ?>
        </section>
    </aside>
</div>
<?php echo $this->getJsFileAfter(); ?>
</body>
</html>

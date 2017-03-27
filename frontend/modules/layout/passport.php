<?php

use frontend\common\DataManager;
use framework\App;

?>
<!doctype html>
<html class="fixed">
<head>
    <!-- Basic -->
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML5 Admin Template"/>
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <?php
    echo $this->getCssFile();
    echo $this->getJsFileBefore();
    ?>
    <!--   <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light"
          rel="stylesheet" type="text/css">
           -->
</head>
<body>
<section class="body-sign">
    <div class="center-sign">

        <?php $this->getTemplateContent(); ?>
        <p class="text-center text-muted mt-md mb-md"><?php echo DataManager::getCopyRight(); ?></p>
    </div>
</section>
<?php echo $this->getJsFileAfter(); ?>
</body>
</html>

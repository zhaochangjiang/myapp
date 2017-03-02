<!doctype html>
<html class="fixed">
<head>

    <!-- Basic -->
    <meta charset="UTF-8">

    <meta name="keywords" content="HTML5 Admin Template"/>
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <?php
    echo $this->getCssFile();
    echo $this->getJsFileBefore();
    ?>
</head>
<body>
<!-- start: page -->
<section class="body-error error-outside">
    <div class="center-error">

        <div class="error-header">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-8">
                            <a href="/" class="logo">
                                <img src="source/images/logo.png" height="54" alt="Porto Admin"/>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <form class="form">
                                <div class="input-group input-search">
                                    <input type="text" class="form-control" name="q" id="q" placeholder="Search...">
                                            <span class="input-group-btn">
                                                <button class="btn btn-default" type="submit"><i
                                                        class="fa fa-search"></i></button>
                                            </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->getTemplateContent(); ?>
    </div>
</section>
<!-- end: page -->
<?php echo $this->getJsFileAfter(); ?>
</body>
</html>
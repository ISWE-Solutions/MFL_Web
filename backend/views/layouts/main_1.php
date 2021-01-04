<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use backend\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" sizes="96x96" href="<?= Url::to('@web/img/coa.png') ?>">
        <?php $this->registerCsrfMetaTags() ?>
        <title>MFL | <?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>

    </head>
    <body class="hold-transition layout-top-nav">
        <?php $this->beginBody() ?>
        <div class="wrapper">

            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand-md navbar-light navbar-green " style="background: #158239">
                <div class="container">
                    
                    <a class="navbar-brand" href="" target="blank">
                        <?=
                        Html::img('@web/img/coa.png', ["class" => "brand-image",
                            'style' => 'opacity: .9']);
                        ?>
                        <span class="brand-text text-white font-weight-light">MOH Master Facility List Administration</span>
                    </a>
                    <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <!-- Right navbar links -->
                    <ul class="navbar-nav order-1 order-md-3 navbar-no-expand ml-auto">
                        <li class="nav-item">
                            <a  href="https://www.moh.gov.zm/" target="blank" class="nav-link text-white">Ministry of Health Home</a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <div class="content-header">
                    <div class="container">

                    </div><!-- /.container-fluid -->
                </div>
                <!-- Main content -->
                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">&nbsp;</div>
                            <div class="col-lg-3">

                            </div>
                            <?= $content ?>
                            <div class="col-lg-3">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- /.navbar -->
            <!-- Main Footer -->
             <footer style="background: #158239" class="main-footer navbar-light navbar-green brand-text text-white text-md font-weight-light">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-4">
                                <h2 class="lead"><b>Contact us</b></h2>
                                <ul class="ml-4 mb-0 fa-ul text-white ">
                                    <li class="small"><span class="fa-li"><i class="fas fa-lg fa-home"></i></span> Ndeke House, Longacres, Lusaka</li>
                                    <li class="small"><span class="fa-li"><i class="fas fa-lg fa-envelope"></i></span> info@moh.gov.zm</li>
                                    <li class="small"><span class="fa-li"><i class="fas fa-lg fa-phone"></i></span> +260 211 253757</li>
                                </ul>
                            </div>
                            <div class="col-4">
                                <h2 class="lead"><b>Important links</b></h2>
                                <ul class="ml-4 mb-0 fa-ul text-white">
                                    <li class="small"><a class="text-white" href="https://www.moh.gov.zm/" target="blank">Ministry of Health</a></li>
                                    <li class="small"><a class="text-white" href="https://www.zicta.zm" target="blank">ZICTA</a></li>
                                    <li class="small"><a class="text-white" href="https://www.szi.gov.zm/" target="blank">Smart Zambia</a></li>
                                </ul>
                            </div>
                            <div class="col-4">
                                <h2 class="lead"><b>Social media</b></h2>
                                <div class="mt-4 product-share ">
                                    <a href="https://facebook.com/mohzambia/" class="text-white">
                                        <i class="fab fa-facebook-square fa-2x"></i>
                                    </a>
                                    <a href="https://twitter.com/moh_zambia" class="text-white">
                                        <i class="fab fa-twitter-square fa-2x"></i>
                                    </a>
                                    <a href="www.youtube.com/" class="text-white">
                                        <i class="fab fa-youtube-square fa-2x"></i>
                                    </a>
                                    <a href="https://github.com/MOH-Zambia/MFL" class="text-white">
                                        <i class="fab fa-github-square fa-2x"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <hr class="dotted short">
                    </div>
                    <div class="col-lg-12 text-center text-sm">
                        Copyright &copy; <?= date("Y") ?> - Master Facility List-
                        <a class="text-white" href="https://www.moh.gov.zm/" target="blank">MoH</a>. All rights reserved.
                    </div>
                </div>

            </footer>
        </div>

        <?php $this->endBody() ?>
        <script>
            var myArrSuccess = [<?php
        $flashMessage = Yii::$app->session->getFlash('success');
        if ($flashMessage) {
            echo '"' . $flashMessage . '",';
        }
        ?>];
            for (var i = 0; i < myArrSuccess.length; i++) {
                $.notify(myArrSuccess[i], {
                    type: 'success',
                    offset: 100,
                    allow_dismiss: true,
                    newest_on_top: true,
                    timer: 5000,
                    placement: {from: 'top', align: 'right'}
                });
            }
            var myArrError = [<?php
        $flashMessage = Yii::$app->session->getFlash('error');
        if ($flashMessage) {
            echo '"' . $flashMessage . '",';
        }
        ?>];
            for (var j = 0; j < myArrError.length; j++) {
                $.notify(myArrError[j], {
                    type: 'danger',
                    offset: 100,
                    allow_dismiss: true,
                    newest_on_top: true,
                    timer: 5000,
                    placement: {from: 'top', align: 'right'}
                });
            }
        </script>

    </body>
</html>
<?php $this->endPage() ?>

<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use backend\assets\AppAsset;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Url;
use backend\models\User;

AppAsset::register($this);
$session = Yii::$app->session;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" sizes="96x96" href="<?= Url::to('@web/img/coas.png') ?>">
        <?php $this->registerCsrfMetaTags() ?>
        <title>MFL | <?= Html::encode($this->title) ?></title>

        <!-- Leaflet kernel -->
        <link  href="<?= Url::to('@web/leaflet/leaflet.css') ?>" rel="stylesheet" />
        <script src="<?= Url::to('@web/leaflet/leaflet-src.js') ?>"></script>

        <!-- Draw & Snap plugins -->
        <link  href="<?= Url::to('@web/leaflet/leaflet.draw.css') ?>" rel="stylesheet" />
        <script src="<?= Url::to('@web/leaflet/leaflet.draw-src.js') ?>"></script>
        <script src="<?= Url::to('@web/leaflet/leaflet.geometryutil.js') ?>"></script>
        <script src="<?= Url::to('@web/leaflet/leaflet.snap.js') ?>"></script>

        <!-- Plugins to import GPX files for test purpose -->
        <script src="<?= Url::to('@web/leaflet/togeojson.js') ?>"></script>
        <script src="<?= Url::to('@web/leaflet/leaflet.filelayer.js') ?>"></script>

        <!-- This plugin -->
        <script src="<?= Url::to('@web/leaflet/Control.Draw.Plus.js') ?>"></script>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed  sidebar-collapse text-sm">
        <?php $this->beginBody() ?>
        <div class="wrapper">
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-green navbar-light" style="background: #158239">
                <ul class="navbar-nav">
                    <li class="nav-item ">
                        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars text-white"></i></a>
                    </li>
                    <li class="nav-item">
                        <a  href="https://www.moh.gov.zm/" target="blank" class="nav-link text-white">Ministry of Health Home</a>
                    </li>
                </ul>
                <!-- Right navbar links -->
                <ul class="navbar-nav ml-auto">

                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu" >

                        <a href="#" class="dropdown-toggle text-white" data-toggle="dropdown">
                            <img src="<?= Url::to('@web/img/icon.png') ?>" class="user-image" alt="User Image">
                            <span class="hidden-xs"> <?= $session['user'] ?> </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" style="background: #158239">
                            <!-- User image -->
                            <li class="user-header text-white">
                                <img src="<?= Url::to('@web/img/icon.png') ?>" class="img-circle" alt="User Image">
                                <p>
                                    <small> <?= $session['user'] ?> - <?= $session['role'] ?> </small>
                                    <small>Member since <?= date('M Y', $session['created_at']) ?></small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div>
                                    <?= Html::a('<i class="fas fa-user-circle"></i> My Profile', ['users/profile', 'id' => Yii::$app->user->identity->id], ['class' => "float-left btn btn-default btn-flat"]); ?>

                                    <a class="float-right btn btn-default btn-flat" href="#" data-toggle="modal" data-target="#logoutModal">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>

                            </li>

                        </ul>
                    </li>

                </ul>
            </nav>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            <aside class="main-sidebar sidebar-light-blue elevation-3" >
                <!-- Brand Logo -->
                <a style="background: #158239" class="brand-link" href="https://www.moh.gov.zm/" target="blank">
                    <?=
                    Html::img('@web/img/coas.png', ["class" => "brand-image",
                        'style' => 'opacity: .9;width:40px;height:40px;']);
                    ?>
                    <span class="brand-text text-white font-weight-light">MFL</span>
                </a>


                <!-- Sidebar -->
                <div class="sidebar">
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                        <div class="info text-black-50">
                            NAVIGATION MENU
                        </div>
                    </div>

                    <!-- Sidebar Menu -->
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <!-- Add icons to the links using the .nav-icon class
                                 with font-awesome or any other icon font library -->
                            <?php
                            echo ' <li class="nav-item">';
                            if (Yii::$app->controller->id == "home") {
                                echo Html::a(' <i class="fas fa-home nav-icon"></i> 
                                    <p>Dashboard</p>', ['/home/home'], ["class" => "nav-link active"]);
                            } else {
                                echo Html::a(' <i class="nav-icon fas fa-home"></i> '
                                        . '<p>Dashboard</p>', ['/home/home'], ["class" => "nav-link"]);
                            }
                            echo '</li>';
                            ?>
                            <!-------------------------------FACILITY MANAGEMENT STARTS----------------------->
                            <?php
                            if (User::userIsAllowedTo("Manage facilities") ||
                                    User::userIsAllowedTo("View facilities")) {
                                if (Yii::$app->controller->id == "facilities") {
                                    echo '<li class="nav-item has-treeview menu-open">'
                                    . ' <a href="#" class="nav-link active">';
                                } else {
                                    echo '<li class="nav-item has-treeview">'
                                    . '<a href="#" class="nav-link">';
                                }
                                ?>
                                <i class="nav-icon fas fa-building"></i>
                                <p>
                                    Facility management
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <?php
                                    if (User::userIsAllowedTo("Manage facilities") ||
                                            User::userIsAllowedTo("View facilities")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "facilities" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "view" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Facilities</p>', ['/facilities/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Facilities</p>', ['/facilities/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    ?>

                                </ul>
                                </li>
                            <?php } ?>
                            <!-------------------------------FACILITY MANAGEMENT ENDS------------------------->
                            <!-------------------------------RATING MANAGEMENT STARTS----------------------->
                            <?php
                            if (User::userIsAllowedTo("Manage Facility rate types") ||
                                    User::userIsAllowedTo("View facility ratings")) {
                                if (Yii::$app->controller->id == "facility-rate-types" ||
                                        Yii::$app->controller->id == "facility-ratings") {
                                    echo '<li class="nav-item has-treeview menu-open">'
                                    . ' <a href="#" class="nav-link active">';
                                } else {
                                    echo '<li class="nav-item has-treeview">'
                                    . '<a href="#" class="nav-link">';
                                }
                                ?>
                                <i class="nav-icon fas fa-star"></i>
                                <p>
                                    Facility Ratings
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <?php
                                    if (User::userIsAllowedTo("Manage Facility rate types")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "facility-rate-types" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Rate types</p>', ['/facility-rate-types/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Rate types</p>', ['/facility-rate-types/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("View facility ratings")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "facility-ratings" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Ratings</p>', ['facility-ratings/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Ratings</p>', ['facility-ratings/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    ?>

                                </ul>
                                </li>
                            <?php } ?>
                            <!-------------------------------RATING MANAGEMENT ENDS------------------------->
                            <!-------------------------------USER MANAGEMENT STARTS----------------------->
                            <?php
                            if (User::userIsAllowedTo("Manage Users") || User::userIsAllowedTo("View Users") ||
                                    User::userIsAllowedTo("View profile") ||
                                    User::userIsAllowedTo("Manage api users") || User::userIsAllowedTo("View api users") ||
                                    User::userIsAllowedTo("Manage Roles") || User::userIsAllowedTo("View Roles")) {
                                if (
                                        Yii::$app->controller->id == "users" ||
                                        Yii::$app->controller->id == "api-users" ||
                                        Yii::$app->controller->id == "role") {
                                    echo '<li class="nav-item has-treeview menu-open">'
                                    . ' <a href="#" class="nav-link active">';
                                } else {
                                    echo '<li class="nav-item has-treeview">'
                                    . '<a href="#" class="nav-link">';
                                }
                                ?>
                                <i class="nav-icon fas fa-users"></i>
                                <p>
                                    User management
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <?php
                                    if (User::userIsAllowedTo("View profile")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "users" &&
                                                (Yii::$app->controller->action->id == "profile")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>My Profile</p>', ['/users/profile', 'id' => Yii::$app->user->identity->id], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>My Profile</p>', ['/users/profile', 'id' => Yii::$app->user->identity->id], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }

                                    if (User::userIsAllowedTo("Manage Roles") || User::userIsAllowedTo("View Roles")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "role" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "view" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Roles</p>', ['/role/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Roles</p>', ['/role/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage Users") || User::userIsAllowedTo("View Users")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "users" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "view" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "profile" ||
                                                Yii::$app->controller->action->id == "update")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Users</p>', ['users/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Users</p>', ['users/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage api users") || User::userIsAllowedTo("View api users")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "api-users" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "view" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "profile" ||
                                                Yii::$app->controller->action->id == "update")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>API Users</p>', ['api-users/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>API Users</p>', ['api-users/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    ?>

                                </ul>
                                </li>
                            <?php } ?>
                            <!-------------------------------USER MANAGEMENT ENDS------------------------->
                            <!-------------------------------MFL CONFIGS STARTS--------------------------->
                            <?php
                            if (User::userIsAllowedTo("Manage facility types") ||
                                    User::userIsAllowedTo("Manage MFL services") ||
                                    User::userIsAllowedTo("Manage MFL operating hours") ||
                                    User::userIsAllowedTo("Manage MFL operation status") ||
//                                    User::userIsAllowedTo("Manage MFL equipments") ||
//                                    User::userIsAllowedTo("Manage MFL administrative units") ||
//                                    User::userIsAllowedTo("Manage MFL infrastructure") ||
//                                    User::userIsAllowedTo("Manage MFL lab levels") ||
                                    User::userIsAllowedTo("Manage facility ownerships") //||
                            ) {
                                if (Yii::$app->controller->id == "facilitytype" ||
                                        Yii::$app->controller->id == "facility-servicecategory" ||
                                        Yii::$app->controller->id == "facility-service" ||
                                        Yii::$app->controller->id == "operatinghours" ||
//                                        Yii::$app->controller->id == "equipment" ||
//                                        Yii::$app->controller->id == "administrative-unit" ||
//                                        Yii::$app->controller->id == "infrastructure" ||
//                                        Yii::$app->controller->id == "lab-level" ||
                                        Yii::$app->controller->id == "operationstatus" ||
                                        Yii::$app->controller->id == "facility-ownership"
                                ) {
                                    echo '<li class="nav-item has-treeview menu-open">'
                                    . ' <a href="#" class="nav-link active">';
                                } else {
                                    echo '<li class="nav-item has-treeview">'
                                    . '<a href="#" class="nav-link">';
                                }
                                ?>
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    MFL Configs
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <?php
                                    if (User::userIsAllowedTo("Manage facility types")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "facilitytype" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Facility types</p>', ['/facilitytype/index',], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Facility types</p>', ['/facilitytype/index',], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage facility ownerships")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "facility-ownership" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Facility ownership</p>', ['/facility-ownership/index',], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Facility ownership</p>', ['/facility-ownership/index',], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage MFL services")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "facility-servicecategory" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Service Areas</p>', ['/facility-servicecategory/index',], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Service Areas</p>', ['/facility-servicecategory/index',], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage MFL services")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "facility-service" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Services</p>', ['/facility-service/index',], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Services</p>', ['/facility-service/index',], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage MFL operating hours")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "operatinghours" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Operating hours</p>', ['/operatinghours/index',], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Operating hours</p>', ['/operatinghours/index',], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage MFL operation status")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "operationstatus" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Operation status</p>', ['/operationstatus/index',], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Operation status</p>', ['/operationstatus/index',], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
//                                    if (User::userIsAllowedTo("Manage MFL equipments")) {
//                                        echo '   <li class="nav-item">';
//                                        if (Yii::$app->controller->id == "equipment" &&
//                                                (Yii::$app->controller->action->id == "index")) {
//                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Equipments</p>', ['/equipment/index',], ["class" => "nav-link active"]);
//                                        } else {
//                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Equipments</p>', ['/equipment/index',], ["class" => "nav-link"]);
//                                        }
//                                        echo '</li>';
//                                    }
//                                    if (User::userIsAllowedTo("Manage MFL administrative units")) {
//                                        echo '   <li class="nav-item">';
//                                        if (Yii::$app->controller->id == "administrative-unit" &&
//                                                (Yii::$app->controller->action->id == "index")) {
//                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Administrative units</p>', ['/administrative-unit/index',], ["class" => "nav-link active"]);
//                                        } else {
//                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Administrative units</p>', ['/administrative-unit/index',], ["class" => "nav-link"]);
//                                        }
//                                        echo '</li>';
//                                    }
//                                    if (User::userIsAllowedTo("Manage MFL infrastructure")) {
//                                        echo '   <li class="nav-item">';
//                                        if (Yii::$app->controller->id == "infrastructure" &&
//                                                (Yii::$app->controller->action->id == "index")) {
//                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Infrastructure</p>', ['/infrastructure/index',], ["class" => "nav-link active"]);
//                                        } else {
//                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Infrastructure</p>', ['/infrastructure/index',], ["class" => "nav-link"]);
//                                        }
//                                        echo '</li>';
//                                    }
//                                    if (User::userIsAllowedTo("Manage MFL lab levels")) {
//                                        echo '<li class="nav-item">';
//                                        if (Yii::$app->controller->id == "lab-level" &&
//                                                (Yii::$app->controller->action->id == "index")) {
//                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Laboratory levels</p>', ['/lab-level/index',], ["class" => "nav-link active"]);
//                                        } else {
//                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Laboratory levels</p>', ['/lab-level/index',], ["class" => "nav-link"]);
//                                        }
//                                        echo '</li>';
//                                    }
                                    ?>

                                </ul>
                                </li>
                            <?php } ?>
                            <!-------------------------------MFL CONFIGS ENDS--------------------------->
                            <!-------------------------------GEOGRAPHY STARTS--------------------------->
                            <?php
                            if (User::userIsAllowedTo("Manage provinces") ||
                                    User::userIsAllowedTo("Manage districts") ||
                                    User::userIsAllowedTo("Manage wards") ||
                                    User::userIsAllowedTo("Manage district types") ||
                                    User::userIsAllowedTo("Manage constituencies") ||
                                    User::userIsAllowedTo("Manage zones") ||
                                    User::userIsAllowedTo("Manage location types")) {
                                if (Yii::$app->controller->id == "provinces" ||
                                        Yii::$app->controller->id == "districts" ||
                                        Yii::$app->controller->id == "district-type" ||
                                        Yii::$app->controller->id == "location-type" ||
                                        Yii::$app->controller->id == "wards" ||
                                        Yii::$app->controller->id == "zones" ||
                                        Yii::$app->controller->id == "constituencies") {
                                    echo '<li class="nav-item has-treeview menu-open">'
                                    . ' <a href="#" class="nav-link active">';
                                } else {
                                    echo '<li class="nav-item has-treeview">'
                                    . '<a href="#" class="nav-link">';
                                }
                                ?>
                                <i class="nav-icon fas fa-map-marker"></i>
                                <p>
                                    Geography
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <?php
                                    if (User::userIsAllowedTo("Manage provinces")) {
                                        echo '<li class="nav-item">';
                                        if (Yii::$app->controller->id == "provinces" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Provinces</p>', ['/provinces/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Provinces</p>', ['/provinces/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }

                                    if (User::userIsAllowedTo("Manage districts")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "districts" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Districts</p>', ['/districts/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Districts</p>', ['/districts/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage constituencies")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "constituencies" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Constituencies</p>', ['/constituencies/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Constituencies</p>', ['/constituencies/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage wards")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "wards" &&
                                                (
                                                Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Wards</p>', ['/wards/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Wards</p>', ['/wards/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage zones")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "zones" &&
                                                (
                                                Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Zones</p>', ['/zones/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Zones</p>', ['/zones/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }


                                    if (User::userIsAllowedTo("Manage district types")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "district-type" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>District types</p>', ['district-type/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>District types</p>', ['district-type/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage location types")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "location-type" &&
                                                (Yii::$app->controller->action->id == "index")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Location types</p>', ['location-type/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Location types</p>', ['location-type/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    ?>

                                </ul>
                                </li>
                            <?php } ?>
                            <!-------------------------------GEOGRAPHY ENDS--------------------------->
                            <!-------------------------------NIDS STARTS--------------------------->
                            <?php
                            if (User::userIsAllowedTo("Manage indicator groups") ||
                                    User::userIsAllowedTo("View indicator groups") ||
                                    User::userIsAllowedTo("Manage indicators") ||
                                    User::userIsAllowedTo("View indicators") ||
                                    User::userIsAllowedTo("Manage data elements") ||
                                    User::userIsAllowedTo("View data elements") ||
                                    User::userIsAllowedTo("Manage nids validation rules") ||
                                    User::userIsAllowedTo("View nids validation rules") ||
                                    User::userIsAllowedTo("Manage data element groups") ||
                                    User::userIsAllowedTo("View data element groups") ||
                                    User::userIsAllowedTo("Manage validation rule operators") ||
                                    User::userIsAllowedTo("View validation rule operators")
                            ) {
                                if (Yii::$app->controller->id == "indicator-group" ||
                                        Yii::$app->controller->id == "indicators" ||
                                        Yii::$app->controller->id == "data-element-group" ||
                                        Yii::$app->controller->id == "data-elements" ||
                                        Yii::$app->controller->id == "validation-rule-operator" ||
                                        Yii::$app->controller->id == "validation-rules") {
                                    echo '<li class="nav-item has-treeview menu-open">'
                                    . ' <a href="#" class="nav-link active">';
                                } else {
                                    echo '<li class="nav-item has-treeview">'
                                    . '<a href="#" class="nav-link">';
                                }
                                ?>
                                <i class="nav-icon fas fa-indent"></i>
                                <p>
                                    NIDS Management
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <?php
                                    if (User::userIsAllowedTo("Manage indicator groups") ||
                                            User::userIsAllowedTo('View indicator groups')) {
                                        echo '<li class="nav-item">';
                                        if (Yii::$app->controller->id == "indicator-group" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Indicator groups</p>', ['/indicator-group/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Indicator group</p>', ['/indicator-group/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }

                                    if (User::userIsAllowedTo("Manage indicators") ||
                                            User::userIsAllowedTo("View indicators")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "indicators" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Indicators</p>', ['/indicators/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Indicators</p>', ['/indicators/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage data element groups") ||
                                            User::userIsAllowedTo("View data element groups")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "data-element-group" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Data element groups</p>', ['/data-element-group/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Data element groups</p>', ['/data-element-group/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage data elements") ||
                                            User::userIsAllowedTo("View data elements")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "data-elements" &&
                                                (
                                                Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Data elements</p>', ['/data-elements/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Data elements</p>', ['/data-elements/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }


                                    if (User::userIsAllowedTo("Manage validation rule operators") ||
                                            User::userIsAllowedTo("View validation rule operators")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "validation-rule-operator" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Validation rule operators</p>', ['validation-rule-operator/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Validation rule operators</p>', ['validation-rule-operator/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    if (User::userIsAllowedTo("Manage nids validation rules") ||
                                            User::userIsAllowedTo("View nids validation rules")) {
                                        echo '   <li class="nav-item">';
                                        if (Yii::$app->controller->id == "validation-rules" &&
                                                (Yii::$app->controller->action->id == "index" ||
                                                Yii::$app->controller->action->id == "create" ||
                                                Yii::$app->controller->action->id == "update" ||
                                                Yii::$app->controller->action->id == "view")) {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Validation rules</p>', ['validation-rules/index'], ["class" => "nav-link active"]);
                                        } else {
                                            echo Html::a('<i class="far fa-circle nav-icon"></i> <p>Validation rules</p>', ['validation-rules/index'], ["class" => "nav-link"]);
                                        }
                                        echo '</li>';
                                    }
                                    ?>

                                </ul>
                                </li>
                            <?php } ?>
                            <!-------------------------------NIDS ENDS-------------------------------->
                            <!-------------------------------AUDIT TRAIL STARTS----------------------->
                            <li class="nav-item">
                                <?php
                                // if (User::userIsAllowedTo("Verify benefit claimer") || User::userIsAllowedTo("View Users") ||
                                //User::userIsAllowedTo("Manage Roles") || User::userIsAllowedTo("View Roles")) {
                                if (User::userIsAllowedTo("View audit trail logs")) {
                                    if (Yii::$app->controller->id == "audit-trail") {
                                        echo Html::a('<i class="fas fa-history nav-icon"></i> '
                                                . '<p>Audit logs</p>', ['audit-trail/index'], ["class" => "nav-link active"]);
                                    } else {
                                        echo Html::a('<i class="fas fa-history nav-icon"></i> '
                                                . '<p>Audit logs</p>', ['audit-trail/index'], ["class" => "nav-link"]);
                                    }
                                }
                                ?>
                            </li>
                            <!-------------------------------AUDIT TRAIL ENDS------------------------->
                        </ul>
                    </nav>
                    <!-- /.sidebar-menu -->
                </div>
                <!-- /.sidebar -->
            </aside>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">

                            </div><!-- /.col -->
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <?=
                                    Breadcrumbs::widget([
                                        'homeLink' => ['label' => 'Home',
                                            'url' => Yii::$app->getHomeUrl() . 'home/home'],
                                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                                    ])
                                    ?>
                                </ol>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.container-fluid -->
                </div>
                <!-- /.content-header -->

                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <?= $content ?>
                        <!-- /.row -->
                    </div><!--/. container-fluid -->
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->

            <div class="modal fade" id="logoutModal">
                <div class="modal-dialog ">
                    <div class="modal-content card-success card-outline">
                        <div class="modal-header">
                            <h4 class="modal-title">Ready to end your session?</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Select "Logout" below if you are ready to end your current session.</p>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancel</button>
                            <?=
                            Html::a('<span>Logout</span>', ['site/logout'], ['data' => ['method' => 'POST'], 'id' => 'logout',
                                'class' => 'btn btn-primary btn-sm'])
                            ?>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
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
                    offset: 60,
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
                    offset: 60,
                    allow_dismiss: true,
                    newest_on_top: true,
                    timer: 5000,
                    placement: {from: 'top', align: 'right'}
                });
            }
            
                var myArrWarning = [<?php
        $flashMessage = Yii::$app->session->getFlash('warning');
        if ($flashMessage) {
            echo '"' . $flashMessage . '",';
        }
        ?>];
                for (var j = 0; j < myArrWarning.length; j++) {
                    $.notify(myArrWarning[j], {
                        type: 'warning',
                        offset: 60,
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

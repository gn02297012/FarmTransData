<?php
$path = "/{$this->Html->request->controller}/{$this->Html->request->action}";
$menuItems = array(
    '/diagrams/search' => '價格查詢',
    '/diagrams/partition' => '組成圖',
    '/diagrams/line' => '價格線圖',
    '/diagrams/dashboard' => '市場圖',
);
?>

<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>農產品交易行情資料視覺化</title>
        <?php
        echo $this->Html->meta('icon');
        echo $this->fetch('meta');
        echo $this->Html->css(array(
            'bootstrap.min',
        ));
        echo $this->Html->script(array('jquery-1.10.2'));
        echo $this->Html->script(array(
            'angular.min',
            'd3.v3.min',
            'bootstrap-tooltip',
            'bootstrap.min',
            'public'
        ));
        ?>
        <style>
            body {
                /*                margin: auto;
                                position: relative;
                                width: 960px;
                                min-height: 900px;*/
            }

            .controlForm {
                position: absolute;
                right: 10px;
                top: 10px;
            }

            .svgSection, svg {
                /*border: 1px solid;*/
            }
        </style>
    </head>
    <body ng-app>
        <div class="container">
            <div class="row">
                <nav class="navbar navbar-default" role="navigation" style="/*display: none;*/">
                    <div class="container-fluid">
                        <!-- Brand and toggle get grouped for better mobile display -->
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <a class="navbar-brand" href="#">農產品交易行情資料視覺化</a>
                        </div>

                        <!-- Collect the nav links, forms, and other content for toggling -->
                        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                            <ul class="nav navbar-nav">
                                <?php
                                foreach ($menuItems as $key => &$value) {
                                    echo $this->Html->tag('li', $this->Html->link($value, $key)
                                            , array('class' => (strcmp($path, $key) === 0 ? 'active' : '')));
                                }
                                ?>
                            </ul>
                        </div><!-- /.navbar-collapse -->
                    </div><!-- /.container-fluid -->
                </nav>
            </div>
            <div class="row" id="mainContent">
                <div class="col-xs-12">
                    <?php echo $this->Session->flash(); ?>

                    <?php echo $this->fetch('content'); ?>
                    <?php //echo $this->element('sql_dump'); ?>
                </div>
            </div>
        </div>
    </body>
</html>

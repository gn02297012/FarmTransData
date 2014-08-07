<?php
$path = "/{$this->Html->request->controller}/{$this->Html->request->action}";
$menuItems = array(
    '/diagrams/search' => '價格查詢',
    '/diagrams/partition' => '價量比例圖',
    '/diagrams/line' => '價格走勢圖',
    '/diagrams/dashboard' => '市場分析圖',
);
?>

<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>農產品交易行情資料視覺化</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script>
            var webroot = '<?php echo $this->Html->webroot ?>';
        </script>
        <?php
        echo $this->Html->meta('icon');
        echo $this->fetch('meta');
        echo $this->Html->css(array(
            'bootstrap.min',
            'font-awesome.min',
            'style',
        ));
        echo $this->Html->script(array('jquery-1.10.2'));
        echo $this->Html->script(array(
            'angular.min',
            'd3.v3.min',
            'bootstrap-tooltip',
            'bootstrap.min',
            'public',
            'ng',
        ));
        ?>
        <style>
            * {
                /*border: 1px solid;*/
            }

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
        <div class="container-fluid">
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
            <div class="row">
                <div class="col-xs-12">
                    <div class="controlPanel" ng-controller="ControlPanelCtrl">
                        <div class="col-md-8">
                            <form class="form-inline">
                                <div class="form-group">
                                    <label>top</label>
                                    <input type="number" class="form-control" ng-model="top">
                                </div>
                                <div class="form-group">
                                    <label>skip</label>
                                    <input type="number" class="form-control" ng-model="skip">
                                </div>
                                <br/><br/>
                                <div class="form-group">
                                    <label>作物名稱</label>
                                    <select class="form-control" ng-model="selCat" ng-options="cat.name for cat in categorys" ng-change="update(selCat)" ng-init="selCat = categorys[0]">
                                    </select>
                                    <select class="form-control" ng-model="Crop" ng-options="item for item in items">
                                    </select>
                                </div>
                                <div class="form-group" ng-show="showMarket">
                                    <label>市場名稱</label>
                                    <select class="form-control" ng-model="Market" ng-options="m for m in markets">
                                    </select>
                                </div>
                                <br/><br/>
                                <div class="form-group">
                                    <label>開始日期</label>
                                    <input type="date" class="form-control" ng-model="StartDate" ng-value="StartDate">
                                </div>
                                <div class="form-group">
                                    <label>結束日期</label>
                                    <input type="date" class="form-control" ng-model="EndDate">
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="submit" ng-click="submit()">查詢</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            設定檔
                            <form class="form-inline">
                                <div class="form-group">
                                    <button type="button" class="btn btn-success" ng-click="saveSetting()">儲存設定</button>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-warning" ng-click="loadSetting()">載入設定</button>
                                </div>
                            </form>
                            <div class="list-group" id="settingList">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12" id="mainContent">
                    <?php echo $this->Session->flash(); ?>

                    <?php echo $this->fetch('content'); ?>
                    <?php //echo $this->element('sql_dump'); ?>
                </div>
            </div>
        </div>
    </body>
</html>

<?php
$path = "/{$this->Html->request->controller}/{$this->Html->request->action}";
$menuItems = array(
    '/diagrams/search' => '價格查詢',
    '/diagrams/partition' => '價量比例圖',
    '/diagrams/line' => '價格走勢圖',
    '/diagrams/dashboard' => '市場分析圖',
    '/diagrams/bubble' => '泡泡圖',
    '/diagrams/rank' => '排行榜',
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
            'jquery-ui',
            'style',
        ));
        echo $this->Html->script(array('jquery-1.10.2'));
        echo $this->Html->script(array(
            'angular.min',
            'd3.v3.min',
            'bootstrap-tooltip',
            'bootstrap.min',
            'jquery.dataTables.min',
            'jquery-ui.min',
            'jquery-scrollto',
            'dataTables.bootstrap',
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
        <div class="container-fluid" ng-controller="ControlPanelCtrl">
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
                            <?php echo $this->Html->link('農產品交易行情資料視覺化', '/diagrams', array('class' => 'navbar-brand')); ?>
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
            <div class="row" ng-show="showControlPanel">
                <div class="col-xs-12">
                    <div class="controlPanel">
                        <div class="col-xs-12 col-sm-8">
                            <div class="panel panel-default"> <!--panel-->
                                <div class="panel-heading" data-toggle="collapse" href="#controlPanelBody">
                                    <span class="panel-title">查詢選單</span>
                                </div>
                                <div class="panel-body panel-collapse collapse in" id="controlPanelBody">
                                    <form class="form-inline">
                                        <div class="">
                                            <div class="form-group">
                                                <label>top</label>
                                                <input type="number" class="form-control" ng-model="top">
                                            </div>
                                            <div class="form-group">
                                                <label>skip</label>
                                                <input type="number" class="form-control" ng-model="skip">
                                            </div>
                                            <br/><br/>
                                        </div>
                                        <div class="form-group">
                                            <label>作物名稱</label>
                                            <select class="form-control" ng-model="selCat" ng-options="cat.name for cat in categorys" ng-change="update(selCat)" ng-init="selCat = categorys[0]">
                                            </select>
                                            <select class="form-control" id="selectCrop" ng-model="Crop" ng-options="item for item in items"
                                                    data-toggle="popover" data-trigger="manual" data-content="選取的時間區間過長，建議選擇單一作物"
                                                    onclick="$(this).popover('hide')">
                                            </select>
                                        </div>
                                        <div class="form-group" ng-show="showMarket">
                                            <label>市場名稱</label>
                                            <select class="form-control" ng-model="Market" ng-options="m for m in markets">
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <a href="#" class="label label-success" ng-click="addSetting($event)" onclick="event.preventDefault();">新增到常用查詢</a>
                                        </div>
                                        <br/><br/>
                                        <div class="form-group">
                                            <label>開始日期</label>
                                            <input type="date" class="form-control" ng-model="StartDate" ng-max="EndDate">
                                        </div>
                                        <div class="form-group">
                                            <label>結束日期</label>
                                            <input type="date" class="form-control" ng-model="EndDate" ng-min="StartDate">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" ng-class="{'waiting':btnSubmitWaiting}" ng-disabled="btnSubmitWaiting" id="submit" ng-click="submit()"><span>查詢</span><i class="fa fa-spinner fa-spin"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </div><!--panel-->
                        </div>
                        <div class="col-xs-12 col-sm-3" style="float: right;">
                            <div class="panel panel-default"> <!--panel-->
                                <div class="panel-heading" data-toggle="collapse" href="#settingList">
                                    <span class="panel-title">常用查詢 </span>
                                    <a href="#" class="label label-success" ng-click="addSetting($event)" onclick="event.preventDefault();">新增</a>
                                </div>
                                <ul class="list-group panel-collapse collapse in" id="settingList" ng-init="loadSetting()">
                                    <li class="list-group-item" ng-repeat="setting in settings" ng-click="setSetting(setting)">
                                        {{setting.CatName}} / {{setting.Crop}} / {{setting.Market}}
                                        <a href="#" style="float: right;" ng-click="deleteSetting(setting.t)"><span class="glyphicon glyphicon-remove"></span></a>
                                    </li>
                                </ul>
                            </div><!--panel-->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12" id="mainContent">
                    <?php echo $this->Session->flash(); ?>

                    <?php echo $this->fetch('content'); ?>
                    <?php //echo $this->element('sql_dump');  ?>
                </div>
            </div>
        </div>

        <footer></footer>
        <?php
        echo $this->Html->script(array(
            'footer',
                ), array('async'));
        ?>
    </body>
</html>

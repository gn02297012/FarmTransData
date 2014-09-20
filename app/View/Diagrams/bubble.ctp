<style>
    .axis path,
    .axis line {
        fill: none;
        stroke: #000;
        shape-rendering: crispEdges;
    }

    .line {
        fill: none;
        stroke: steelblue;
        stroke-width: 1.5px;
    }

    circle {
        stroke: rgba(0, 0, 0, 0.8);
        stroke-width: 1px;
    }

    circle:hover {
        stroke-width: 2px;
    }
</style>
<script>
    //設定ControlPanelCtrl的參數
    $(document).ready(function() {
    angular.element('.controlPanel').scope().$apply(function($scope, $http) {
    $scope.showControlPanel = true;
            $scope.showAllCrop = true;
            $scope.showMarket = true;
            $scope.baseUrl = '<?php echo $this->Html->webroot('/query/test'); ?>';
            //$scope.Crop = '';
            //$scope.Market = '';
            $scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 10);
            //$scope.EndDate = formatDateInput(new Date());
            $scope.top = 5000;
            $scope.skip = 0;
            $scope.submit = function() {
            var cat = $scope.selCat.cat;
                    var url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + ($scope.Crop === '全部' ? '' : $scope.Crop) + '&Market=' + ($scope.Market === '全部' ? '' : $scope.Market) + '&StartDate=' + formatROCDate($scope.StartDate) + '&EndDate=' + formatROCDate($scope.EndDate) + '&Category=' + cat;
                    $scope.getData(url, jsonSuccess, window.location.pathname);
            };
    });
    });</script>

<br />
<br />

<div class="svgSection">
    <div class="col-md-4" ng-controller="ZoomCtrl as zoomCtrl" style="overflow: hidden;">
        <div id="key" style="visibility: hidden; margin-top: -20px;">
            <h3>請選擇作物</h3>
            <span>占 <span class="label cropCategory"></span> <span class="ration1">10</span> %</span>、
            <span>占 <span class="label label-default">全部作物</span> <span class="ration2">1</span> %</span>
        </div>
        <svg class="svgPartition"></svg>
        <div id="quantityPieChart" style="height: 320px;"></div>
    </div>
    <div class="col-md-4 hide" id="bubbleDetailSection">
        <br />
        <svg class="svgLine hide"></svg>
        <svg class="svgLine2 hide"></svg>
    </div>
    <div class="col-md-8">
        <br /><br /><br />
        <div id="quantityLineChart"></div>
        <div id="priceLineChart"></div>
    </div>
</div>

<?php
echo $this->Html->script(array('diagrams/bubble'));
?>
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
            $scope.baseUrl = '<?php echo $this->Html->webroot('/query/line'); ?>';
            //$scope.Crop = '';
            //$scope.Market = '';
            $scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 4);
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
    <button onclick="$('#bubbleDetailSection').animate({width: 'toggle'});">slide</button>
    <div class="well col-md-6" id="bubbleDetailSection">
        <div id="key"></div>
        <label>是否要滑鼠點擊時才畫圖<input type="checkbox" ng-model="showOneCrop" ng-init="showOneCrop = false"></label>
        <svg class="svgLine"></svg>
        <svg class="svgLine2"></svg>
    </div>
    <div class="col-md-6" ng-controller="ZoomCtrl" style="overflow: hidden;">
        <input type="range" min="{{minZoom}}" max="{{maxZoom}}" ng-value="minZoom" id="svgZoom" ng-model="zoom" ng-init="zoom = minZoom" style="position: absolute; z-index: 10;">{{zoom}}
        <svg class="svgBubble" ng-style="myStyle" style="border: 1px solid black;"></svg>
        <svg class="svgPartition"></svg>
    </div>
</div>

<?php 
echo $this->Html->script(array('diagrams/bubble'));
?>
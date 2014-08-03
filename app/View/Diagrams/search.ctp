<script>
    function cpCtrl($scope, $http) {
        //作物名稱選單
        $scope.categorys = JSON.parse('<?php echo json_encode([['name' => '全部', 'items' => [], 'cat' => 0], ['name' => '水果', 'items' => $fruits, 'cat' => 2], ['name' => '蔬菜', 'items' => $vegetables, 'cat' => 1]]); ?>');
        $scope.items = $scope.categorys[0]['items'];
        //市場選單
        $scope.showMarket = true;
        $scope.markets = JSON.parse('<?php echo json_encode($markets); ?>');
        //API參數
        $scope.baseUrl = '<?php echo $this->Html->webroot('/query/search'); ?>';
        $scope.Crop = '';
        $scope.Market = '';
        $scope.StartDate = '<?php echo date('Y-m-d', time() - 86400 * 10); ?>';
        $scope.EndDate = '<?php echo date('Y-m-d'); ?>';
        $scope.top = 100;
        $scope.skip = 0;

        //作物選單
        $scope.update = function(selectedCat) {
            $scope.items = selectedCat.items;
            //$scope.Crop = $scope.items[0];
        };

        //送出查詢
        $scope.submit = function() {
            function d(d) {
                return formatROCDate(d);
            }
            var cat = $scope.selCat.cat;
            $url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + ($scope.Crop ? $scope.Crop : '') + '&Market=' + ($scope.Market ? $scope.Market : '') + '&StartDate=' + d($scope.StartDate) + '&EndDate=' + d($scope.EndDate) + '&Category=' + cat;
            console.log($url);
            $http.get($url).success(function(data) {
                showData(data);
            });
        }
    }
</script>
<div class="controlPanel" ng-controller="cpCtrl">
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
                <option value="" selected>全部</option>
            </select>
        </div>
        <div class="form-group" ng-show="showMarket">
            <label>市場名稱</label>
            <select class="form-control" ng-model="Market" ng-options="m for m in markets">
                <option value="" selected>全部</option>
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

<div class="result">
    <br />
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>日期</th>
                <th>作物名稱</th>
                <th>市場</th>
                <th>平均價格</th>
                <th>上價</th>
                <th>中價</th>
                <th>下價</th>
                <th>交易量(公斤)</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    var tbody = d3.select('.result').select('tbody');

    var showData = function(data) {
        tbody.selectAll('tr').remove();
        var trs = tbody
                .selectAll('tr')
                .data(data).enter()
                .append('tr');

        trs.append('td').text(function(d, i) {
            return d.date;
        });
        trs.append('td').text(function(d, i) {
            return d.name;
        });
        trs.append('td').text(function(d, i) {
            return d.market;
        });
        trs.append('td').text(function(d, i) {
            return d.price;
        });
        trs.append('td').text(function(d, i) {
            return d.priceTop;
        });
        trs.append('td').text(function(d, i) {
            return d.priceMid;
        });
        trs.append('td').text(function(d, i) {
            return d.priceBottom;
        });
        trs.append('td').text(function(d, i) {
            return d.quantity;
        });
    };


</script>

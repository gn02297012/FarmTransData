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
        $scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 10);
        $scope.EndDate = formatDateInput(new Date());
        $scope.top = 100;
        $scope.skip = 0;

        //作物選單
        $scope.update = function(selectedCat) {
            $scope.items = selectedCat.items;
            //$scope.Crop = $scope.items[0];
        };

        //送出查詢
        $scope.submit = function() {
            var cat = $scope.selCat.cat;
            $url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + ($scope.Crop ? $scope.Crop : '') + '&Market=' + ($scope.Market ? $scope.Market : '') + '&StartDate=' + formatROCDate($scope.StartDate) + '&EndDate=' + formatROCDate($scope.EndDate) + '&Category=' + cat;
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
    <div class="filter"></div><br />
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>交易日期</th>
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
    var filter = d3.select('.result').select('.filter');

    var showData = function(data) {

        var nested_data = d3.nest()
                .key(function(d) {
                    return d.name;
                })
                .entries(data);

        filter.selectAll('label').remove();
        if (nested_data.length < data.length && nested_data.length > 1) {
            var labels = filter.selectAll('label')
                    .data(nested_data).enter()
                    .append('label');
            labels.append('input')
                    .attr('type', 'checkbox')
                    .attr('checked', true)
                    .on('change', function(d) {
                        if (this.checked) {
                            $('[data-name="' + d.key + '"]').fadeIn();
                        } else {
                            $('[data-name="' + d.key + '"]').fadeOut();
                        }
                    });
            labels.append('span')
                    .text(function(d) {
                        return d.key;
                    });
        }

        tbody.selectAll('tr').remove();
        var trs = tbody
                .selectAll('tr')
                .data(data).enter()
                .append('tr')
                .attr('data-name', function(d) {
                    return d.name;
                });

        var fields = ['date', 'name', 'market', 'price', 'priceTop', 'priceMid', 'priceBottom', 'quantity'];
        trs.selectAll('td')
                .data(function(d) {
                    return $.map(fields, function(key) {

                        if (!isNaN(d[key])) {
                            d[key] = parseFloat(d[key]);
                        }
                        return d[key];
                    })
                }).enter()
                .append('td')
                .text(function(d) {
                    return d;
                });

        var sortFlag = 1;
        var icons = ['chevron-up', 'chevron-down'];
        var lastSortKey = '';
        d3.selectAll("thead th").data(fields).on("click", function(k) {
            //判斷是否在同一個欄位中按下排序
            if (lastSortKey !== k) {
                lastSortKey = k;
                //清除所有欄位的箭頭按鈕
                d3.selectAll('.chevron-up').classed('chevron-up', false);
                d3.selectAll('.chevron-down').classed('chevron-down', false);
            } else {
                //如果都在同一欄上面按，才切換ASC或DESC
                d3.select(this).classed((sortFlag == 1 ? icons[0] : icons[1]), false);
                sortFlag *= -1;
            }
            //設定當前欄位的箭頭
            d3.select(this).classed((sortFlag == 1 ? icons[0] : icons[1]), true);
            //排序
            trs.sort(function(a, b) {
                return (a[k] == b[k] ? 0 : (a[k] > b[k] ? 1 : -1)) * sortFlag;
            });
        });
    };


</script>

<script>
    //設定ControlPanelCtrl的參數     
    $(document).ready(function() {
        angular.element('.controlPanel').scope().$apply(function($scope, $http) {
            $scope.showAllCrop = true;
            $scope.showMarket = true;
            $scope.baseUrl = '<?php echo $this->Html->webroot('/query/search'); ?>';
            //$scope.Crop = '';
            //$scope.Market = '';
            //$scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 10);
            //$scope.EndDate = formatDateInput(new Date());
            $scope.top = 500;
            $scope.skip = 0;

            $scope.submit = function() {
                var cat = $scope.selCat.cat;
                var url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + ($scope.Crop === '全部' ? '' : $scope.Crop) + '&Market=' + ($scope.Market === '全部' ? '' : $scope.Market) + '&StartDate=' + formatROCDate($scope.StartDate) + '&EndDate=' + formatROCDate($scope.EndDate) + '&Category=' + cat;
                $scope.getData(url, showData, window.location.pathname);
            };
        });
    });</script>

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
        var icons = ['sort-asc', 'sort-desc', 'sortable'];
        var lastSortKey = '';
        d3.selectAll("thead th").data(fields)
                .attr('class', function(d, i) {
                    if (i > 2) {
                        return 'sortable';
                    }
                })
                .on("click", function(k) {
                    //判斷是否在同一個欄位中按下排序
                    if (lastSortKey !== k) {
                        lastSortKey = k;                 //清除所有欄位的箭頭按鈕
                        $.each(icons, function(i, d) {
                            d3.selectAll('.' + d).classed(d, false);
                        });
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

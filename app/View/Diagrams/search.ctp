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
            $scope.top = 1000;
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
    <table class="table table-bordered table-hover" id="dataTable">
        <thead>
            <tr>
                <th class="sort-desc">交易日期</th>
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
    var dataTable = $('#dataTable').DataTable({
        "language": {
            "lengthMenu": "每頁顯示 _MENU_ 筆交易行情",
            "zeroRecords": "沒有任何資料",
            "info": "第 _PAGE_ 頁，共 _PAGES_ 頁(_MAX_ 筆交易行情)",
            "infoEmpty": "找不到指定的資料",
            "infoFiltered": "(從全部 _MAX_ 筆交易行情中)",
            "search": "搜尋　",
            "url": "",
            "paginate": {
                "first": "第一頁",
                "previous": "上一頁",
                "next": "下一頁",
                "last": "最後一頁"
            }
        },
        "lengthMenu": [[20, 50, 100, 200, -1], [20, 50, 100, 200, "All"]],
        "order": [[0, "desc"]]
    });
    var fields = ['date', 'name', 'market', 'price', 'priceTop', 'priceMid', 'priceBottom', 'quantity'];


    var initTable = function() {
        //清除快速篩選的checkbox
        filter.selectAll('label').remove();
        //清空所有的資料列
        dataTable.rows().remove().draw();
    }

    var showData = function(data) {
        //處理原始資料成dataTable需要的格式
        var rows = $.map(data, function(d) {
            return [$.map(fields, function(key) {
                    if (!isNaN(d[key])) {
                        d[key] = parseFloat(d[key]);
                    }
                    return d[key];
                })];
        });
        //如果skip為0，表示這次是第一次搜尋，要先將表格清空
        if (angular.element('.controlPanel').scope().skip === 0) {
            initTable();
        }
        //加入篩選資料的checkbox
        var keys = d3.nest()
                .key(function(d) {
                    return d.name;
                })
                .rollup(function() {
                })
                .entries(data)
                .map(function(d) {
                    return d.key;
                });
        //找出現有的key
        var existedKeys = $('.filter').find('input').map(function(i, d) {
            return d.getAttribute('data-key');
        }).toArray();
        if (existedKeys.length) {
            //將未有的key插入到現有的key
            $.each(keys, function(i, d) {
                if (existedKeys.indexOf(d) === -1) {
                    existedKeys.push(d);
                }
            });
            keys = existedKeys;
        }
        //如果key數等於row數，或key數為1就不產生checkbox
        if (keys.length < (dataTable.rows()[0].length + data.length) && keys.length > 1) {
            var labels = filter.selectAll('label')
                    .data(keys).enter()
                    .append('label');
            labels.append('input')
                    .attr('type', 'checkbox')
                    .attr('checked', true)
                    .attr('data-key', function(d) {
                        return  d;
                    })
                    .on('change', function(d) {
                        var selectedCrops = $('.filter').find('input:checked').map(function(i, d) {
                            return d.getAttribute('data-key');
                        }).toArray();
                        if (selectedCrops.length === 0) {
                            selectedCrops = ['^$'];
                        }
                        dataTable.column(1).search(selectedCrops.join('|'), true, true).draw();
                    });
            labels.append('span')
                    .text(function(d) {
                        return d;
                    });
        }
        dataTable.rows.add(rows).draw();
//        tbody.selectAll('tr').remove();
//        var trs = tbody
//                .selectAll('tr')
//                .data(data).enter()
//                .append('tr')
//                .attr('data-name', function(d) {
//                    return d.name;
//                });
//
//        trs.selectAll('td')
//                .data(function(d) {
//                    return $.map(fields, function(key) {
//                        if (!isNaN(d[key])) {
//                            d[key] = parseFloat(d[key]);
//                        }
//                        return d[key];
//                    })
//                }).enter()
//                .append('td')
//                .text(function(d) {
//                    return d;
//                });
        console.log(data.length);
        if (data.length) {
            angular.element('.controlPanel').scope().skip += angular.element('.controlPanel').scope().top;
            angular.element('.controlPanel').scope().submit();
        } else {
            angular.element('.controlPanel').scope().skip = 0;
        }
    };


</script>

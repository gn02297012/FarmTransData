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

    .overlay {
        fill: none;
        pointer-events: all;
    }

    #detail {
        width: 100%;
    }

    #detail th {
        width: 20%;
    }

    .legend{
        width: auto;
        display:inline-block;
        margin-top: 50px;
        vertical-align: top;
    }


</style>

<script>
    //設定ControlPanelCtrl的參數
    $(document).ready(function() {
        angular.element('.controlPanel').scope().$apply(function($scope, $http) {
            $scope.showAllCrop = false;
            $scope.showMarket = true;
            $scope.baseUrl = '<?php echo $this->Html->webroot('/query/line'); ?>';
            //$scope.Crop = '';
            //$scope.Market = '';
            //$scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 365);
            //$scope.EndDate = formatDateInput(new Date());
            $scope.top = 3000;
            $scope.skip = 0;

            $scope.submit = function() {
                var cat = $scope.selCat.cat;
                var url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + $scope.Crop + '&Market=' + ($scope.Market ? $scope.Market : '') + '&StartDate=' + formatROCDate($scope.StartDate) + '&EndDate=' + formatROCDate($scope.EndDate);
                $scope.getData(url, jsonSuccess, window.location.pathname);
            };
        });
    });
</script>

<br />
<br />
<div class="svgSection col-xs-12" style="overflow-x: scroll;">
    <svg></svg>
    <div class="datePicker" style="float: left;" ng-controller="DatePickerCtrl">
        <input type="range" ng-model="selectedDate" ng-value="{{selectedDate}}" min="{{range[0]}}" max="{{range[1]}}">
    </div>
</div>
<table class="table table-striped table-bordered table-hover" id="detail">
    <thead>
        <tr>
            <th>作物名稱</th>
            <th>交易日期</th>
            <th>價格</th>
            <th>交易量</th>
            <th>交易額</th>
        </tr>        
    </thead>
    <tbody>

    </tbody>
</table>

<script>
    var margin = {top: 80, right: 40, bottom: 80, left: 50},
    width = 1040 - margin.left - margin.right,
            height = 600 - margin.top - margin.bottom;

    var format = d3.time.format('%Y.%m.%d');
    var formatDate = function(d) {
        return (d.getFullYear()) + '/' + (d.getMonth() + 1) + '/' + (d.getDate());
    };

    var x = d3.time.scale()
            .range([0, width]);

    var y = d3.scale.linear()
            .range([height, 0]);

    var color = d3.scale.category10();

    var xAxis = d3.svg.axis()
            .scale(x)
            .orient('bottom')
            .tickFormat(function(d) {
                return formatDate(d);
            });

    var yAxis = d3.svg.axis()
            .scale(y)
            .orient('left');

    var line = d3.svg.line()
            .x(function(d) {
                return x(d.date);
            })
            .y(function(d) {
                return y(d.price);
            });

    var svg = d3.select('body').select('.svgSection svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

    var jsonSuccess = function(data) {
        svg.selectAll('g').remove();
        var nested_data = d3.nest()
                .key(function(d) {
                    return d.name;
                })
                .entries(data);
         color.domain(nested_data.map(function(d) {
            return d.key;
        }));
        data.forEach(function(d) {
            d.date = format.parse(d.date);
        });
        x.domain([d3.min(data, function(c) {
                return c.date.getTime();
            }),
            d3.max(data, function(c) {
                return c.date.getTime();
            })
        ]);
        y.domain(d3.extent(data, function(d) {
            return d.price;
        }));
        svg.append('g')
                .attr('id', 'xAxis')
                .attr('class', 'x axis')
                .attr('transform', 'translate(0,' + height + ')')
                .call(xAxis).selectAll('text')
                .style('text-anchor', 'end')
                .attr('dx', '-.8em')
                .attr('dy', '.15em')
                .attr('transform', function(d) {
                    return 'rotate(-65)'
                });
        svg.append('g')
                .attr('class', 'y axis')
                .call(yAxis)
                .append('text')
                .attr('transform', 'rotate(-90)')
                .attr('y', 6)
                .attr('dy', '.71em')
                .style('text-anchor', 'end')
                .text('Price ($)');
        svg.append('g')
                .attr('class', 'info')
                .append('text')
                .attr('transform', 'translate(' + (width - 50) + ', -20)')
                .attr('y', 6)
                .attr('dy', '.71em')
                .style('text-anchor', 'end')
                .text(function() {
                    var d = new Date();
                    d.setFullYear(d.getFullYear() - 1911);
                    return formatDate(d);
                });
        var item = svg.selectAll('.item')
                .data(nested_data)
                .enter().append('g')
                .attr('class', 'item')
                .attr('onmousemove', 'toFront(this)');
        item.append('path')
                .attr('class', 'line')
                .attr('data-key', function(d) {
                    return d.key;
                })
                .attr('d', function(d) {
                    return line(d.values);
                })
                .style('stroke', function(d) {
                    return color(d.key);
                })
                .style('stroke-opacity', 0.8);
        //套上範圍資料
        //console.log(angular.element('.datePicker').scope());
        if (angular.element('.datePicker').controller()) {
            //console.log('normal');
            angular.element('.datePicker').scope().init(x.domain());
        } else {
            //console.log('from other page');
            //因為AngularJS無法運作，所以這邊改成用jQuery來設定
            var domain = [x.domain()[0].getTime(), x.domain()[1].getTime()];
            var total = (domain[1] - domain[0]) / 86400 / 1000;
            var range = [0, total];
            $('.datePicker input[type="range"]').attr('max', total);
            $('.datePicker input[type="range"]').attr('min', 0);
            //angular.element('.datePicker').controller('DatePickerCtrl');
//            angular.element('.datePicker').scope()
//                    .$apply(function($scope) {
//                        //$scope.domain = [0,1];
//                        $scope.domain = [x.domain()[0].getTime(), x.domain()[1].getTime()];
//                    });
        }
        //日期選擇BAR的樣式
        $('.datePicker').css('width', (width + 6) + 'px')
                .css('margin-left', ($('#xAxis .domain').offset().left - $('.svgSection svg').offset().left) + 'px');
        $('.datePicker').fadeIn();

        //掃描線
        svg.append('g').append('line')
                .attr('class', 'scanline')
                .attr('x1', 0)
                .attr('y1', 0)
                .attr('x2', 0)
                .attr('y2', 0)
                .style('stroke', 'rgba(100, 100, 100, 0.8)')
                .style('stroke-width', '1px');
        var focus = svg.append('g')
                .attr('class', 'focus')
                .style('display', 'none');
        var circles = focus.selectAll('circle')
                .data(nested_data)
                .enter()
                .append('circle')
                .attr('class', 'circle')
                .attr('r', 6)
                .attr('fill', 'none')
                .attr('stroke', function(d) {
                    return color(d.key);
                });
        item.append('rect')
                .attr('class', 'overlay')
                .attr('width', width)
                .attr('height', height)
                .on('mouseover', function() {
                    focus.style('display', null);
                })
                .on('mouseout', function() {
                    focus.style('display', 'none');
                })
                .on('mousemove', mousemove);
        //表格初始化
        var tbody = d3.select('#detail tbody');
        //清除原本的表格資料
        tbody.selectAll('tr').remove();
        var trs = tbody.selectAll('tr')
                .data(nested_data).enter()
                .append('tr')
                .on('mouseout', function(d) {
                    $('[data-key="' + d.key + '"]').css('stroke-width', '');
                })
                .on('mousemove', function(d) {
                    //將線移到最上面並且加粗
                    toFront($('[data-key="' + d.key + '"]').parent()[0]);
                    $('[data-key="' + d.key + '"]').css('stroke-width', '3px');
                });
        //印出每列中的資料
        var crop = trs.append('td');
        crop.append("svg").attr("width", '16').attr("height", '16')
                .style('margin-right', '5px')
                .append("rect").attr("width", '16').attr("height", '16')
                .attr("fill", function(d) {
                    return color(d.key);
                });
        crop.append('span').text(function(d, i) {
            return d.key;
        });
        trs.append('td').text(function(d, i) {
            return formatDate(d.values[0].date);
        });
        trs.append('td').text(function(d, i) {
            return d.values[0].price;
        });
        trs.append('td').text(function(d, i) {
            return d.values[0].quantity;
        });
        trs.append('td').text(function(d, i) {
            return Math.round(d.values[0].price * d.values[0].quantity * 100) / 100;
        });

        //滑鼠移動事件，用來處理線上面的圈圈
        function mousemove() {
            //取得滑鼠x位置所對應到的時間
            var x0 = x.invert(d3.mouse(this)[0]);
            x0 = Math.round(x0 / 1000) * 1000;
            //由於x0的時間會包含時分秒，所以要整理成只有日期的格式
            var date = new Date();
            var offset = date.getTimezoneOffset() * 60000;
            var a = x0.valueOf() + offset;
            a -= a % 86400000 - offset;
            date.setTime(a);
            //設定圈圈顯示的位置
            circles.attr('transform', function(d) {
                //找出指定日期的那一筆資料
                var s = d.values.filter(function(d, i) {
                    return d.date.getTime() == a;
                });
                //不是每條線每天都有資料，所以要有資料才顯示
                if (s.length) {
                    return 'translate(' + x(a) + ',' + y(s[0].price) + ')';
                } else {
                    return 'translate(9999,9999)';
                }
            });
            //下方input range的值要跟著改
            var day = (a - x.domain()[0]) / 86400000;
            angular.element('.datePicker').scope()
                    .$apply(function($scope) {
                        $scope.selectedDate = day;
                    });
            //掃描線位置，由於有用AngularJS設定重畫，所以下面這行先註解起來
            moveScanline(x(a));
            $('.datePicker input[type="range"]').val(day);

            //更新詳細資料的表格內容
            //updateDetailTable(a);

            //顯示日期
            $('.info').children('text').html(formatDate(date));
        }
    };

    //更新詳細資料的表格內容
    function updateDetailTable(time) {
        var tbody = d3.select('#detail tbody');
        //表格資料要跟著連動
        tbody.selectAll('tr')
                .each(function(d, i) {
                    var s = d.values.filter(function(d, i) {
                        return d.date.getTime() == time;
                    });
                    //只顯示指定日期的
                    if (s.length) {
                        d3.select(this).selectAll('td')
                                .each(function(d, i) {
                                    //作物名稱那一欄不要更新
                                    if (i === 0)
                                        return;
                                    //不同的td要顯示不同資料
                                    var txt;
                                    switch (i) {
                                        case 0:
                                            txt = s[0].name;
                                            break;
                                        case 1:
                                            txt = formatDate(s[0].date);
                                            break;
                                        case 2:
                                            txt = s[0].price;
                                            break;
                                        case 3:
                                            txt = s[0].quantity;
                                            break;
                                        case 4:
                                            txt = Math.round(s[0].price * s[0].quantity * 100) / 100;
                                            break;
                                    }
                                    this.innerHTML = txt;
                                });
                    } else {
                        d3.select(this).selectAll('td')
                                .each(function(d, i) {
                                    if (i > 0) {
                                        this.innerHTML = '';
                                    }
                                });
                    }
                });
    }

    function moveScanline(x) {
        d3.select('.scanline')
                .attr('x1', x)
                .attr('y1', 0)
                .attr('x2', x)
                .attr('y2', height);
    }

    function toFront(el) {
        el.parentNode.appendChild(el.parentNode.removeChild(el));
    }
</script>
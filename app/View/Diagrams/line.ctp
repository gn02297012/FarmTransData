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
            $scope.showControlPanel = true;
            $scope.showAllCrop = false;
            $scope.showMarket = true;
            $scope.baseUrl = '<?php echo $this->Html->webroot('/query/test'); ?>';
            //$scope.Crop = '';
            //$scope.Market = '';
            //$scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 7);
            //$scope.EndDate = formatDateInput(new Date());
            $scope.top = 5000;
            $scope.skip = 0;

            $scope.submit = function() {
                var cat = $scope.selCat.cat;
                var url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + ($scope.Crop === '全部' ? '' : $scope.Crop) + '&Market=' + ($scope.Market === '全部' ? '' : $scope.Market) + '&StartDate=' + formatROCDate($scope.StartDate) + '&EndDate=' + formatROCDate($scope.EndDate);
                $scope.getData(url, jsonSuccess, window.location.pathname);
            };
        });
    });
</script>

<br />
<br />

<div class="svgSection col-xs-12 col-md-7" style="/*overflow-x: scroll;*/">
    <form class="form-inline" id="filterPanel" style="display :none;">
        <div class="form-group">
            <label>作物名稱</label>
            <select class="form-control" id="filterCrop">
            </select>
            <label>市場名稱</label>
            <select class="form-control" id="filterMarket">
            </select>
            <button type="button" class="btn btn-xs btn-success" id="addToShowList" data-toggle="tooltip" data-placement="right" title="加入到顯示清單">
                <span class="glyphicon glyphicon-plus"></span>
            </button>
        </div>
    </form>
    <svg></svg>
    <!--    <div class="datePicker" style="float: left; display: none;" ng-controller="DatePickerCtrl">
            <input type="range" ng-model="selectedDate" ng-value="{{selectedDate}}" min="{{range[0]}}" max="{{range[1]}}">
        </div>-->

</div>
<div class="col-xs-12 col-md-5">
    <table class="table table-bordered table-hover table-condensed" id="detail">
        <thead>
            <tr>
                <th>作物名稱</th>
                <th>市場</th>
                <th>交易日期</th>
                <th>價格</th>
                <th>交易量(公斤)</th>
                <th>交易額</th>
            </tr>        
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    var margin = {top: 80, right: 60, bottom: 80, left: 60},
    width = 700 - margin.left - margin.right,
            height = 600 - margin.top - margin.bottom;

    var format = d3.time.format('%Y.%m.%d');
    var formatDate = function(d) {
        return (d.getFullYear()) + '/' + (d.getMonth() + 1) + '/' + (d.getDate());
    };

    var x = d3.time.scale()
            .range([0, width]);

    var y = d3.scale.linear()
            .range([height - 100, 0]);

    var color = d3.scale.category20();

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
    //交易量的線
    var y2 = d3.scale.linear()
            .range([height, height - 100]);

    var y2Axis = d3.svg.axis()
            .scale(y2)
            .orient('left').ticks(5);
    var line2 = d3.svg.line()
            .x(function(d) {
                return x(d.date);
            })
            .y(function(d) {
                return y2(d.quantity);
            });

    var svg = d3.select('body').select('.svgSection svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

    var prevData = [];

    $('#addToShowList').on('click', function() {
        var filterCrop = d3.select('#filterCrop');
        var filterMarket = d3.select('#filterMarket');
        $('[data-key="' + filterCrop.node().value + '@' + filterMarket.node().value + '"]').fadeIn();
    });

    //第一次搜尋的初始化動作
    var init = function() {
        //清除先前的資料
        prevData = [];
        //顯示過濾清單
        $('#filterPanel').fadeIn();
        //設定作物名稱篩選與市場篩選
        var filterCrop = d3.select('#filterCrop');
        var filterMarket = d3.select('#filterMarket');
        //清除原有的選單
        filterCrop.selectAll('option').remove();
        filterMarket.selectAll('option').remove();
    };

    var jsonSuccess = function(data) {
        //將原始資料的日期格式化
        $.each(data, function(i, d) {
            d.date = format.parse(d.date);
                    d.price = +d.price;
        });
        //如果skip為0，表示這次是第一次搜尋，要先將表格清空
        svg.selectAll('g').remove();
        if (angular.element('.controlPanel').scope().skip === 0) {
            init();
        } else {
            data = prevData.concat(data);
        }
        //處理本次搜尋到的資料
        var nested_data = d3.nest()
                .key(function(d) {
                    return d.name;
                })
                .sortValues(function(a, b) {
                    return b.date.getTime() - a.date.getTime();
                })
                .entries(data);
        //取出本次資料中所有的作物名稱
        var crops = d3.nest()
                .key(function(d) {
                    return d.name;
                })
                .entries(data)
                .map(function(d) {
                    return d.key;
                });
        //加入下拉選單
        var filterCrop = d3.select('#filterCrop');
        filterCrop.selectAll('option')
                .data(crops).enter()
                .append('option')
                .attr('value', function(d) {
                    return d;
                })
                .text(function(d) {
                    return d;
                });
        //取出本次資料中所有的市場名稱
        var markets = d3.nest()
                .key(function(d) {
                    return d.market;
                })
                .entries(data)
                .map(function(d) {
                    return d.key;
                });
        markets.splice(0, 0, '全部');
        //加入下拉選單
        var filterMarket = d3.select('#filterMarket');
        filterMarket.selectAll('option')
                .data(markets).enter()
                .append('option')
                .attr('value', function(d) {
                    return d;
                })
                .text(function(d) {
                    return d;
                });

        var combineMarket = nested_data.map(function(d) {
            //將每個市場的資料切割出來
            var eachMarket = d3.nest()
                    .key(function(d) {
                        return d.market;
                    })
                    .entries(d.values)
                    .map(function(m) {
                        return {key: d.key + '@' + m.key, values: m.values};
                    });
            //將各個市場的資料加總起來
            var tmp = d3.nest()
                    .key(function(d) {
                        return d.date;
                    })
                    .sortKeys(function(a, b) {
                        return (new Date(b)).getTime() - (new Date(a)).getTime();
                    })
                    .rollup(function(t) {
                        var sum = {code: t[0].code, name: t[0].name, market: '全部', marketCode: t[0].marketCode,
                            date: t[0].date,
                            marketCount: d3.sum(t, function(v) {
                                return v.marketCount;
                            }), quantity: d3.sum(t, function(v) {
                                return v.quantity;
                            }), amount: d3.sum(t, function(v) {
                                return v.price * v.quantity;
                            })};
                        sum.price = sum.amount / sum.quantity;
                        return sum;
                    })
                    .entries(d.values);
            var total = {key: d.key + '@全部', values: tmp.map(function(d) {
                    return d.values;
                })};
            //將市場總和的資料和各市場資料合併
            eachMarket.splice(0, 0, total);
            return eachMarket;
        });
        //資料格式處理
        nested_data = [];
        combineMarket.map(function(d) {
            nested_data = nested_data.concat(d);
        });
        color.domain(nested_data.map(function(d) {
            return d.key;
        }));

        //計算delta
        $.each(combineMarket, function(i, d) {
            $.each(d, function(i, e) {
                //設定每個作物最早那天的delta資料
                if (e.values.length) {
                    e.values[e.values.length - 1].deltaPrice = 0;
                    e.values[e.values.length - 1].deltaQuantity = 0;
                }
                //計算其他天的delta
                for (var index = 0; index < e.values.length - 1; index++) {
                    e.values[index].deltaPrice = e.values[index].price - e.values[index + 1].price;
                    e.values[index].deltaQuantity = e.values[index].quantity - e.values[index + 1].quantity;
                }
            });
        });

        var startDate = new Date(angular.element('.controlPanel').scope().StartDate);
        startDate.setFullYear(startDate.getFullYear() - 1911);
        var currStartDate = d3.min(data, function(c) {
            return c.date.getTime();
        });

        x.domain([(prevData.length - data.length) ? startDate : currStartDate,
            d3.max(data, function(c) {
                return c.date.getTime();
            })
        ]);
        y.domain([d3.min(nested_data, function(d) {
                return d3.min(d.values, function(m) {
                    return m.price;
                });
            }), d3.max(nested_data, function(d) {
                return d3.max(d.values, function(m) {
                    return m.price;
                });
            })]);
        y2.domain([d3.min(nested_data, function(d) {
                return d3.min(d.values, function(m) {
                    return m.quantity;
                });
            }), d3.max(nested_data, function(d) {
                return d3.max(d.values, function(m) {
                    return m.quantity;
                });
            })]);
        //計算時間範圍的天數
        var domain = [x.domain()[0].getTime(), x.domain()[1].getTime()];
        var total = (domain[1] - domain[0]) / 86400 / 1000;
        var range = [0, total];
        //重新設定x的標籤數，解決天數過少會有重複標籤的問題
        xAxis.ticks(total < 7 ? total : 7);
        //X軸日期
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
        //Y軸價格
        svg.append('g')
                .attr('class', 'y axis')
                .call(yAxis)
                .append('text')
                .attr('transform', 'rotate(-90)')
                .attr('y', 6)
                .attr('dy', '.71em')
                .style('text-anchor', 'end')
                .text('Price ($)');
        //Y軸交易量
        svg.append('g')
                .attr('class', 'y axis')
                //.attr('transform', 'translate(' + width + ', 0)')
                .call(y2Axis)
                .append('text')
                .attr('transform', 'rotate(-90)')
                .attr('y', 6)
                .attr('dy', '.71em')
                .style('text-anchor', 'end');
                //.text('Quantity (KG)');
        //右上角的顯示日期
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
        //每個作物的線
        var item = svg.selectAll('.item')
                .data(nested_data)
                .enter().append('g')
                .attr('class', 'item')
                .attr('data-key', function(d) {
                    return d.key;
                })
                .style('display', function(d, i) {
                    return i ? 'none' : '';
                })
                .attr('onmousemove', 'toFront(this)');
        item.append('path')
                .attr('class', 'line')
                .attr('d', function(d) {
                    return line(d.values);
                })
                .style('stroke', function(d) {
                    return color(d.key);
                })
                .style('stroke-opacity', 0.8);
        item.append('path')
                .attr('class', 'line')
                .attr('d', function(d) {
                    return line2(d.values);
                })
                .style('stroke', function(d) {
                    return color(d.key);
                })
                .style('stroke-opacity', 0.3);

        //掃描線
        svg.append('g').append('line')
                .attr('class', 'scanline')
                .attr('x1', 0)
                .attr('y1', 0)
                .attr('x2', 0)
                .attr('y2', 0)
                .style('stroke', 'rgba(100, 100, 100, 0.8)')
                .style('stroke-width', '1px');
        //用於滑鼠移動時的圈圈
        var focus = svg.append('g')
                .attr('class', 'focus');
        var circles = focus.selectAll('circle')
                .data(nested_data)
                .enter()
                .append('circle')
                .attr('data-key', function(d) {
                    return d.key;
                })
                .attr('class', 'circle')
                .style('display', function(d, i) {
                    return i ? 'none' : '';
                })
                .attr('transform', function(d) {
                    return 'translate(' + x(d.values[0].date.getTime()) + ',' + y(d.values[0].price) + ')';
                })
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
                .attr('data-key', function(d) {
                    return d.key;
                })
                .style('display', function(d, i) {
                    return i ? 'none' : '';
                })
                .on('mouseout', function(d) {
                    $('[data-key="' + d.key + '"]').css('stroke-width', '');
                })
                .on('mousemove', function(d) {
                    //將線移到最上面並且加粗
                    toFront($('[data-key="' + d.key + '"]').parent()[0]);
                    $('[data-key="' + d.key + '"]').css('stroke-width', '3px');
                });
        //印出每列中的資料
        var crop = trs.append('td').append('label');
        //加入是否顯示的checkbox
        crop.append('a')
                .attr('href', '#')
                .on('click', function(d, i, event) {
                    d3.event.preventDefault();
                    $('[data-key="' + d.key + '"]').fadeOut();
                })
                .append('span')
                .attr('class', 'glyphicon glyphicon-remove');
        //加入圖例顏色
        crop.append("svg").attr("width", '16').attr("height", '16')
                .style('margin-left', '5px')
                .style('margin-right', '5px')
                .append("rect").attr("width", '16').attr("height", '16')
                .attr("fill", function(d) {
                    return color(d.key);
                });
        //加入文字
        crop.append('span').text(function(d, i) {
            return d.values[0].name;
        });
        trs.append('td').text(function(d, i) {
            return d.values[0].market;
        });
        trs.append('td').text(function(d, i) {
            return formatROCDate(d.values[0].date);
        });
        trs.append('td').text(function(d, i) {
            return Math.round(d.values[0].price * 100) / 100;
        });
        trs.append('td').text(function(d, i) {
            return d.values[0].quantity;
        });
        trs.append('td').text(function(d, i) {
            return Math.round(d.values[0].price * d.values[0].quantity * 100) / 100;
        });

        //預設只顯示第一種作物@全部市場
        nested_data.map(function(d, i) {
            if (i) {
                $('[data-key="' + d.key + '"]').fadeOut(1);
            } else {
                $('[data-key="' + d.key + '"]').fadeIn();
            }
            return d.key;
        });

        console.log(data.length - prevData.length);
        if (prevData.length - data.length) {
            prevData = data;
            angular.element('.controlPanel').scope().skip += angular.element('.controlPanel').scope().top;
            angular.element('.controlPanel').scope().submit();
        } else {
            angular.element('.controlPanel').scope().skip = 0;
        }

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
//            angular.element('.datePicker').scope()
//                    .$apply(function($scope) {
//                        $scope.selectedDate = day;
//                    });
            //掃描線位置，由於有用AngularJS設定重畫，所以下面這行先註解起來
            moveScanline(x(a));
            $('.datePicker input[type="range"]').val(day);

            //更新詳細資料的表格內容
            updateDetailTable(a);

            //顯示日期
            $('.info').children('text').html(formatROCDate(date));
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
                                            txt = s[0].market;
                                            break;
                                        case 2:
                                            txt = formatROCDate(s[0].date);
                                            break;
                                        case 3:
                                            txt = (Math.round(s[0].price * 100) / 100) + '<br />';
                                            if (s[0].deltaPrice > 0) {
                                                txt += '<i class="fa fa-sort-asc" style="color: red;"></i>(' + (Math.round(s[0].deltaPrice * 100) / 100) + ')';
                                            } else if (s[0].deltaPrice < 0) {
                                                txt += '<i class="fa fa-sort-desc" style="color: green;"></i>(' + (Math.round(s[0].deltaPrice * 100) / 100) + ')';
                                            }
                                            break;
                                        case 4:
                                            txt = s[0].quantity + '<br />';
                                            if (s[0].deltaQuantity > 0) {
                                                txt += '<i class="fa fa-sort-asc" style="color: red;"></i>(' + s[0].deltaQuantity + ')';
                                            } else if (s[0].deltaQuantity < 0) {
                                                txt += '<i class="fa fa-sort-desc" style="color: green;"></i>(' + s[0].deltaQuantity + ')';
                                            } else {
                                                
                                            }
                                            break;
                                        case 5:
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
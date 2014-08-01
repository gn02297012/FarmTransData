<style>
    .axis path,
    .axis line {
        fill: none;
        stroke: #000;
        shape-rendering: crispEdges;
    }

    .x.axis path {
        /*display: none;*/
    }

    .line {

        fill: none;
        stroke: steelblue;
        stroke-width: 1.5px;
    }

    .item:hover>.line {
        /*stroke-width: 3px;*/
    }

    .overlay {
        fill: none;
        pointer-events: all;
    }

    #detail {
        width: 100%;
        margin-left: 30px;
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
    function cpCtrl($scope, $http) {
        //作物名稱選單
        $scope.categorys = JSON.parse('<?php echo json_encode([ ['name' => '水果', 'items' => $fruits],['name' => '蔬菜', 'items' => $vegetables]]); ?>');
        $scope.items = $scope.categorys[0]['items'];
        //市場選單
        $scope.markets = JSON.parse('<?php echo json_encode($markets); ?>');
        //API參數
        $scope.Crop = '';
        $scope.Market = '';
        $scope.StartDate = '<?php echo date('Y-m-d', time() - 86400 * 365); ?>';
        $scope.EndDate = '<?php echo date('Y-m-d'); ?>';
        $scope.top = 2000;
        $scope.skip = 0;

        //作物選單
        $scope.update = function(selectedCat) {
            $scope.items = selectedCat.items;
            $scope.Crop = $scope.items[0];
        };

        //送出查詢
        $scope.submit = function() {
            function d(d) {
                return formatROCDate(d);
            }            
            $url = '<?php echo $this->Html->webroot('/query/line'); ?>?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + $scope.Crop + '&Market=' + ($scope.Market?$scope.Market:'') + '&StartDate=' + d($scope.StartDate) + '&EndDate=' + d($scope.EndDate);
            console.log($url);
            $http.get($url).success(function(data) {
                jsonSuccess(null, data);
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
            <select class="form-control" ng-model="Crop" ng-options="item for item in items" ng-init="Crop = items[0]">

            </select>
        </div>
        <div class="form-group">
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
<br />
<br />
<div class="svgSection">

</div>
<table id="detail">
    <thead>
        <tr>
            <th>Name</th>
            <th>Date</th>
            <th>Price</th>
            <th>Quantity(KG)</th>
            <th>Amount</th>
        </tr>        
    </thead>
    <tbody>

    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#submit').click();
    });

    var margin = {top: 80, right: 40, bottom: 80, left: 50},
    width = 960 - margin.left - margin.right,
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

    var svg = d3.select('body').select('.svgSection').append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

    var jsonSuccess = function(error, data) {
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
        /*
         * //原本放在線後端的文字
         item.append('text')
         .datum(function(d) {
         return {name: d.key, value: d.values[0]};
         })
         .attr('class', 'bg-text')
         .attr('transform', function(d) {
         return 'translate(' + x(d.value.date) + ',' + (y(d.value.price) - 7) + ')';
         })
         .attr('x', 3)
         .attr('dy', '.35em')
         .style('stroke', function(d) {
         return color(d.value.name);
         })
         .text(function(d) {
         return d.value.name;
         });
         
         item.append('text')
         .datum(function(d) {
         return {name: d.key, value: d.values[0]};
         })
         .attr('transform', function(d) {
         return 'translate(' + x(d.value.date) + ',' + (y(d.value.price) - 7) + ')';
         })
         .attr('fill', 'black')
         .attr('x', 3)
         .attr('dy', '.35em')
         .text(function(d) {
         return d.value.name;
         });*/

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


        legend($.map(nested_data, function(d) {
            return d.key;
        }));

        var tbody = d3.select('#detail')
                .select('tbody');

        //清除原本的表格資料
        tbody.selectAll('tr').remove();
        var trs = tbody.selectAll('tr')
                .data(nested_data).enter()
                .append('tr');

        //印出每列中的資料
        trs.append('td').text(function(d, i) {
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
            return d.values[0].price * d.values[0].quantity;
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
            //表格
            tbody.selectAll('tr')
                    .each(function(d, i) {
                        var s = d.values.filter(function(d, i) {
                            return d.date.getTime() == a;
                        });
                        //只顯示指定日期的
                        if (s.length) {
                            d3.select(this).selectAll('td')
                                    .each(function(d, i) {
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
                                                txt = s[0].price * s[0].quantity;
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
            //顯示日期
            $('.info').children('text').html(formatDate(date));
        }
    };

    function legend(lD) {
        var leg = {};
        //清除現有的圖例
        d3.select('.svgSection').select('table').remove();
        // create table for legend.
        var legend = d3.select('body').select('.svgSection').append("table").attr('class', 'legend table table-hover table-bordered');

        // create one row per segment.
        var tr = legend.append("tbody")
                .selectAll("tr")
                .data(lD).enter()
                .append("tr")
                .on('mouseout', function(d) {
                    $('[data-key="' + d + '"]').css('stroke-width', '');
                })
                .on('mousemove', function(d) {
                    $('[data-key="' + d + '"]').css('stroke-width', '3px');
                });

        // create the first column for each segment.
        tr.append("td").append("svg").attr("width", '16').attr("height", '16').append("rect")
                .attr("width", '16').attr("height", '16')
                .attr("fill", function(d) {
                    return color(d);
                });

        // create the second column for each segment.
        tr.append("td").text(function(d) {
            return d;
        });
    }

    function toFront(el) {
        el.parentNode.appendChild(el.parentNode.removeChild(el));
    }



</script>
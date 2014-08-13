<script>
    //設定ControlPanelCtrl的參數
    $(document).ready(function() {
        angular.element('.controlPanel').scope().$apply(function($scope, $http) {
            $scope.showAllCrop = true;
            $scope.showMarket = true;
            $scope.baseUrl = '<?php echo $this->Html->webroot('/query/line'); ?>';
            //$scope.Crop = '';
            //$scope.Market = '';
            $scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 0);
            //$scope.EndDate = formatDateInput(new Date());
            $scope.top = 500;
            $scope.skip = 0;

            $scope.submit = function() {
                var cat = $scope.selCat.cat;
                var url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + ($scope.Crop === '全部' ? '' : $scope.Crop) + '&Market=' + ($scope.Market === '全部' ? '' : $scope.Market) + '&StartDate=' + formatROCDate($scope.StartDate) + '&EndDate=' + formatROCDate($scope.EndDate) + '&Category=' + cat;
                $scope.getData(url, jsonSuccess, window.location.pathname);
            };
        });
    });
</script>

<br />
<br />

<div class="svgSection">
    <div id="key"></div>
    <svg></svg>
</div>

<script>
    var width = 960,
            height = 800,
            padding = 1.5, //同一群中，每個圈圈的留白
            clusterPadding = 4, //不同群的圈圈留白
            maxRadius = 100;

    var svg = d3.select('body').select('.svgSection svg')
            .attr("width", width)
            .attr("height", height);

    var color = d3.scale.category20();
    var radius = d3.scale.linear()
            .range([1, maxRadius]);

    var format = d3.time.format('%Y.%m.%d');
    var formatDate = function(d) {
        return (d.getFullYear()) + '/' + (d.getMonth() + 1) + '/' + (d.getDate());
    };
    var selectProp = function(d) {
        return d.quantity;
    };

    var prevData = [];

    //第一次搜尋的初始化動作
    var init = function() {
        //清除先前的資料
        prevData = [];
    };

    var jsonSuccess = function(data) {
        //全部資料抓完才開始畫圖
        console.log(data.length);
        if (data.length) {
            prevData = prevData.concat(data);
            angular.element('.controlPanel').scope().skip += angular.element('.controlPanel').scope().top;
            angular.element('.controlPanel').scope().submit();
            return;
        } else {
            angular.element('.controlPanel').scope().skip = 0;
        }

        data = prevData;
        //將原始資料的日期格式化
        $.each(data, function(i, d) {
            d.date = format.parse(d.date);
        });
        //如果skip為0，表示這次是第一次搜尋，要先將表格清空
        if (angular.element('.controlPanel').scope().skip === 0) {
            init();
            svg.selectAll("circle").remove();
        }

        //承接下面的變數，資料分群後，再將所有子元素合併成一維陣列
        var children = []
        //將資料分群，並將同一個作物的數值加總起來
        var nodes = d3.nest()
                .key(function(d) {
                    return d.cropCategory;
                })
                .key(function(d, i) {
                    return d.name;
                })
                .rollup(function(t) {
                    var sum = {key: t[0].name, code: t[0].code, name: t[0].name, market: '全部', marketCode: t[0].marketCode,
                        date: t[0].date, cropCategory: t[0].cropCategory,
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
                .entries(data)
                .map(function(d, i) {
                    var e = d.values.map(function(e) {
                        e.values.cluster = i;
                        return e.values;
                    }).sort(function(a, b) {
                        return selectProp(b) - selectProp(a);
                    });
                    children = children.concat(e);
                    return e;
                });

        //總共要有多少個圈
        var n = children.length;
        //總共要有多少個群
        var m = nodes.length;

        color.domain(nodes.map(function(d) {
            return d.key;
        }));

        radius.domain(d3.extent(children, function(d) {
            return selectProp(d);
        }));
        
        //因為先前有做過排序，直接傳回每個群中的第一項，即為各群的最大值
        var clusters = nodes.map(function(d) {
            return d[0];
        });

        var force = d3.layout.force()
                .nodes(children)
                .size([width, height])
                .gravity(.04)
                .charge(0)
                .on("tick", tick)
                .start();

        var circle = svg.selectAll("circle")
                .data(children)
                .enter().append("circle")
                .attr("r", function(d) {
                    return radius(selectProp(d));
                })
                .style("fill", function(d) {
                    return color(d.cropCategory);
                })
                .style('stroke', 'black')
                .style('stroke-width', '1px')
                .call(force.drag)
                .on('mousemove', function(d) {
                    d3.select('.svgSection #key')
                            .text(d.key + "\t" + selectProp(d));
                });

        function tick(e) {
            circle
                    .each(cluster(10 * e.alpha * e.alpha))
                    .each(collide(.1))
                    .attr("cx", function(d) {
                        return d.x;
                    })
                    .attr("cy", function(d) {
                        return d.y;
                    });
        }

        // Move d to be adjacent to the cluster node.
        function cluster(alpha) {
            return function(d) {
                var cluster = clusters[d.cluster];
                if (cluster === d)
                    return;
                var x = d.x - cluster.x,
                        y = d.y - cluster.y,
                        l = Math.sqrt(x * x + y * y),
                        r = radius(selectProp(d)) + radius(selectProp(cluster));
                if (l != r) {
                    l = (l - r) / l * alpha;
                    d.x -= x *= l;
                    d.y -= y *= l;
                    cluster.x += x;
                    cluster.y += y;
                }
            };
        }

        // Resolves collisions between d and all other circles.
        function collide(alpha) {
            var quadtree = d3.geom.quadtree(children);
            return function(d) {
                var r = radius(selectProp(d)) + maxRadius + Math.max(padding, clusterPadding),
                        nx1 = d.x - r,
                        nx2 = d.x + r,
                        ny1 = d.y - r,
                        ny2 = d.y + r;
                quadtree.visit(function(quad, x1, y1, x2, y2) {
                    if (quad.point && (quad.point !== d)) {
                        var x = d.x - quad.point.x,
                                y = d.y - quad.point.y,
                                l = Math.sqrt(x * x + y * y),
                                r = radius(selectProp(d)) + radius(selectProp(quad.point)) + (d.cluster === quad.point.cluster ? padding : clusterPadding);
                        if (l < r) {
                            l = (l - r) / l * alpha;
                            d.x -= x *= l;
                            d.y -= y *= l;
                            quad.point.x += x;
                            quad.point.y += y;
                        }
                    }
                    return x1 > nx2 || x2 < nx1 || y1 > ny2 || y2 < ny1;
                });
            };
        }
    }
    ;
</script>
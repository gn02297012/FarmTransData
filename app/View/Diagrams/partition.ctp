<script>
    function cpCtrl($scope, $http) {
        //作物名稱選單
        $scope.categorys = JSON.parse('<?php echo json_encode([['name' => '全部', 'items' => [], 'cat' => 0], ['name' => '水果', 'items' => $fruits, 'cat' => 2], ['name' => '蔬菜', 'items' => $vegetables, 'cat' => 1]]); ?>');
        $scope.items = $scope.categorys[0]['items'];
        //市場選單
        $scope.markets = JSON.parse('<?php echo json_encode($markets); ?>');
        //API參數
        $scope.baseUrl = '<?php echo $this->Html->webroot('/query/partition'); ?>';
        $scope.Crop = '';
        $scope.Market = '';
        $scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 10);
        $scope.EndDate = formatDateInput(new Date());
        $scope.top = 500;
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
            <select class="form-control" ng-model="Crop" ng-options="item for item in items">
                <option value="" selected>全部</option>
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

<form class="form-inline">
    <br/>
    <div class="form-group">
        <label>顯示類型</label>
    </div>
    <div class="form-group">
        <label><input type="radio" name="mode" value="amount"> 價量</label>
    </div>
    <div class="form-group">
        <label><input type="radio" name="mode" value="quantity" checked> 交易量</label>
    </div>
    <div class="form-group">
        <label><input type="radio" name="mode" value="count"> 種類</label>
    </div>
</form>


<div class="svgSection" style="text-align: center;">
    <div class="showName" style="display: inline-block; position: absolute;"><br /><br /></div>
</div>

<script>
    var margin = {top: 40, right: 40, bottom: 40, left: 40},
    width = 900 - margin.left - margin.right,
            height = 900 - margin.top - margin.bottom;
    radius = Math.min(width, height) / 2,
            color = d3.scale.category20c();

    var x = d3.scale.linear()
            .range([0, 2 * Math.PI]);

    var y = d3.scale.linear()
            .range([0, radius]);

    var svg = d3.select("body").select('.svgSection').append("svg")
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append("g")
            .style('cursor', 'pointer')
            .attr("transform", "translate(" + (width / 2 + margin.left) + "," + (height / 2 + margin.top + 10) + ")");

    var partition = d3.layout.partition()
            .value(function(d) {
                return d.quantity;
            });

    var arc = d3.svg.arc()
            .startAngle(function(d) {
                return Math.max(0, Math.min(2 * Math.PI, x(d.x)));
            })
            .endAngle(function(d) {
                return Math.max(0, Math.min(2 * Math.PI, x(d.x + d.dx)));
            })
            .innerRadius(function(d) {
                if (d.depth == 1)
                    return 80;
                return Math.max(0, y(d.y) * 0.9);
            })
            .outerRadius(function(d) {
                if (!d.depth)
                    return 100;
                return Math.max(0, y(d.y + d.dy));
            });
    // Keep track of the node that is currently being displayed as the root.
    var node;
    //總共的value，用來計算百分比
    var totalValue = 0;

    var jsonSuccess = function(error, root) {
        node = root;
        svg.select('g').remove();
        var g = svg.datum(root).append("g")
                .selectAll("path")
                .data(partition.nodes)
                .enter()
                .append("path");
        var path = g.attr("d", arc)
                .attr("onmousemove", "ShowTooltip(evt)") //顯示文字提示
                .attr("onmouseout", "HideTooltip(evt)")
                .attr("data-name", function(d) {
                    return d.name;
                })
                .style("stroke", "#fff")
                .style("fill", function(d) {
                    return color((d.children ? d : d.parent).name);
                })
                //.style("fill-rule", "evenodd")
                .on("click", click)
                .on("mouseover", function mouseover(d) {
                    var percentage = (100 * d.value / totalValue).toPrecision(3);
                    var percentageString = percentage + "%";
                    if (percentage < 0.1) {
                        percentageString = "< 0.1%";
                    }
                    var oX = Math.sin(x(d.x + d.dx / 2)) * radius * 1.1 + radius + margin.left -20;
                    var oY = -Math.cos(x(d.x + d.dx / 2)) * radius * 1.05 + radius + margin.top - 10;
                    var translate = 'translate(' + oX + "px," + oY + 'px)';
                    $('.showName')
                            .css("transform", translate)
                            .html(d.name + '<br />' + percentageString);
                    //console.log(percentageString);
                })
                .each(stash);

        totalValue = path.node().__data__.value;
        function click(d) {
            node = d;
            path.transition()
                    .duration(1000)
                    .attrTween("d", arcTweenZoom(d));
        }

        d3.selectAll("[name='mode']").on("change", function change() {
            var value;
            switch (this.value) {
                case 'amount':
                    value = function(d) {
                        return d.price * d.quantity;
                    };
                    break;
                case 'quantity':
                    value = function(d) {
                        return d.quantity;
                    };
                    break;
                default:
                    value = function() {
                        return 1;
                    };
                    break;
            }

            path.data(partition.value(value).nodes)
                    .transition()
                    .duration(1000)
                    .attrTween("d", arcTweenData)
                    .attr("data-name", function(d) {
                        return d.name;
                    })
                    .style("fill", function(d) {
                        return color((d.children ? d : d.parent).name);
                    });
            totalValue = path.node().__data__.value;
        });
    };

    // Stash the old values for transition.
    function stash(d) {
        d.x0 = d.x;
        d.dx0 = d.dx;
    }

    // When switching data: interpolate the arcs in data space.
    function arcTweenData(a, i) {
        var oi = d3.interpolate({x: a.x0, dx: a.dx0}, a);
        function tween(t) {
            var b = oi(t);
            a.x0 = b.x;
            a.dx0 = b.dx;
            return arc(b);
        }
        if (i == 0) {
            // If we are on the first arc, adjust the x domain to match the root node
            // at the current zoom level. (We only need to do this once.)
            var xd = d3.interpolate(x.domain(), [node.x, node.x + node.dx]);
            return function(t) {
                x.domain(xd(t));
                return tween(t);
            };
        } else {
            return tween;
        }
    }

    // When zooming: interpolate the scales.
    function arcTweenZoom(d) {
        var xd = d3.interpolate(x.domain(), [d.x, d.x + d.dx]),
                yd = d3.interpolate(y.domain(), [d.y, 1]),
                yr = d3.interpolate(y.range(), [d.y ? 20 : 0, radius]);
        return function(d, i) {
            return i
                    ? function(t) {
                        return arc(d);
                    }
            : function(t) {
                x.domain(xd(t));
                y.domain(yd(t)).range(yr(t));
                return arc(d);
            };
        };
    }

    function ShowTooltip(evt) {
        var text = evt.target.getAttribute('data-name');
        var tooltip = $("#myTooltip");
        tooltip.css("top", evt.offsetY - 7);
        tooltip.css("left", evt.offsetX + 5);
        tooltip.css("opacity", 0);
        $(tooltip).children(".text").text(text);
        //console.log($(tooltip).children(".text"));
    }

    function HideTooltip(evt) {
        /*var text = evt.target.nextSibling;
         text.style.display = 'none';*/
        var tooltip = $("#myTooltip");
        tooltip.css("opacity", 0);
    }
</script>

<!-- Generated markup by the plugin -->
<div class="tooltip right" id="myTooltip" role="tooltip">
    <div class="tooltip-arrow"></div>
    <div class="tooltip-inner text">
        123
    </div>
</div>
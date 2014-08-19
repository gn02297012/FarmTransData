var margin = {top: 20, right: 60, bottom: 80, left: 70};
var width = 960,
        height = 600,
        padding = 1.5, //同一群中，每個圈圈的留白
        clusterPadding = 4, //不同群的圈圈留白
        maxRadius = 50;

var svg = d3.select('body').select('.svgBubble')
        .attr("width", width / 2)
        .attr("height", height)
        .style('z-index', '0');

var svgPartition = d3.select('body').select('.svgPartition')
        .attr("width", width / 2)
        .attr("height", 400);

var svgLine = d3.select('body').select('.svgLine')
        .attr("width", width / 2)
        .attr("height", 300);
var svgLine2 = d3.select('body').select('.svgLine2')
        .attr("width", width / 2)
        .attr("height", 300);

var color = d3.scale.category20();
var radius = d3.scale.linear()
        .range([1, maxRadius]);
//將原始資料的日期格式化
var format = d3.time.format('%Y.%m.%d');
var formatDate = function(d) {
    return (d.getFullYear()) + '/' + (d.getMonth() + 1) + '/' + (d.getDate());
};
//選取哪個屬性作為計算
var selectProp = function(d, isPrice) {
    if (isPrice)
        return d.price;
    return d.quantity;
};

//儲存每次查詢的資料，等到全部抓完後再進行動作
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
            .size([svg.attr('width'), height])
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
            .call(force.drag)
            .on('mousemove', function(d) {
                //顯示滑鼠移動到的作物名稱
                d3.select('.svgSection #key').text(d.key + "\t" + selectProp(d));
                if (angular.element('#bubbleDetailSection').scope().showOneCrop)
                    return;
                drawLine(d);

            })
            .on('click', function(d) {
                //畫線
                drawLine(d);
                angular.element('#bubbleDetailSection').scope().$apply(
                        function($scope) {
                            $scope.showOneCrop = true;
                        });
            });

    var partition = d3.layout.partition()
            .children(function(d) {
                return d.values;
            })
            .value(function(d) {
                return d.quantity;
            });
    var nes = d3.nest()
            .key(function(d) {
                return d.cropCategory;
            })
            .entries(children);
    var root = {name: '全部作物', values: nes};
    console.log(root);

    var px = d3.scale.linear()
            .range([0, 2 * Math.PI]);
    var arc = d3.svg.arc()
            .startAngle(function(d) {
                return Math.max(0, Math.min(2 * Math.PI, px(d.x)));
            })
            .endAngle(function(d) {
                return Math.max(0, Math.min(2 * Math.PI, px(d.x + d.dx)));
            })
            .innerRadius(function(d) {
                return 300 / 5 * d.depth;
            })
            .outerRadius(function(d) {
                return 300 / 5 * (d.depth + 1) - 1;
            });

    var node = partition.nodes(root);
    svg.on('mousewheel', function() {
        d3.event.preventDefault();
        console.log(d3.event);
        var dx = d3.event.offsetX / $('.svgBubble').width(),
        dy = d3.event.offsetY / $('.svgBubble').height();
        console.log(dx);
        console.log(dy);
        $('.svgBubble').css('transform-origin', (dx*100) + '% ' + (dy*100) + '%');
        angular.element('#svgZoom').scope().$apply(function($scope) {
            $scope.zoom += d3.event.wheelDelta;
        })
    });
    var path = svgPartition.append('g').datum(root)
            .style('stroke', 'white')
            .style('stroke-width', '0.5px')
            .style('transform', 'translate(200px,200px)')
            .selectAll('path')
            .data(node).enter()
            .append('path')
            .attr('d', arc)
            .attr("display", function(d) {
                return d.depth ? null : "none";
            })
            .style('fill', function(d) {
                return color((d.depth < 2) ? d.children[0].cropCategory : d.cropCategory);
            })
            .on('mouseenter', function(d) {
                console.log(d);
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

    function drawLine(d) {
        var width = svgLine.attr('width') - margin.left - margin.right,
                height = 300 - margin.top - margin.bottom;

        var x = d3.time.scale()
                .range([0, width]);

        var y = d3.scale.linear()
                .range([height, 0]);

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
                    return y(selectProp(d));
                });

        svgLine.selectAll('g').remove();
        var g = svgLine.append('g')
                .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

        var cropData = data.filter(function(e) {
            return e.name === d.name;
        });

        //儲存市場加總過後的各元素
        var afterNested = [];
        //根據日期分群，用來加總同一天所有市場的資料
        var nestedDate = d3.nest()
                .key(function(d) {
                    return d.date;
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
                    var result = [sum].concat(t);
                    afterNested = afterNested.concat(result);
                    return result;
                })
                .entries(cropData);
        //將加總過後的資料用市場分群
        var nestedMarket = d3.nest()
                .key(function(d) {
                    return d.market;
                })
                .entries(afterNested);

        x.domain([d3.min(nestedMarket, function(d) {
                return d3.min(d.values, function(e) {
                    return e.date.getTime();
                });
            }), d3.max(nestedMarket, function(d) {
                return d3.max(d.values, function(e) {
                    return e.date.getTime();
                });
            })]);

        y.domain([d3.min(nestedMarket, function(d) {
                return d3.min(d.values, function(e) {
                    return selectProp(e);
                });
            }), d3.max(nestedMarket, function(d) {
                return d3.max(d.values, function(e) {
                    return selectProp(e);
                });
            })]);

        //計算時間範圍的天數
        var domain = [x.domain()[0].getTime(), x.domain()[1].getTime()];
        var total = (domain[1] - domain[0]) / 86400 / 1000;
        var range = [0, total];
        //重新設定x的標籤數，解決天數過少會有重複標籤的問題
        xAxis.ticks(total < 7 ? total : 7);

        //X軸日期
        g.append('g')
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
        g.append('g')
                .attr('class', 'y axis')
                .call(yAxis)
                .append('text')
                .attr('transform', 'rotate(-90)')
                .attr('y', 6)
                .attr('dy', '.71em')
                .style('text-anchor', 'end')
                .text('交易量 (KG)');

        //每個作物的線
        var item = g.selectAll('.item')
                .data(nestedMarket)
                .enter().append('g')
                .attr('class', 'item')
                .style('display', function(d, i) {
                    return i ? 'none' : '';
                })
                .attr('data-key', function(d) {
                    return d.key;
                });
        item.append('path')
                .attr('class', 'line')
                .attr('d', function(d) {
                    return line(d.values);
                })
                .style('stroke', function(d) {
                    return color(d.values[0].cropCategory);
                })
                .style('stroke-opacity', 0.8);

        //價格
        svgLine2.selectAll('g').remove();
        var g2 = svgLine2.append('g')
                .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

        var y2 = d3.scale.linear()
                .range([height, 0]);

        var yAxis2 = d3.svg.axis()
                .scale(y2)
                .orient('left');

        var line2 = d3.svg.line()
                .x(function(d) {
                    return x(d.date);
                })
                .y(function(d) {
                    return y2(selectProp(d, 1));
                });

        y2.domain([d3.min(nestedMarket, function(d) {
                return d3.min(d.values, function(e) {
                    return selectProp(e, 1);
                });
            }), d3.max(nestedMarket, function(d) {
                return d3.max(d.values, function(e) {
                    return selectProp(e, 1);
                });
            })]);

        //X軸日期
        g2.append('g')
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
        g2.append('g')
                .attr('class', 'y axis')
                .call(yAxis2)
                .append('text')
                .attr('transform', 'rotate(-90)')
                .attr('y', 6)
                .attr('dy', '.71em')
                .style('text-anchor', 'end')
                .text('平均價格 ($)');
        //每個作物的線
        var item2 = g2.selectAll('.item')
                .data(nestedMarket)
                .enter().append('g')
                .attr('class', 'item')
                .style('display', function(d, i) {
                    return i ? 'none' : '';
                })
                .attr('data-key', function(d) {
                    return d.key;
                });
        item2.append('path')
                .attr('class', 'line')
                .attr('d', function(d) {
                    return line2(d.values);
                })
                .style('stroke', function(d) {
                    return color(d.values[0].cropCategory);
                })
                .style('stroke-opacity', 0.8);
    }
};
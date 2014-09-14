var margin = {top: 20, right: 60, bottom: 80, left: 70};
var width = 960,
        height = 500,
        padding = 1.5, //同一群中，每個圈圈的留白
        clusterPadding = 4, //不同群的圈圈留白
        maxRadius = 50;

var svgPartition = d3.select('body').select('.svgPartition')
        .attr("width", 500)
        .attr("height", 500);

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
        return d.price * 1;
    return d.quantity * 1;
};


//mouseenter時是否要畫線
var drawLineOnMouseEnter = true;

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
    $.each(data, function(i, d) {
        //將原始資料的日期格式化
        d.date = format.parse(d.date);
        //將交易量、價格變成number型態
        d.price *= 1;
        d.quantity *= 1;
    });
    //如果skip為0，表示這次是第一次搜尋，要先將表格清空
    if (angular.element('.controlPanel').scope().skip === 0) {
        init();
    }

    //承接下面的變數，資料分群後，再將所有子元素合併成一維陣列
    var children = [];
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

    color.domain(nodes.map(function(d) {
        return d.key;
    }));

    drawPartition(children);

    //畫階層圖
    function drawPartition(children) {
        var width = svgPartition.attr('width'),
                height = svgPartition.attr('height');

        var partition = d3.layout.partition()
                .children(function(d) {
                    return d.values;
                })
                .value(function(d) {
                    return d.quantity;
                });

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
                    return (width - 100) / 5 * d.depth;
                })
                .outerRadius(function(d) {
                    return (height - 100) / 5 * (d.depth + 1) - 1;
                });

        //處理資料
        var nes = d3.nest()
                .key(function(d) {
                    return d.cropCategory;
                })
                .entries(children);
        var root = {name: '全部作物', values: nes};
        var node = partition.nodes(root);

        svgPartition.selectAll('g').remove();
        //中間圓圈要顯示的文字
        svgPartition.append('g')
                .append('text')
                .attr('x', (width / 2) - 20)
                .attr('y', (height / 2))
                .attr('fill', 'black')
                .attr('id', 'partitionSelectCrop');
        var path = svgPartition.append('g').datum(root)
                .style('stroke', 'white')
                .style('stroke-width', '0.5px')
                .style('transform', 'translate(' + (width / 2) + 'px, ' + (height / 2) + 'px)')
                .selectAll('path')
                .data(node).enter()
                .append('path')
                .attr('d', arc)
                .attr("display", function(d) {
                    return d.depth ? null : "none";
                })
                .style('cursor', function(d) {
                    return (d.depth === 2) ? 'pointer' : null;
                })
                .style('fill', function(d) {
                    return color((d.depth < 2) ? d.children[0].cropCategory : d.cropCategory);
                })
                .on('mouseenter', function(d) {
                    //設定圓圈中間顯示的文字
                    var text = d3.select('.svgSection #partitionSelectCrop');
                    text.selectAll('tspan').remove();
                    //作物名稱
                    text.append('tspan')
                            .attr('x', function() {
                                //算出最適合的X位置
                                return (width / 2) - (d.key.length / 2) * 12;
                            })
                            .text(d.key);
                    //占全部的百分比
                    text.append('tspan')
                            .attr('x', function() {
                                //算出最適合的X位置
                                return (width / 2) - 12;
                            })
                            .attr('dy', '1.5em')
                            .text(Math.round(d.value / root.value * 10000) / 100 + '%');
                    if (drawLineOnMouseEnter && d.depth === 2) {
                        //畫線
                        drawLine(d);
                    }
                })
                .on('click', function(d) {
                    if (d.depth < 2) {
                        return;
                    }
                    //畫線
                    drawLine(d);
                    //mouseenter時不畫線
                    drawLineOnMouseEnter = false;
                });
    }
    ;

    //畫價格、交易量的線圖
    function drawLine(d) {
        //取出depth=0的value
        var totalValue = (function(d) {
            return d.depth ? arguments.callee(d.parent) : d.value;
        })(d);
        //顯示出此線圖的資料
        var detail = d3.select('.svgSection #key')
                .style('display', null);
        //作物名稱
        detail.select('h3')
                .text(d.key);
        //作物種類
        detail.select('.cropCategory')
                .style('background-color', function() {
                    return  color(d.cropCategory);
                })
                .text(d.parent.key);
        //占種類、全部的比例
        detail.select('.ration1')
                .text(Math.round(d.value / d.parent.value * 10000) / 100);
        detail.select('.ration2')
                .text(Math.round(d.value / totalValue * 10000) / 100);

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
                    //計算全部市場的總和
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
                    //將全部市場與其他市場的資料合併起來
                    var result = [sum].concat(t);
                    afterNested = afterNested.concat(result);
                    return result;
                })
                .entries(cropData);
        //計算資料的時間區間
        var dateRange = d3.extent(afterNested, function(d) {
            return d.date;
        });
        //console.log(dateRange);
        //算出日期最多差了幾天
        var dateDiff = (dateRange[1] - dateRange[0]) / 86400 / 1000;
        //要用多少天的趨勢線
        var avgDay = (dateDiff > 20) ? parseInt(dateDiff / 10) : 10;
        //console.log('dateDiff = ' + dateDiff);
        //將加總過後的資料用市場分群
        var nestedMarket = d3.nest()
                .key(function(d) {
                    return d.market;
                })
                .rollup(function(d) {
                    //計算價格均線
                    for (var i = d.length - 1; i >= 0; i--) {
                        if (i === (d.length - 1)) {
                            d[i].expPrice = d[i].price;
                        } else if (i >= (d.length - 1 - avgDay)) {
                            d[i].expPrice = (d[i + 1].expPrice * (d.length - i - 1) + d[i].price * 1) / (d.length - i);
                        } else {
                            d[i].expPrice = (d[i + 1].expPrice * avgDay - d[i + avgDay].price + d[i].price) / avgDay;
                        }
                    }
                    return d;
                })
                .entries(afterNested);
        //console.log(nestedMarket);
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
        //趨勢線
        line2.y(function(d) {
            if (isNaN(d.expPrice)) {
                console.log(d);
            }
            return y2(d.expPrice);
        });
        item2.append('path')
                .attr('class', 'line')
                .attr('d', function(d) {
                    return line2(d.values);
                })
                .style('stroke', function(d) {
                    return 'rgba(170, 170, 170, 0.8)';
                })
                .style('stroke-opacity', 0.8);

        //C3測試
        //X軸標籤
        var x = nestedMarket[0].values.map(function(d) {
            return d.date;
        });
        x.unshift('x');
        //價格
        var prices = nestedMarket[0].values.map(function(d) {
            return round4(d.price);
        });
        prices.unshift(nestedMarket[0].values[0].name);
        //交易量
        var quantities = nestedMarket[0].values.map(function(d) {
            return round4(d.quantity);
        });
        quantities.unshift(nestedMarket[0].values[0].name);
        //趨勢線
        var avg = nestedMarket[0].values.map(function(d) {
            return round4(d.expPrice);
        });
        avg.unshift(avgDay + '日均線');
        //畫圖
        var quantityChart = c3.generate({
            bindto: '#quantityLineChart',
            data: {
                x: 'x',
                columns: [x, quantities]
            },
            zoom: {
                enabled: true
            },
            point: {
                show: true
            },
            axis: {
                x: {
                    type: 'timeseries',
                    //padding: {right: 100},
                    tick: {
                        fit: true,
                        format: function(x) {
                            return formatDate(x);
                        }
                    }
                },
                y: {
                    label: '交易量(KG)'
                }
            }
        });
        var priceChart = c3.generate({
            bindto: '#priceLineChart',
            data: {
                x: 'x',
                columns: [x, prices, avg]
            },
            subchart: {
                show: true
            },
            axis: {
                x: {
                    type: 'timeseries',
                    //padding: {right: 100},
                    tick: {
                        fit: true,
                        format: function(x) {
                            return formatDate(x);
                        }
                    }
                },
                y: {
                    label: '價格'
                }
            }
        });

        //市場交易量
        var marketSum = nestedMarket.map(function(d) {
            return [d.key, d3.sum(d.values, function(d) {
                    return d.quantity;
                })];
        });
        //移除全部市場
        marketSum.shift(0);
        var quantityPieChart = c3.generate({
            bindto: '#quantityPieChart',
            data: {
                columns: marketSum,
                type: 'pie'
            }
        });
    }
};
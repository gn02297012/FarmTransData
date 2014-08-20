var rankSection = d3.select('#rankSection');

d3.selectAll('circle')
        .attr('r', 10)
        .attr('data-toggle', 'tooltip')
        .attr('data-placement', 'top')
        .style('display', 'none')
        .on('mouseenter', function() {
            d3.select(this).attr('data-market', d3.select(this).attr('title'));
//            var m = d3.select(this).attr('data-market');
//            if (m) {
//                d3.selectAll('[data-market="' + m + '"]').style('background-color', 'rgba(255, 255, 0, 0.5)');
//            }
        })
        .on('mouseleave', function() {
//            var m = d3.select(this).attr('data-market');
//            if (m) {
//                d3.selectAll('[data-market="' + m + '"]').style('background-color', null);
//            }
        })
        .on('click', function() {
            var circle = d3.select(this);
            //切換顯示市場
            var t = circle.attr('data-toggle');
            var m = circle.attr('data-market');
            console.log(m);
            //市場是否要顯示，因為circle中可能沒有data-toggle參數，所以必須這樣寫
            t = (t * 1 ? 0 : 1);
            circle.attr('data-toggle', t);
            if (m) {
                if (t) {
                    var table = d3.selectAll('.rankTable[data-market="' + m + '"]')[0][0];
                    table.parentNode.appendChild(table);
                    d3.selectAll('.rankTable[data-market="' + m + '"]').transition()
                            .duration(1000)
                            .styleTween('opacity', function(d, i, a) {
                                var i = d3.interpolateRound(0, 100);
                                return function(t) {
                                    return i(t) / 100;
                                }
                            })
                            .style('display', 'inline-block');
                } else {
                    d3.selectAll('.rankTable[data-market="' + m + '"]').transition()
                            .duration(1000)
                            .styleTween('opacity', function(d, i, a) {
                                var i = d3.interpolateRound(100, 0);
                                return function(t) {
                                    return i(t) / 100;
                                }
                            })
                            .style('display', 'none');
                }
            }

            //圈圈點下去的效果
            circle.transition()
                    .ease('bounce')
                    .duration(1000)
                    .styleTween('stroke-width', function(d, i, a) {
                        var i = d3.interpolateRound(100, 2);
                        return function(t) {
                            return i(t);
                        }
                    })
                    .attr('fill', t ? 'red' : 'block');

        });

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
    //隱藏所有的circle
    d3.selectAll('circle')
            .attr('fill', 'black')
            .attr('data-toggle', '0')
            .style('display', 'none');
};

var jsonSuccess = function(data) {
    //全部資料抓完才開始畫圖
    console.log(data.length);
    if (data.length) {
        //將每次取到的資料都存起來
        prevData = prevData.concat(data);
        angular.element('.controlPanel').scope().skip += angular.element('.controlPanel').scope().top;
        angular.element('.controlPanel').scope().submit();
        return;
    } else {
        angular.element('.controlPanel').scope().skip = 0;
    }

    //所有資料
    data = prevData;
    if (data.length === 0) {
        rankSection.append('div').append('h2').text('找不到資料');
        return;
    }

    //將原始資料的日期格式化
    $.each(data, function(i, d) {
        d.date = format.parse(d.date);
    });
    //如果skip為0，表示這次是第一次搜尋，要先將表格清空
    if (angular.element('.controlPanel').scope().skip === 0) {
        init();
    }

    //承接下面的變數，資料分群後，再將所有子元素合併成一維陣列
    var children = []
    //將資料分群，並將同一個作物的數值加總起來
    var nodes = d3.nest()
            .key(function(d) {
                return d.market;
            })
            .key(function(d) {
                return d.name;
            })
            .rollup(function(t) {
                var sum = {key: t[0].name, code: t[0].code, name: t[0].name, market: t[0].market, marketCode: t[0].marketCode,
                    date: t[0].date, cropCategory: t[0].cropCategory,
                    marketCount: d3.sum(t, function(v) {
                        return v.marketCount;
                    }), quantity: Math.round(d3.sum(t, function(v) {
                        return v.quantity;
                    }) * 10) / 10, amount: d3.sum(t, function(v) {
                        return v.price * v.quantity;
                    })};
                sum.price = sum.amount / sum.quantity;
                return sum;
            })
            .sortValues(function(a, b) {
                return selectProp(b) - selectProp(a);
            })
            .entries(data)
            .map(function(d, i) {
                var e = d.values.map(function(e) {
                    return e.values;
                }).sort(function(a, b) {
                    return selectProp(b) - selectProp(a);
                });
                return {key: d.key, values: e};
            });
    console.log(nodes);

    rankSection.selectAll('div').remove();
    var div = rankSection.selectAll('div')
            .data(nodes).enter()
            .append('div')
            .attr('class', 'rankTable')
            .attr('data-market', function(d) {
                //市場有資料才顯示圈圈
                d3.selectAll('circle[title="' + d.key + '"]')
                        .style('display', 'block');
                return d.key;
            })
            .style('display', 'none');
    div.append('h3')
            .text(function(d) {
                return d.key;
            });
    var table = div.append('table')
            .attr('class', '')
            .attr('data-market', function(d) {
                return d.key;
            });
    var thead = table.append('thead');
    thead.append('tr')
            .selectAll('td')
            .data(['#', '作物名稱', '交易量'])
            .enter()
            .append('th')
            .text(function(d) {
                return d;
            });
    var tbody = table.append('tbody');
    var tr = tbody.selectAll('tr')
            .data(function(d) {
                return d.values.slice(0, 10);
            }).enter()
            .append('tr')
            .attr('data-crop', function(d) {
                return d.key;
            })
            .on('mouseenter', function(d) {
                d3.selectAll('[data-crop="' + d.key + '"]').style('background-color', 'rgba(255, 255, 0, 0.5)');
            })
            .on('mouseleave', function(d) {
                d3.selectAll('[data-crop="' + d.key + '"]').style('background-color', null);
            });
    tr.selectAll('td')
            .data(function(d, i) {
                return ['', d.name, d.quantity];
            }).enter()
            .append('td')
            .text(function(d) {
                return d;
            });
};
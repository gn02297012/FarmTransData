var rankSection = d3.select('#rankSection');
var processStatus = 'day';

//讓排名的表格總是在最後一格顯示
var popupRankTable = function(market) {
    var tables = d3.selectAll('.rankTable[data-market="' + market + '"]')[0];
    $.each(tables, function(i, d) {
        d.parentNode.appendChild(d);
    });
};
//切換隱藏或顯示排名表格
var toggleRankTable = function(market, isShow) {
    d3.selectAll('.rankTable[data-market="' + market + '"]')
            .classed('showTable', isShow)
            .transition()
            .duration(1000)
            .styleTween('opacity', function(d, i, a) {
                var i = isShow ? d3.interpolateRound(0, 100) : d3.interpolateRound(100, 0);
                return function(t) {
                    return i(t) / 100;
                }
            })
            .style('display', isShow ? 'inline-block' : 'none');
};

//切換顯示本日或本月
$('#rankContorl .toggleRange button').on('click', function(event) {
    $(this).parent().children('.btn').toggleClass('btn-primary', false);
    $(this).parent().children('.btn').toggleClass('btn-default', true);
    $(this).toggleClass('btn-primary', true);

    var sec = $(this).data('toggle');
    var other = (sec === 'month' ? 'day' : 'month');
    $('div[data-sec="' + sec + '"]').fadeIn();
    $('div[data-sec="' + other + '"]').fadeOut();
});

//切換顯示蔬菜或水果
$('#rankContorl .toggleCategory button').on('click', function(event) {
    $(this).parent().children('.btn').toggleClass('btn-primary', false);
    $(this).parent().children('.btn').toggleClass('btn-default', true);
    $(this).toggleClass('btn-primary', true);

    var sec = $(this).data('toggle');
    var other = (sec == '1' ? '2' : '1');
    $('div[data-sec="' + sec + '"]').fadeIn();
    $('div[data-sec="' + other + '"]').fadeOut();
});

//本頁內容的初始化
var initPage = function() {
    d3.selectAll('circle')
            .attr('r', 10)
            .attr('data-toggle', 'tooltip')
            .attr('data-placement', 'top')
            .style('display', 'none')
            .on('mouseenter', function() {
                d3.select(this).attr('data-market', d3.select(this).attr('title'));
            })
            .on('mouseleave', function() {
            })
            .on('click', function() {
                var circle = d3.select(this);
                //切換顯示市場
                var t = circle.classed('toggleCircle');
                var market = circle.attr('data-market');
                t = !t;
                if (t) {
                    popupRankTable(market);
                }
                toggleRankTable(market, t);

                //圈圈點下去的效果
                circle.transition()
                        .ease('bounce')
                        .duration(1000)
                        .styleTween('stroke-width', function(d, i, a) {
                            var i = d3.interpolateRound(100, 2);
                            return function(t) {
                                return i(t);
                            }
                        });
                //切換class
                circle.classed('toggleCircle', t);
            });


    //顯示區域初始化
    rankSection.selectAll('div').remove();
    var sec = rankSection.selectAll('div')
            .data(['month', 'day']).enter()
            .append('div')
            .attr('data-sec', function(d) {
                return d;
            })
            .attr('class', function(d) {
                return d;
            })
            .style('display', function(d) {
                return (d === $('.toggleRange .btn-primary').data('toggle')) ? 'block' : 'none';
            })
            .html('<div>載入中請稍後 <i class="fa fa-spinner fa-spin fa-2x"></div>');
};
initPage();

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
            .attr('fill', 'black').attr('data-toggle', '0')
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
        rankSection.select('div.' + processStatus).append('div').append('h2').text('找不到資料');
        return;
    }

    //將原始資料的日期格式化
    $.each(data, function(i, d) {
        d.date = format.parse(d.date);
    });
    //如果skip為0，表示這次是第一次搜尋，要先將表格清空
    if (angular.element('.controlPanel').scope().skip === 0 && processStatus === 'day') {
        init();
    }
    //承接下面的變數，資料分群後，再將所有子元素合併成一維陣列
    var children = []
    //將資料分群，並將同一個作物的數值加總起來
    var nodes = d3.nest()
            .key(function(d) {
                return d.category;
            })
            .key(function(d) {
                return d.market;
            })
            .key(function(d) {
                return d.name;
            })
            .rollup(function(t) {
                var sum = {key: t[0].name, code: t[0].code, name: t[0].name, market: t[0].market, marketCode: t[0].marketCode,
                    date: t[0].date, cropCategory: t[0].cropCategory, category: t[0].category,
                    marketCount: d3.sum(t, function(v) {
                        return v.marketCount;
                    }), quantity: Math.round(d3.sum(t, function(v) {
                        return v.quantity;
                    }) * 10) / 10, amount: d3.sum(t, function(v) {
                        return v.price * v.quantity;
                    })};
                sum.price = sum.amount / sum.quantity;
                if (sum.category == '其他') {
                    console.log(sum);
                }
                return sum;
            })
            .sortValues(function(a, b) {
                return b.quantity - a.quantity;
            })
            .entries(data)
            .map(function(d, i) {
                var e = d.values.map(function(e) {
                    return {key: e.key, values: e.values.map(function(f) {
                            return f.values;
                        }).sort(function(a, b) {
                            return b.quantity - a.quantity;
                        })};
                });
                return {key: d.key, values: e};
            });
    console.log(nodes);


    rankSection.select('div.' + processStatus)
            .selectAll('div').remove();
    $.each(nodes, function(i, d) {
        drawEachTable(d.key, rankSection.select('div.' + processStatus), d);
    });

    if (processStatus === 'day') {
        processStatus = 'month';
        //預設顯示的市場資料
        var defaultShowMarkets = ['台北一', '台北二', '台中市', '高雄市'];
        $.each(defaultShowMarkets, function(i, d) {
            popupRankTable(d);
            toggleRankTable(d, true);
            d3.selectAll('circle[title="' + d + '"]')
                    .classed('toggleCircle', true);
        });
        //設定參數，繼續抓完本月的資料
        var d = new Date(angular.element('.controlPanel').scope().EndDate);
        if (d.getDate() > 1) {
            //先設定結束日期為前一天
            d.setDate(d.getDate() - 1);
            var endDate = formatDateInput(d);
            angular.element('.controlPanel').scope().EndDate = endDate;
            //設起開始日期為本月第一天
            d.setDate(1);
            var startDate = formatDateInput(d);
            angular.element('.controlPanel').scope().StartDate = startDate;
            angular.element('.controlPanel').scope().submit();
        } else {
            //如果今天是本月的第一天，就直接複製本日排行到本月排行
            $('#rankSection .month').append($('#rankSection .day').clone());
        }
    } else {
        processStatus = 'day';
        //預設顯示已經有顯示的市場資料
        var tables = d3.selectAll('.rankTable.showTable');
        $.each(tables[0], function(i, d) {
            var market = d.getAttribute('data-market');
            toggleRankTable(market, true);
        });
    }
};

var drawEachTable = function(tag, sec, nodes) {
    var sec = sec.append('div')
            .attr('data-sec', tag)
            .classed('cropCategorySection', 1)
            .style('display', function(d) {
                return (tag == $('.toggleCategory .btn-primary').data('toggle')) ? 'block' : 'none';
            });
    var div = sec.selectAll('div')
            .data(nodes.values).enter()
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
            .attr('class', 'table table-condensed')
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
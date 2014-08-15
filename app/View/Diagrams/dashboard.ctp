<style>
    path {  stroke: #fff; }
    path:hover {  opacity:0.9; }
    rect:hover {  fill:blue; }

    .axis {
        font: 10px sans-serif;
    }
    .axis path, .axis line {
        fill: none;
        stroke: #000;
        shape-rendering: crispEdges;
    }
    .x.axis path {
        display: none;
    }
</style>
<script>
    //設定ControlPanelCtrl的參數
    $(document).ready(function() {
        angular.element('.controlPanel').scope().$apply(function($scope, $http) {
            $scope.showControlPanel = true;
            $scope.showAllCrop = false;
            $scope.showMarket = false;
            $scope.baseUrl = '<?php echo $this->Html->webroot('/query/dashboard'); ?>';
            //$scope.Crop = '';
            //$scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 30);
            //$scope.EndDate = formatDateInput(new Date());
            $scope.top = 3000;
            $scope.skip = 0;

            $scope.submit = function() {
                var cat = $scope.selCat.cat;
                var url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + $scope.Crop + '&StartDate=' + formatROCDate($scope.StartDate) + '&EndDate=' + formatROCDate($scope.EndDate);
                $scope.getData(url, dashboard, window.location.pathname);
            };
        });
    });
</script>

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
</form>

<div class="svgSection">
</div>

<script>
    var color = d3.scale.category20();
    var markets = JSON.parse('<?php echo json_encode($markets_raw); ?>');

    //圖表要根據交易量還是價量呈現
    var showType = function(d) {
        return d.quantity;
    };

    function dashboard(fData) {
        var id = '.svgSection';
        if (fData.length) {
            $(id).html('');
        } else {
            $(id).html('找不到資料');
            return false;
        }

        var barColor = 'steelblue';
        function segColor(c) {
            return color(c);
        }

        var count = 0;
        var keyLengthMax = 4;

        //畫出長條圖
        function histoGram(fD) {
            var hG = {}, hGDim = {t: 60, r: 0, b: 30, l: 0};
            hGDim.w = (count < 3 ? 2 : count) * keyLengthMax * 11 - hGDim.l - hGDim.r,
                    hGDim.h = 300 - hGDim.t - hGDim.b;

            var hGsvg = d3.select(id).append("svg")
                    .attr("width", hGDim.w + hGDim.l + hGDim.r)
                    .attr("height", hGDim.h + hGDim.t + hGDim.b).append("g")
                    .attr("transform", "translate(" + hGDim.l + "," + hGDim.t + ")");

            var x = d3.scale.ordinal().rangeRoundBands([0, hGDim.w], 0.1)
                    .domain(fD.map(function(d) {
                        return d[0];
                    }));

            hGsvg.append("g").attr("class", "x axis")
                    .attr("transform", "translate(0," + hGDim.h + ")")
                    .call(d3.svg.axis().scale(x).orient("bottom"));

            var y = d3.scale.linear().range([hGDim.h, 0])
                    .domain([0, d3.max(fD, function(d) {
                            return d[1];
                        })]);

            var bars = hGsvg.selectAll(".bar").data(fD).enter()
                    .append("g").attr("class", "bar");

            bars.append("rect")
                    .attr("x", function(d) {
                        return x(d[0]);
                    })
                    .attr("y", function(d) {
                        return y(d[1]);
                    })
                    .attr("width", x.rangeBand())
                    .attr("height", function(d) {
                        return hGDim.h - y(d[1]);
                    })
                    .attr('fill', barColor)
                    .on("mouseover", mouseover)
                    .on("mouseout", mouseout);

            bars.append("text")
                    .text(function(d) {
                        return d3.format(",")(d[1])
                    })
                    .attr("x", function(d) {
                        return x(d[0]) + x.rangeBand() / 2;
                    })
                    .attr("y", function(d) {
                        return y(d[1]) - 5;
                    })
                    .attr("text-anchor", "middle");

            function mouseover(d) {
                var st = fData.filter(function(s) {
                    return s.name == d[0];
                })[0];
                var nD = $.map(tF, function(s) {
                    return {type: s.type, total: (st.markets[s.type] == undefined) ? 0 : showType(st.markets[s.type])};
                });
                pC.update(nD);
                leg.update(nD);
            }

            function mouseout(d) {
                pC.update(tF);
                leg.update(tF);
            }

            hG.update = function(nD, color) {
                y.domain([0, d3.max(nD, function(d) {
                        return d[1];
                    })]);

                var bars = hGsvg.selectAll(".bar").data(nD);

                bars.select("rect").transition().duration(500)
                        .attr("y", function(d) {
                            return y(d[1]);
                        })
                        .attr("height", function(d) {
                            return hGDim.h - y(d[1]);
                        })
                        .attr("fill", color ? color : function(d) {
                            //如果有給顏色就使用，否則就自動產生顏色
                            return segColor(d[2]);
                        });

                bars.select("text").transition().duration(500)
                        .text(function(d) {
                            return d3.format(",")(d[1])
                        })
                        .attr("y", function(d) {
                            return y(d[1]) - 5;
                        });
            }
            return hG;
        }

        //畫出圓餅圖
        function pieChart(pD) {
            var pC = {}, pieDim = {w: 250, h: 250};
            pieDim.r = Math.min(pieDim.w, pieDim.h) / 2;

            var piesvg = d3.select(id).append("svg")
                    .attr("width", pieDim.w).attr("height", pieDim.h).append("g")
                    .attr("transform", "translate(" + pieDim.w / 2 + "," + pieDim.h / 2 + ")");

            var arc = d3.svg.arc().outerRadius(pieDim.r - 10).innerRadius(0);

            var pie = d3.layout.pie().sort(null).value(function(d) {
                return d.total;
            });

            piesvg.selectAll("path").data(pie(pD)).enter().append("path").attr("d", arc)
                    .each(function(d) {
                        this._current = d;
                    })
                    .style("fill", function(d) {
                        return segColor(d.data.type);
                    })
                    .on("mouseover", mouseover).on("mouseout", mouseout);

            pC.update = function(nD) {
                piesvg.selectAll("path").data(pie(nD)).transition().duration(500)
                        .attrTween("d", arcTween);
            }

            function mouseover(d) {
                hG.update(fData.map(function(v) {
                    return [v.name, (v.markets[d.data.type] == undefined) ? 0 : showType(v.markets[d.data.type]), d.data.type];
                }));
            }

            function mouseout(d) {
                hG.update(fData.map(function(v) {
                    return [v.name, Math.floor(v.total * 100) / 100];
                }), barColor);
            }

            function arcTween(a) {
                var i = d3.interpolate(this._current, a);
                this._current = i(0);
                return function(t) {
                    return arc(i(t));
                };
            }
            return pC;
        }

        //畫出圖例與資料數值
        function legend(lD) {
            var leg = {};

            var legend = d3.select(id).append("table").attr('class', 'legend');

            var tr = legend.append("tbody").selectAll("tr").data(lD).enter().append("tr");

            tr.append("td").append("svg").attr("width", '16').attr("height", '16').append("rect")
                    .attr("width", '16').attr("height", '16')
                    .attr("fill", function(d) {
                        return segColor(d.type);
                    });

            tr.append("td").text(function(d) {
                return d.market;
            });

            tr.append("td").attr("class", 'legendFreq')
                    .text(function(d) {
                        return d3.format(",")(d.total);
                    });

            tr.append("td").attr("class", 'legendPerc')
                    .text(function(d) {
                        return getLegend(d, lD);
                    });

            leg.update = function(nD) {
                var l = legend.select("tbody").selectAll("tr").data(nD);

                l.select(".legendFreq").text(function(d) {
                    return d3.format(",")(d.total);
                });

                l.select(".legendPerc").text(function(d) {
                    return getLegend(d, nD);
                });
            }

            function getLegend(d, aD) {
                return d3.format("%")(d.total / d3.sum(aD.map(function(v) {
                    return v.total;
                })));
            }

            return leg;
        }

        var tF, sF;

        function calc() {
            //計算市場的總和
            tF = $.map(markets, function(d, i) {
                return {type: d, market: i, total: Math.floor(d3.sum(fData.map(function(t) {
                        return (t.markets[d] == undefined) ? 0 : showType(t.markets[d]);
                    })) * 100) / 100};
            });
            //把total為0的市場剔除
            tF = tF.filter(function(d) {
                return d.total;
            });
            //計算各作物的總和
            keyLengthMax = 4;
            fData.forEach(function(d) {
                var values = $.map(d.markets, function(d, i) {
                    return showType(d);
                });
                //找出最長的字數，用來計算svg的寬度
                if (d.name.length > keyLengthMax) {
                    keyLengthMax = d.name.length;
                }
                d.total = Math.floor(d3.sum(values) * 100) / 100;
            });
            count = fData.length;
            sF = fData.map(function(d) {
                return [d.name, d.total, d.total2];
            });
        }

        //切換顯示類型
        d3.selectAll("[name='mode']").on("change", function change() {
            switch (this.value) {
                case 'amount':
                    showType = function(d) {
                        return d.amount;
                    };
                    break;
                default:
                    showType = function(d) {
                        return d.quantity;
                    };
                    break;
            }
            calc();
            hG.update(sF, barColor);
            pC.update(tF);
            leg.update(tF);
        });
        calc();
        var hG = histoGram(sF),
                pC = pieChart(tF),
                leg = legend(tF);
    }
</script>
<style>
    body{

    }
    path {  stroke: #fff; }
    path:hover {  opacity:0.9; }
    rect:hover {  fill:blue; }
    .axis {  font: 10px sans-serif; }
    .legend tr{    border-bottom:1px solid grey; }
    .legend tr:first-child{    border-top:1px solid grey; }

    .axis path,
    .axis line {
        fill: none;
        stroke: #000;
        shape-rendering: crispEdges;
    }

    .x.axis path {  display: none; }
    .legend{
        margin-bottom:76px;
        display:inline-block;
        border-collapse: collapse;
        border-spacing: 0px;
        transform: translate(0,50px);
    }
    .legend td{
        padding:4px 5px;
        vertical-align:bottom;
    }
    .legendFreq, .legendPerc{
        align:right;
        width:50px;
    }

</style>

<script>
    function cpCtrl($scope, $http) {
        //作物名稱選單
        $scope.categorys = JSON.parse('<?php echo json_encode([['name' => '水果', 'items' => $fruits], ['name' => '蔬菜', 'items' => $vegetables]]); ?>');
        $scope.items = $scope.categorys[0]['items'];
        //API參數
        $scope.baseUrl = '<?php echo $this->Html->webroot('/query/dashboard'); ?>';
        $scope.Crop = '';
        $scope.StartDate = '<?php echo date('Y-m-d', time() - 86400 * 30); ?>';
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
            $url = $scope.baseUrl + '?$top=' + $scope.top + '&$skip=' + $scope.skip + '&Crop=' + $scope.Crop + '&StartDate=' + d($scope.StartDate) + '&EndDate=' + d($scope.EndDate);
            $http.get($url).success(function(data) {
                dashboard('#dashboard', data);
            });
        }
    }
</script>
<div class="controlPanel ng-scope" ng-controller="cpCtrl">
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

<div id='dashboard'>
</div>

<script>    
    var color = d3.scale.category20();
    var markets = JSON.parse('<?php echo json_encode($markets); ?>');

    function dashboard(id, fData) {
        $(id).html('');

        var barColor = 'steelblue';
        function segColor(c) {
            return color(c);
        }

        var count = 0;
        var keyLengthMax = 0;
        
        // compute total for each state.
        fData.forEach(function(d) {
            var values = $.map(d.markets, function(d, i) {
                return d.quantity;
            });
            if (d.name.length > keyLengthMax) keyLengthMax = d.name.length;
            count++;
            d.total = d3.sum(values);
        });

        // function to handle histogram.
        function histoGram(fD) {
            var hG = {}, hGDim = {t: 60, r: 0, b: 30, l: 0};
            hGDim.w = count*keyLengthMax*11 - hGDim.l - hGDim.r,
                    hGDim.h = 300 - hGDim.t - hGDim.b;

            //create svg for histogram.
            var hGsvg = d3.select(id).append("svg")
                    .attr("width", hGDim.w + hGDim.l + hGDim.r)
                    .attr("height", hGDim.h + hGDim.t + hGDim.b).append("g")
                    .attr("transform", "translate(" + hGDim.l + "," + hGDim.t + ")");

            // create function for x-axis mapping.
            var x = d3.scale.ordinal().rangeRoundBands([0, hGDim.w], 0.1)
                    .domain(fD.map(function(d) {
                        return d[0];
                    }));

            // Add x-axis to the histogram svg.
            hGsvg.append("g").attr("class", "x axis")
                    .attr("transform", "translate(0," + hGDim.h + ")")
                    .call(d3.svg.axis().scale(x).orient("bottom"));

            // Create function for y-axis map.
            var y = d3.scale.linear().range([hGDim.h, 0])
                    .domain([0, d3.max(fD, function(d) {
                            return d[1];
                        })]);

            // Create bars for histogram to contain rectangles and freq labels.
            var bars = hGsvg.selectAll(".bar").data(fD).enter()
                    .append("g").attr("class", "bar");

            //create the rectangles.
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
                    .on("mouseover", mouseover)// mouseover is defined below.
                    .on("mouseout", mouseout);// mouseout is defined below.

            //Create the frequency labels above the rectangles.
            bars.append("text").text(function(d) {
                return d3.format(",")(d[1])
            })
                    .attr("x", function(d) {
                        return x(d[0]) + x.rangeBand() / 2;
                    })
                    .attr("y", function(d) {
                        return y(d[1]) - 5;
                    })
                    .attr("text-anchor", "middle");

            function mouseover(d) {  // utility function to be called on mouseover.
                // filter for selected state.
                var st = fData.filter(function(s) {
                    return s.name == d[0];
                })[0];
                var nD = $.map(tF, function(s) {
                    return {type: s.type, total: (st.markets[s.type] == undefined) ? 0 : st.markets[s.type].quantity};
                });
                // call update functions of pie-chart and legend.    
                pC.update(nD);
                leg.update(nD);
            }

            function mouseout(d) {    // utility function to be called on mouseout.
                // reset the pie-chart and legend.    
                pC.update(tF);
                leg.update(tF);
            }

            // create function to update the bars. This will be used by pie-chart.
            hG.update = function(nD, color) {
                // update the domain of the y-axis map to reflect change in frequencies.
                y.domain([0, d3.max(nD, function(d) {
                        return d[1];
                    })]);

                // Attach the new data to the bars.
                var bars = hGsvg.selectAll(".bar").data(nD);

                // transition the height and color of rectangles.
                bars.select("rect").transition().duration(500)
                        .attr("y", function(d) {
                            return y(d[1]);
                        })
                        .attr("height", function(d) {
                            return hGDim.h - y(d[1]);
                        })
                        .attr("fill", color);

                // transition the frequency labels location and change value.
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

        // function to handle pieChart.
        function pieChart(pD) {
            var pC = {}, pieDim = {w: 250, h: 250};
            pieDim.r = Math.min(pieDim.w, pieDim.h) / 2;

            // create svg for pie chart.
            var piesvg = d3.select(id).append("svg")
                    .attr("width", pieDim.w).attr("height", pieDim.h).append("g")
                    .attr("transform", "translate(" + pieDim.w / 2 + "," + pieDim.h / 2 + ")");

            // create function to draw the arcs of the pie slices.
            var arc = d3.svg.arc().outerRadius(pieDim.r - 10).innerRadius(0);

            // create a function to compute the pie slice angles.
            var pie = d3.layout.pie().sort(null).value(function(d) {
                //console.log(d);
                return d.total;
            });

            // Draw the pie slices.
            piesvg.selectAll("path").data(pie(pD)).enter().append("path").attr("d", arc)
                    .each(function(d) {
                        this._current = d;
                    })
                    .style("fill", function(d) {
                        return segColor(d.data.type);
                    })
                    .on("mouseover", mouseover).on("mouseout", mouseout);

            // create function to update pie-chart. This will be used by histogram.
            pC.update = function(nD) {
                piesvg.selectAll("path").data(pie(nD)).transition().duration(500)
                        .attrTween("d", arcTween);
            }
            // Utility function to be called on mouseover a pie slice.
            function mouseover(d) {
                // call the update function of histogram with new data.
                hG.update(fData.map(function(v) {
                    return [v.name, (v.markets[d.data.type] == undefined) ? 0 : v.markets[d.data.type].quantity];
                }), segColor(d.data.type));
            }
            //Utility function to be called on mouseout a pie slice.
            function mouseout(d) {
                // call the update function of histogram with all data.
                hG.update(fData.map(function(v) {
                    return [v.name, v.total];
                }), barColor);
            }
            // Animating the pie-slice requiring a custom function which specifies
            // how the intermediate paths should be drawn.
            function arcTween(a) {
                var i = d3.interpolate(this._current, a);
                this._current = i(0);
                return function(t) {
                    return arc(i(t));
                };
            }
            return pC;
        }

        // function to handle legend.
        function legend(lD) {
            var leg = {};

            // create table for legend.
            var legend = d3.select(id).append("table").attr('class', 'legend');

            // create one row per segment.
            var tr = legend.append("tbody").selectAll("tr").data(lD).enter().append("tr");

            // create the first column for each segment.
            tr.append("td").append("svg").attr("width", '16').attr("height", '16').append("rect")
                    .attr("width", '16').attr("height", '16')
                    .attr("fill", function(d) {
                        return segColor(d.type);
                    });

            // create the second column for each segment.
            tr.append("td").text(function(d) {
                return d.market;
            });

            // create the third column for each segment.
            tr.append("td").attr("class", 'legendFreq')
                    .text(function(d) {
                        return d3.format(",")(d.total);
                    });

            // create the fourth column for each segment.
            tr.append("td").attr("class", 'legendPerc')
                    .text(function(d) {
                        return getLegend(d, lD);
                    });

            // Utility function to be used to update the legend.
            leg.update = function(nD) {
                // update the data attached to the row elements.
                var l = legend.select("tbody").selectAll("tr").data(nD);

                // update the frequencies.
                l.select(".legendFreq").text(function(d) {
                    return d3.format(",")(d.total);
                });

                // update the percentage column.
                l.select(".legendPerc").text(function(d) {
                    return getLegend(d, nD);
                });
            }

            function getLegend(d, aD) { // Utility function to compute percentage.
                return d3.format("%")(d.total / d3.sum(aD.map(function(v) {
                    return v.total;
                })));
            }

            return leg;
        }

        // calculate total frequency by segment for all state.
        var tF = $.map(markets, function(d, i) {
            return {type: d, market: i, total: d3.sum(fData.map(function(t) {
                    return (t.markets[d] == undefined) ? 0 : t.markets[d].quantity;
                }))};
        });
        //把total為0的市場剔除
        tF = tF.filter(function(d) {
            return d.total;
        });

        // calculate total frequency by state for all segment.
        var sF = fData.map(function(d) {
            return [d.name, d.total];
        });

        var hG = histoGram(sF), // create the histogram.
                pC = pieChart(tF), // create the pie-chart.
                leg = legend(tF);  // create the legend.
    }
</script>
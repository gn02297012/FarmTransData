<form>
    <label><input type="radio" name="mode" value="amount"> 價量</label>
    <label><input type="radio" name="mode" value="quantity"> 交易量</label>
    <label><input type="radio" name="mode" value="count" checked> 種類</label>
</form>
<script>
    var width = 850,
            height = 900,
            radius = Math.min(width, height) / 2,
            color = d3.scale.category20c();

    var x = d3.scale.linear()
            .range([0, 2 * Math.PI]);

    var y = d3.scale.linear()
            .range([0, radius]);

    var svg = d3.select("body").append("svg")
            .attr("width", width)
            .attr("height", height)
            .append("g")
            .attr("transform", "translate(" + width / 2 + "," + (height / 2 + 10) + ")");

    var partition = d3.layout.partition()
            .value(function(d) {
                return 1;
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
                    console.log(percentageString);
                })
                .each(stash);
        totalValue = path.node().__data__.value;
        function click(d) {
            node = d;
            path.transition()
                    .duration(1000)
                    .attrTween("d", arcTweenZoom(d));
        }

        d3.selectAll("input").on("change", function change() {
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
    d3.json("<?php echo $this->webroot; ?>query/partition?$top=100&$skip=0&Category=2", jsonSuccess);
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
        tooltip.css("opacity", 1);
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
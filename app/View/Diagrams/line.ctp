<style>

    body {
        font: 10px sans-serif;
    }

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

</style>

<script>

    var margin = {top: 20, right: 20, bottom: 80, left: 50},
    width = 960 - margin.left - margin.right,
            height = 600 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%Y.%m.%d").parse;

    var x = d3.time.scale()
            .range([0, width]);

    var y = d3.scale.linear()
            .range([height, 0]);

    var xAxis = d3.svg.axis()
            .scale(x)
            .orient("bottom")
            .tickFormat(function(d) {
                return (d.getYear() + 1900) + '/' + (d.getMonth() + 1) + '/' + (d.getDate() + 1);
            });

    var yAxis = d3.svg.axis()
            .scale(y)
            .orient("left");

    var line = d3.svg.line()
            .x(function(d) {
                return x(d.date);
            })
            .y(function(d) {
                return y(d.price);
            });

    var svg = d3.select("body").append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    d3.json("<?php echo $this->webroot; ?>query/line?$top=1000&$skip=0&Crop=大西瓜&StartDate=103.04.01", function(error, data) {
        data.forEach(function(d) {
            d.date = parseDate(d.date);
        });

        x.domain(d3.extent(data, function(d) {
            return d.date;
        }));
        y.domain(d3.extent(data, function(d) {
            return d.price;
        }));

        svg.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(xAxis).selectAll("text")
                .style("text-anchor", "end")
                .attr("dx", "-.8em")
                .attr("dy", ".15em")
                .attr("transform", function(d) {
                    return "rotate(-65)"
                });

        svg.append("g")
                .attr("class", "y axis")
                .call(yAxis)
                .append("text")
                .attr("transform", "rotate(-90)")
                .attr("y", 6)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                .text("Price ($)");

        svg.append("path")
                .datum(data)
                .attr("class", "line")
                .attr("d", line);
    });

</script>
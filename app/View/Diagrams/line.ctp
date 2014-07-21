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

<form>
    <input type="text" id="Crop"><br>
    <input type="button" id="submit" value="View">
</form>

<script>
    $('#submit').on('click', function(){
        d3.json("<?php echo $this->webroot; ?>query/line?$top=1000&$skip=0&Crop=" + $('#Crop').val() + "&StartDate=103.02.01", jsonSuccess);
    });
    $('form').on('submit', function(){
        $('#submit').click();
        return false;
    });
    
    var margin = {top: 80, right: 20, bottom: 80, left: 50},
    width = 960 - margin.left - margin.right,
            height = 600 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%Y.%m.%d").parse;

    var x = d3.time.scale()
            .range([0, width]);

    var y = d3.scale.linear()
            .range([height, 0]);

    var color = d3.scale.category10();

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

    var jsonSuccess = function(error, data) {
        svg.selectAll('g').remove();
        
        var nested_data = d3.nest()
                .key(function(d) {
                    return d.name;
                })
                .entries(data);

        color.domain(nested_data.map(function(d) {
            return d.key;
        }));

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

        var item = svg.selectAll(".item")
                .data(nested_data)
                .enter().append("g")
                .attr("class", "item");

        item.append("path")
                .attr("class", "line")
                .attr("d", function(d) {
                    return line(d.values);
                })
                .style("stroke", function(d) {
                    return color(d.key);
                });

        item.append("text")
                .datum(function(d) {
                    return {name: d.key, value: d.values[d.values.length - 1]};
                })
                .attr("transform", function(d) {
                    return "translate(" + x(d.value.date) + "," + (y(d.value.price)-7) + ")";
                })
                .attr("x", 3)
                .attr("dy", ".35em")
                .text(function(d) {
                    return d.value.name;
                });
    };
    
    d3.json("<?php echo $this->webroot; ?>query/line?$top=500&$skip=0&Crop=蘋果鳳梨&StartDate=103.04.01", jsonSuccess);

</script>
function PieChart(data, groups, groupIds, highChartOptions)
{
    this.data = data;
    this.groups = groups;
    this.groupIds = groupIds;
    this.primaryData = [];
    this.secondaryData = [];
    this.colors = null;
    this.highChartOptions = highChartOptions;
}

PieChart.prototype.getColors = function()
{
    this.colors = Highcharts.getOptions().colors;
    return this.colors;
}

PieChart.prototype.getPrimaryData = function()
{
    for (var i = 0; i < this.data.length; i++) {
        this.primaryData.push({
            name  : this.groups[i],
            id    : this.groupIds[i],
            type  : "group",
            y     : this.data[i].y,
            color : this.data[i].color
        });
    }

    return this.primaryData;
}

PieChart.prototype.getSecondaryData = function()
{
    for (var i = 0; i < this.data.length; i++) {
        for (var j = 0; j < this.data[i].drilldown.data.length; j++) {
            var brightness = 0.2 - (j / this.data[i].drilldown.data.length) / 5;
            this.secondaryData.push({
                name     : this.data[i].drilldown.items[j],
                type     : this.data[i].drilldown.types[j],
                y        : this.data[i].drilldown.data[j],
                color    : Highcharts.Color(this.data[i].color).brighten(brightness).get(),
                id_group : this.data[i].drilldown.id_groups[j],
                id_item  : this.data[i].drilldown.id_items[j]
            });
        }
    }

    return this.secondaryData;
}
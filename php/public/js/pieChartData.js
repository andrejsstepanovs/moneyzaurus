function PieChartData()
{

}

PieChartData.prototype.setGroupIds = function(groupIds)
{
    this.groupIds = groupIds;
    return this;
}

PieChartData.prototype.setGroups = function(groups)
{
    this.groups = groups;
    return this;
}

PieChartData.prototype.setData = function(data)
{
    this.data = data;
    return this;
}

PieChartData.prototype.getColors = function()
{
    if (typeof this.colors == "undefined") {
        this.colors = Highcharts.getOptions().colors;
    }
    return this.colors;
}

PieChartData.prototype.getPrimaryData = function()
{
    if (typeof this.primaryData == "undefined") {
        this.primaryData = [];
        for (var i = 0; i < this.data.length; i++) {
            this.primaryData.push({
                name  : this.groups[i],
                id    : this.groupIds[i],
                type  : "group",
                y     : this.data[i].y,
                color : this.data[i].color
            });
        }
    }
    return this.primaryData;
}

PieChartData.prototype.getSecondaryData = function()
{
    if (typeof this.secondaryData == "undefined") {
        this.secondaryData = [];
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
    }
    return this.secondaryData;
}
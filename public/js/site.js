$(document).bind("mobileinit", function(){
    $.extend($.mobile, {
        ajaxEnabled: false
    });

    function pieChartSliceSelected(primary, secondary)
    {
        console.log(primary);
        console.log(secondary);
    }
});

function renderChart(data, groups, groupIds)
{
    for (var i = 0; i < data.length; i++) {

        primaryData.push({
            name  : groups[i],
            id    : groupIds[i],
            type  : "group",
            y     : data[i].y,
            color : data[i].color
        });

        for (var j = 0; j < data[i].drilldown.data.length; j++) {
            var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5;
            secondaryData.push({
                name     : data[i].drilldown.items[j],
                type     : data[i].drilldown.types[j],
                y        : data[i].drilldown.data[j],
                color    : Highcharts.Color(data[i].color).brighten(brightness).get(),
                id_group : data[i].drilldown.id_groups[j],
                id_item  : data[i].drilldown.id_items[j]
            });
        }
    }
}
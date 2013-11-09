function PieChartRender(highChartOptions)
{
    this.highChartOptions = highChartOptions;
}

PieChartRender.prototype.renderChart = function()
{
    this.highChart = new Highcharts.Chart(this.highChartOptions);
}
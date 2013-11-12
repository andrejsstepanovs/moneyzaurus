function PieChartRender(highChartOptions)
{
    this.highChartOptions = highChartOptions;
    this.highChart = null;
}

PieChartRender.prototype.renderChart = function()
{
    if (this.highChart == null) {
        this.highChart = new Highcharts.Chart(this.highChartOptions);
    }
}
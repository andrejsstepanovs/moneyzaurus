<?php
namespace Application\Helper\Pie;

use Application\Helper\AbstractHelper;
use HighchartsPHP\Highcharts as Highcharts;
use HighchartsPHP\HighchartsJsExpr as HighchartJsExpr;

/**
 * Class Highchart
 *
 * @package Application\Helper\Pie
 */
class Highchart extends AbstractHelper
{
    /** @var Highcharts */
    protected $_chart;

    /** @var int */
    protected $_charDataIterator = 0;

    /**
     * @return \HighchartsPHP\Highcharts
     */
    public function getMainChart()
    {
        $chart = new Highcharts();

        $chart->chart->renderTo = 'container';
        $chart->chart->type     = 'pie';
        $chart->title->text     = 'Pie Chart';

        $chart->plotOptions->pie->allowPointSelect = true;
        $chart->plotOptions->pie->dataLabels->enabled = true;
        $chart->plotOptions->pie->shadow = true;

        $chart->series[0]->dataLabels->distance = -80;
        $chart->series[0]->dataLabels->color = 'white';
        $chart->series[0]->name      = 'EUR';
        $chart->series[0]->data      = new HighchartJsExpr('primaryData');
        $chart->series[0]->size      = '80%';
        $chart->series[0]->point->events->click = new HighchartJsExpr(
            'function (e) {
                console.log(this.name);
                console.log(this);
            }'
        );

        $chart->series[1]->point->events->click = new HighchartJsExpr(
            'function (e) {
                var parentSerie = this.options.parentId;
                console.log(parentSerie);
                console.log(this.name);
                console.log(this);
                //pieChartSliceSelected();
            }'
        );
        $chart->series[1]->dataLabels->enabled = false;
        $chart->series[1]->name      = 'EUR';
        $chart->series[1]->data      = new HighchartJsExpr('secondaryData');
        $chart->series[1]->innerSize = '80%';

        return $chart;
    }

    /**
     * @param array  $priceData
     * @param string $categories
     * @param string $groupName
     *
     * @return $this
     */
    public function setChartData($priceData, $categories)
    {
        $i = $this->_charDataIterator++;

        if (null === $this->_chart) {
            $this->_chart = new Highcharts();
        }
        $this->_chart[$i]->y                     = array_sum($priceData);
        $this->_chart[$i]->z                     = 'EUR';
        $this->_chart[$i]->color                 = new HighchartJsExpr('colors[' . $i . ']');
        $this->_chart[$i]->drilldown->name       = '';
        $this->_chart[$i]->drilldown->categories = array_values($categories);
        $this->_chart[$i]->drilldown->data       = $priceData;
        $this->_chart[$i]->drilldown->color      = new HighchartJsExpr('colors[0]');
        $this->_chart[$i]->drilldown->ids        = array_keys($categories);

        return $this;
    }

    /**
     * @return Highcharts
     */
    public function getChartData()
    {
        return $this->_chart;
    }
}
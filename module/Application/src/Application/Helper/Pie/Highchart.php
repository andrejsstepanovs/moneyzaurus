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
     * @param string     $title
     * @param string     $elementId  html element id
     * @param string     $prefix
     * @param null|array $parameters
     *
     * @return \HighchartsPHP\Highcharts
     */
    public function getMainChart($title, $elementId, $prefix, array $parameters = null)
    {
        $chart = new Highcharts();

        $chart->chart->renderTo = $elementId;
        $chart->chart->type     = 'pie';
        $chart->title->text     = $title;

        $chart->plotOptions->pie->allowPointSelect = true;
        $chart->plotOptions->pie->dataLabels->enabled = true;
        $chart->plotOptions->pie->shadow = true;

        $chart->series[0]->dataLabels->distance = -80;
        $chart->series[0]->dataLabels->color    = 'white';
        $chart->series[0]->name                 = 'EUR';
        $chart->series[0]->data                 = new HighchartJsExpr($prefix . 'primaryData');
        $chart->series[0]->size                 = '80%';
        $chart->series[0]->point->events->click = new HighchartJsExpr($this->_getSubPieChartJs($parameters));

        $chart->series[1]->point->events->click = new HighchartJsExpr($this->_getSubPieChartJs($parameters));
        $chart->series[1]->dataLabels->enabled = false;
        $chart->series[1]->name                = 'EUR';
        $chart->series[1]->data                = new HighchartJsExpr($prefix . 'secondaryData');
        $chart->series[1]->innerSize           = '80%';

        return $chart;
    }

    /**
     * @param null|array $parameters
     *
     * @return string
     */
    protected function _getSubPieChartJs(array $parameters = null)
    {
        $defaultParameters = array(
            'id'       => 'this.id',
            'type'     => 'this.type',
            'id_item'  => 'this.id_item',
            'id_group' => 'this.id_group'
        );
        if ($parameters) {
            $defaultParameters = array_merge($parameters, $defaultParameters);
        }

        $jsonData = json_encode($defaultParameters);

        return 'function (e) {
            var url = "pie/ajax";
            var data = ' . $jsonData . ';

            $.getJSON(url, data)
                .done (function(json) {
                    if (json.success) {
                        jQuery.globalEval(json.script);
                    }
                })
                .fail (function(jqxhr, textStatus, error) {
                    var err = textStatus + ", " + error;
                    console.log("Request Failed: " + err);
                });
        }';
    }

    /**
     * @param array $priceData
     * @param array $data
     *
     * @return $this
     */
    public function setChartData(array $priceData, array $data)
    {
        $i = $this->_charDataIterator++;

        if (null === $this->_chart) {
            $this->_chart = new Highcharts();
        }

        $itemNames = $groupIds = $itemIds = $types = array();
        foreach ($data as $itemData) {
            $itemNames[] = $itemData['name'];
            $groupIds[]  = $itemData['id_group'];
            $itemIds[]   = $itemData['id_item'];
            $types[]     = $itemData['type'];
        }

        $this->_chart[$i]->color                = new HighchartJsExpr('colors[' . $i . ']');
        $this->_chart[$i]->drilldown->color     = new HighchartJsExpr('colors[0]');
        $this->_chart[$i]->y                    = array_sum($priceData);
        $this->_chart[$i]->z                    = 'EUR';
        $this->_chart[$i]->drilldown->data      = $priceData;
        $this->_chart[$i]->drilldown->items     = $itemNames;
        $this->_chart[$i]->drilldown->id_groups = $groupIds;
        $this->_chart[$i]->drilldown->id_items  = $itemIds;
        $this->_chart[$i]->drilldown->types     = $types;

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
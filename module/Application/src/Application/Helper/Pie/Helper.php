<?php

namespace Application\Helper\Pie;

use Application\Helper\AbstractHelper;
use HighchartsPHP\Highcharts as Highchart;
use HighchartsPHP\HighchartsJsExpr as HighchartJsExpr;
use Zend\Db\Sql\Select;
use Zend\Http\PhpEnvironment\Request;


/**
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @method Helper setRequest(Request $request)
 * @method Helper setTransactionsDataValue(array $data)
 * @method Helper setSortedGroupsDataValue(array $data)
 * @method Helper setChartDataValue(array $data)
 * @method Helper setGroupedDataValue(array $data)
 * @method array getTransactionsDataValue()
 * @method array getSortedGroupsDataValue()
 * @method array getChartDataValue()
 * @method array getGroupedDataValue()
 */
class Helper extends AbstractHelper
{
    /** @var int */
    protected $_charDataIterator = 0;

    /** @var \Highchart */
    protected $_chartData;

    /** @var int */
    protected $_groupCount = 5;

    /**
     * @return array
     */
    private function getGroupedData()
    {
        if (null === $this->getGroupedDataValue()) {
            $groupedData = array();
            $transactionsData = $this->getTransactionsData();
            if ($transactionsData) {
                foreach ($transactionsData as $model) {
                    $groupedData[$model['group_name']][] = $model;
                }
            }
            $this->setGroupedDataValue($groupedData);
        }
        return $this->getGroupedDataValue();
    }

    /**
     * @return Highchart
     */
    public function getChartData()
    {
        if (null === $this->getChartDataValue()) {
            $groupedData = $this->getGroupedData();
            $sortedGroups = $this->getSortedGroups();
            $count = count($sortedGroups);

            for ($i = 0; $i < $this->_groupCount; $i++) {
                $priceData = $categories = array();
                $rows = $this->_compactRows($groupedData[$sortedGroups[$i]]);
                foreach ($rows AS $row) {
                    $priceData[]  = round((float)$row->getPrice(), 2);
                    $categories[] = $row->getData('item_name');
                }
                $this->_setChartData($priceData, $categories);
            }

            $priceData = $categories = array();
            for ($i; $i < $count; $i++) {
                $groupName = $sortedGroups[$i];
                $categories[] = $groupName;

                $total = 0;
                foreach ($groupedData[$groupName] AS $row) {
                    $total += round((float)$row->getPrice(), 2);
                }

                $priceData[] = $total;
            }

            $this->_setChartData($priceData, $categories)
                 ->setChartDataValue($this->_getHighchart());
        }

        return $this->getChartDataValue();
    }

    /**
     * @param array  $priceData
     * @param string $categories
     * @param string $groupName
     *
     * @return $this
     */
    protected function _setChartData($priceData, $categories, $groupName = '')
    {
        $i = $this->_charDataIterator++;

        $this->_chartData = $this->_getHighchart();
        $this->_chartData[$i]->y                     = array_sum($priceData);
        $this->_chartData[$i]->z                     = 'EUR';
        $this->_chartData[$i]->color                 = new HighchartJsExpr('colors[' . $i . ']');
        $this->_chartData[$i]->drilldown->name       = $groupName;
        $this->_chartData[$i]->drilldown->categories = $categories;
        $this->_chartData[$i]->drilldown->data       = $priceData;
        $this->_chartData[$i]->drilldown->color      = new HighchartJsExpr('colors[0]');

        return $this;
    }

    /**
     * @return Highchart
     */
    private function _getHighchart()
    {
        if (null === $this->_chartData) {
            $this->_chartData = new Highchart();
        }
        return $this->_chartData;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    private function _compactRows(array $rows, $maxCount = 4)
    {
        $count = count($rows);
        if ($count <= $maxCount) {
            return $rows;
        }

        $newRows = array();
        for ($i = 0; $i < $maxCount; $i++) {
            $newRows[] = $rows[$i];
        }

        $price = 0;
        for ($i; $i < $count; $i++) {
            $row = $rows[$i];
            $price += $row->getPrice();
        }

        $newRows[] = $row->setPrice($price)->setItemName('Other Items');

        return $newRows;
    }

    /**
     * @return \HighchartsPHP\Highcharts
     */
    public function getChart()
    {
        $chart = new Highchart();

        $chart->chart->renderTo = 'container';
        $chart->chart->type     = 'pie';
        $chart->title->text     = 'Pie Chart';
//        $chart->yAxis->title->text = "Total percent market share";
//        $chart->plotOptions->pie->shadow = false;

        $chart->tooltip->formatter = new HighchartJsExpr("function() {
            return '<b>' + this.point.name + '</b>: '+ this.y; alert(this);
        }");

        $chart->series[0] = array(
            'data'       => new HighchartJsExpr('primaryData'),
            'size'       => '60%',
            'dataLabels' => array(
//                'formatter' => new HighchartJsExpr('function() {
//                    return this.y > 5 ? this.point.name : null;
//                }'),
                'color'    => 'white', // title color
                'distance' => -50     // title distance
            )
        );

        $chart->series[1]->name      = 'Secondary';
        $chart->series[1]->data      = new HighchartJsExpr('secondaryData');
        $chart->series[1]->size      = "80%";
        $chart->series[1]->innerSize = "60%";

        $chart->series[1]->dataLabels->formatter = new HighchartJsExpr("function() {
            return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y : null;
        }");

        return $chart;
    }

    /**
     * @return array
     */
    public function getSortedGroups($full = true)
    {
        if (null === $this->getSortedGroupsDataValue()) {
            $groups = array();
            /** @var \Db\Db\ActiveRecord $row */
            foreach ($this->getTransactionsData() AS $row) {
                $groups[$row->getGroupName()] += $row->getPrice();
            }

            arsort($groups, SORT_NUMERIC);
            $this->setSortedGroupsDataValue(array_keys($groups));
        }

        $data = $this->getSortedGroupsDataValue();

        if (!$full) {
            $data = array_slice($data, 0, $this->_groupCount);
            $data[] = 'Other Groups';
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getTransactionsData()
    {
        return $this->getTransactionsDataValue();
    }

    /**
     * @return $this
     */
    public function setTransactionsData(array $transactionsData)
    {
        return $this->setTransactionsDataValue($transactionsData);
    }

}
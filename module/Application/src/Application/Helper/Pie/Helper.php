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
    protected $_groupCount = 8;

    /** @var int */
    protected $_itemCount = 4;

    /** @var int */
    protected $_otherGroupCount = 4;

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
                $rows = $this->_compactItems($groupedData[$sortedGroups[$i]]);
                foreach ($rows AS $row) {
                    $priceData[]  = round((float)$row->getPrice(), 2);
                    $categories[] = $row->getData('item_name');
                }
                $this->_setChartData($priceData, $categories);
            }

            $priceDataTmp = $categoriesTmp = array();
            for ($i; $i < $count; $i++) {
                $groupName = $sortedGroups[$i];
                $categoriesTmp[] = $groupName;

                $total = 0;
                foreach ($groupedData[$groupName] AS $row) {
                    $total += round((float)$row->getPrice(), 2);
                }

                $priceDataTmp[] = $total;
            }

            $count = count($priceDataTmp);
            $priceData = $categories = array();
            for ($i = 0; $i < $this->_otherGroupCount; $i++) {
                $priceData[] = $priceDataTmp[$i];
                $categories[] = $categoriesTmp[$i];
            }

            $total = 0;
            for ($i; $i < $count; $i++) {
                $total += $priceDataTmp[$i];
            }
            $priceData[] = $total;
            $categories[] = 'Other';


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
    private function _compactItems(array $rows)
    {
        $count = count($rows);
        if ($count <= $this->_itemCount) {
            return $rows;
        }

        $newRows = array();
        for ($i = 0; $i < $this->_itemCount; $i++) {
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

        $chart->plotOptions->pie->allowPointSelect = true;
        $chart->plotOptions->pie->dataLabels->enabled = true;
        $chart->plotOptions->pie->shadow = true;

        $chart->series[0]->dataLabels->distance = -80;
        $chart->series[0]->name      = 'EUR';
        $chart->series[0]->data      = new HighchartJsExpr('primaryData');
        $chart->series[0]->size      = '80%';

        $chart->series[1]->dataLabels->enabled = false;
        $chart->series[1]->name      = 'EUR';
        $chart->series[1]->data      = new HighchartJsExpr('secondaryData');
        $chart->series[1]->innerSize = '80%';

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
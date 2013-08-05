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
 */
class Helper extends AbstractHelper
{
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

            $chartData = new Highchart();

            $i = 0;
            foreach ($sortedGroups AS $groupName) {
                $rows = $groupedData[$groupName];

                $data = $categories = array();

                foreach ($rows AS $row) {
                    $data[]       = round((float)$row->getData('price'), 2);
                    $categories[] = $row->getData('item_name');
                }

                $chartData[$i]->y                     = array_sum($data);
                $chartData[$i]->z                     = 'EUR';
                $chartData[$i]->color                 = new HighchartJsExpr('colors[' . $i . ']');
                $chartData[$i]->drilldown->name       = $groupName;
                $chartData[$i]->drilldown->categories = $categories;
                $chartData[$i]->drilldown->data       = $data;
                $chartData[$i]->drilldown->color      = new HighchartJsExpr("colors[0]");

                $i++;
            }

            $this->setChartDataValue($chartData);
        }

        return $this->getChartDataValue();
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
            'size'       => '80%',
            'dataLabels' => array(
//                'formatter' => new HighchartJsExpr('function() {
//                    return this.y > 5 ? this.point.name : null;
//                }'),
                'color'    => 'white', // title color
                'distance' => -100     // title distance
            )
        );

        $chart->series[1]->name      = 'Secondary';
        $chart->series[1]->data      = new HighchartJsExpr('secondaryData');
        $chart->series[1]->innerSize = "60%";

        $chart->series[1]->dataLabels->formatter = new HighchartJsExpr("function() {
            return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y : null;
        }");

        return $chart;
    }

    /**
     * @return array
     */
    public function getSortedGroups()
    {
        if (null === $this->getSortedGroupsDataValue()) {
            $groups = array();
            foreach ($this->getTransactionsData() AS $row) {
                $groups[$row->getGroupName()] =+  $row->getPrice();
            }

            arsort($groups, SORT_NUMERIC);
            $this->setSortedGroupsDataValue(array_keys($groups));
        }

        return $this->getSortedGroupsDataValue();
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
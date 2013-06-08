<?php
namespace Application\Controller;

use Varient\Controller\AbstractActionController;

use HighchartsPHP\Highcharts as Highchart;
use HighchartsPHP\HighchartsJsExpr as HighchartJsExpr;


class PieController extends AbstractActionController
{
    /** @var array */
    protected $activeRecords = array();

    /** @var integer */
    protected $userId;


    public function indexAction()
    {
        $this->getViewHelperPlugin('headscript')
             ->appendFile('/js/highcharts/highcharts.js')
             ->appendFile('/js/highcharts/modules/exporting.js');


        $groupedData = $this->getGroupedData();
        $groupNames = array_keys($groupedData);


        $chartData = new Highchart();

        $i = 0;
        foreach ($groupedData AS $groupName => $rows) {

            $data = array();
            $categories = array();
            foreach ($rows AS $row) {
                $data[]       = (float)$row->getData('price');
                $categories[] = $row->getData('item_name');
            }

            $chartData[$i]->y = 55.11;
            $chartData[$i]->z = 'USD';
            $chartData[$i]->color = new HighchartJsExpr('colors['.$i.']');
            $chartData[$i]->drilldown->name = $groupName;
            $chartData[$i]->drilldown->categories = $categories;
            $chartData[$i]->drilldown->data = $data;
            $chartData[$i]->drilldown->color = new HighchartJsExpr("colors[0]");

            $i++;
        }
//            \DEBUG::dump($data, $categories);
//            \DEBUG::dump();

//
//
//        //We can also use Highchart library to produce any kind of javascript structures
//        $chartData = new Highchart();
//        $chartData[0]->y = 55.11;
//        $chartData[0]->z = 'USD';
//        $chartData[0]->color = new HighchartJsExpr("colors[0]");
//        $chartData[0]->drilldown->name = "MSIE versions";
//        $chartData[0]->drilldown->categories = array('MSIE 6.0', 'MSIE 7.0', 'MSIE 8.0', 'MSIE 9.0');
//        $chartData[0]->drilldown->data = array(10.85, 7.35, 33.06, 2.81);
//        $chartData[0]->drilldown->color = new HighchartJsExpr("colors[0]");
//
//        $chartData[1]->y = 21.63;
//        $chartData[1]->z = 'USD';
//        $chartData[1]->color = new HighchartJsExpr("colors[1]");
//        $chartData[1]->drilldown->name = "Firefox versions";
//
//        $chartData[1]->drilldown->categories = array('Firefox 2.0', 'Firefox 3.0', 'Firefox 3.5',
//                                                     'Firefox 3.6', 'Firefox 4.0');
//
//        $chartData[1]->drilldown->data = array(0.20, 0.83, 1.58, 13.12, 5.43);
//        $chartData[1]->drilldown->color = new HighchartJsExpr("colors[1]");


        $this->getViewHelperPlugin('inlineScript')->appendScript(
            $this->getChart()->render()
        );

        return array(
            'chartData'  => $chartData,
            'groupNames' => $groupNames
        );
    }

    /**
     * @return \HighchartsPHP\Highcharts
     */
    public function getChart()
    {
        $chart = new Highchart();

        $chart->chart->renderTo = 'container';
        $chart->chart->type = 'pie';
        $chart->title->text = 'Pie Chart';
//        $chart->yAxis->title->text = "Total percent market share";
//        $chart->plotOptions->pie->shadow = false;

        $chart->tooltip->formatter = new HighchartJsExpr("function() {
            return '<b>'+ this.point.name +'</b>: '+ this.y; alert(this);
        }");

        $chart->series[] = array(
            'data'       => new HighchartJsExpr("primaryData"),
            'size'       => "60%",
            'dataLabels' => array(
//                'formatter' => new HighchartJsExpr('function() {
//                    return this.y > 5 ? this.point.name : null;
//                }'),
                'color'    => 'white', // title color
                'distance' => -40      // title distance
            )
        );

        $chart->series[1]->name = 'Secondary';
        $chart->series[1]->data = new HighchartJsExpr("secondaryData");
        $chart->series[1]->innerSize = "60%";

        $chart->series[1]->dataLabels->formatter = new HighchartJsExpr("function() {
            return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y : null;
        }");

        return $chart;
    }

    /**
     * @return array
     */
    protected function getGroupedData()
    {
        $rowset = $this->getTransactions($this->getUserId());

        $data = array();
        foreach ($rowset as $model) {
            $data[$model['group_name']][] = $model;
        }

        return $data;
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getTransactions($userId)
    {
        $transactionTable = array('t' => 'transaction');

        $select = new \Zend\Db\Sql\Select();
        $select->from($transactionTable)
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item_name' => 'name'))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
               ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency_html' => 'html'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'))
               ->order('g.name ASC');

//        $select->where('t.id_user = ?', $userId);

        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResuls \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResuls = $table->fetch($select);
        return $transactionsResuls;
    }

}

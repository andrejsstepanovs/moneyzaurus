<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;

use HighchartsPHP\Highcharts as Highchart;
use HighchartsPHP\HighchartsJsExpr as HighchartJsExpr;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class PieController extends AbstractActionController
{
    /** @var array */
    protected $activeRecords = array();

    /** @var integer */
    protected $userId;

    /** @var arrat */
    protected $transactionsData;


    public function indexAction()
    {
        $this->getViewHelperPlugin('headscript')
             ->appendFile('/js/highcharts/highcharts.js')
             ->appendFile('/js/highcharts/modules/exporting.js');


        $groupedData = array();
        foreach ($this->getTransactions() as $model) {
            $groupedData[$model['group_name']][] = $model;
        }

        $sortedGroups = $this->getSortedGroups($groupedData);

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
            $chartData[$i]->color                 = new HighchartJsExpr('colors['.$i.']');
            $chartData[$i]->drilldown->name       = $groupName;
            $chartData[$i]->drilldown->categories = $categories;
            $chartData[$i]->drilldown->data       = $data;
            $chartData[$i]->drilldown->color      = new HighchartJsExpr("colors[0]");

            $i++;
        }

        $this->getViewHelperPlugin('inlineScript')->appendScript(
            $this->getChart()->render()
        );

        return array(
            'chartData'  => $chartData,
            'groupNames' => $sortedGroups
        );
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
            return '<b>'+ this.point.name +'</b>: '+ this.y; alert(this);
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

    protected function getSortedGroups(array $groupedData)
    {
        $groups = array();

        foreach ($this->getTransactions() AS $row) {
            $groups[$row->getGroupName()] =+  $row->getPrice();
        }

        arsort($groups, SORT_NUMERIC);

        return array_keys($groups);
    }

    /**
     * @return array
     */
    protected function getTransactions()
    {
//        if (null === $this->transactionsData) {
            $select = $this->getTransactionsSelect();
            $select = $this->applyTransactionSelectFilters($select);
            $this->transactionsData = $this->fetchTransactions($select);
//        }

        return $this->transactionsData;
    }

    /**
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\Sql\Select
     */
    protected function applyTransactionSelectFilters(Select $select)
    {
        $where = array();

        $where[] = $this->getWhere()
                           ->between('date', '2013-05-01', date('Y-m-d H:i:s'));

        $where[] = $this->getWhere()
                        ->expression('t.price > ?', 0);

        $select->where($where);

        return $select;
    }

    /**
     * @return \Zend\Db\Sql\Where
     */
    protected function getWhere()
    {
        $where = new Where();
        return $where;
    }

    /**
     * @return \Zend\Db\Sql\Select
     */
    protected function getTransactionsSelect()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item_name' => 'name'))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
               ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency_html' => 'html'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'));

        return $select;
    }

    /**
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function fetchTransactions(Select $select)
    {
        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable(array('t' => 'transaction'));

        /** @var $transactionsResuls \Zend\Db\ResultSet\HydratingResultSet */
        return $table->fetch($select);
    }

}

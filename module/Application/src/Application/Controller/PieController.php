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



            $chartData[$i]->y = array_sum($data);
            $chartData[$i]->z = 'EUR';
            $chartData[$i]->color = new HighchartJsExpr('colors['.$i.']');
            $chartData[$i]->drilldown->name = $groupName;
            $chartData[$i]->drilldown->categories = $categories;
            $chartData[$i]->drilldown->data = $data;
            $chartData[$i]->drilldown->color = new HighchartJsExpr("colors[0]");

            $i++;
        }

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
        $chart->chart->type     = 'pie';
        $chart->title->text     = 'Pie Chart';
//        $chart->yAxis->title->text = "Total percent market share";
//        $chart->plotOptions->pie->shadow = false;

        $chart->tooltip->formatter = new HighchartJsExpr("function() {
            return '<b>'+ this.point.name +'</b>: '+ this.y; alert(this);
        }");

        $chart->series[0] = array(
            'data'       => new HighchartJsExpr('primaryData'),
            'size'       => '50%',
            'dataLabels' => array(
//                'formatter' => new HighchartJsExpr('function() {
//                    return this.y > 5 ? this.point.name : null;
//                }'),
                'color'    => 'white', // title color
                'distance' => -40      // title distance
            )
        );

        $chart->series[1]->name      = 'Secondary';
        $chart->series[1]->data      = new HighchartJsExpr('secondaryData');
        $chart->series[1]->innerSize = "50%";

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
        $select = $this->getTransactionsSelect();

        $select = $this->applyTransactionSelectFilters($select);

        $rowset = $this->fetchTransactions($select);

        $data = array();
        foreach ($rowset as $model) {
            $data[$model['group_name']][] = $model;
        }

        return $data;
    }

    /**
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\Sql\Select
     */
    protected function applyTransactionSelectFilters(Select $select)
    {
        $whereArr = array();

        $whereArr[] = $this->getWhere()
                           ->between('date', '2013-05-01', date('Y-m-d H:i:s'));


        foreach ($whereArr AS $where) {
            $select->where($where);
        }

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
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'))
               ->order('g.name ASC');

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

<?php
namespace Application\Controller;

use Varient\Controller\AbstractActionController;
use HighRollerLineChart;
use HighRollerPieChart;
use HighRollerPlotOptions;
use HighRollerFormatter;
use HighRollerDataLabels;
use HighRollerSeriesData;

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


//        $chart = new HighRollerPieChart();
//
////
//        // HighRoller: sample data
//        $chartData = array(array('Foo', 5324), array('Bar', 7534), array('Baz', 6234), array('Fooey', 7234), array('Barry', 8251), array('Bazzy', 10324));
//
//        // HighRoller: create new series data object and hydrate with precious data
//        $series1 = new HighRollerSeriesData();
//        $series1->addData($chartData);
//
//        // HighRoller: pie chart
//        $chart->chart->renderTo = 'piechart';
//        $chart->title->text = 'Pie Chart';
////        $chart->plotOptions = new HighRollerPlotOptions('pie');
////        $chart->plotOptions->pie->dataLabels = new HighRollerDataLabels();
////        $chart->plotOptions->pie->dataLabels->formatter = new HighRollerFormatter();
//        $chart->addSeries($series1);
//
//        // HighRoller: add series data object to chart object
//        $chart->addSeries($series1);




        $chart = new HighRollerLineChart();

        $series = new HighRollerSeriesData();
        $series->name = 'myData';

        $chartData = array(5324, 7534, 6234, 7234, 8251, 10324);
        foreach ($chartData as $value){
            $series->addData($value);
        }


        $chart->title->text = 'Line Chart';
        $chart->addSeries($series);

        $chart->chart->renderTo = "highroller";
        $this->getViewHelperPlugin('inlineScript')->appendScript(
            $chart->renderChart()
        );
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getTransactions($order_by, $order)
    {
        $transactionTable = array('t' => 'transaction');

        $select = new \Zend\Db\Sql\Select();
        $select->from($transactionTable)
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item_name' => 'name'))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
               ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency_html' => 'html'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'))
               ->order($order_by . ' ' . $order);

        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResuls \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResuls = $table->fetch($select);
        return $transactionsResuls;
    }

}

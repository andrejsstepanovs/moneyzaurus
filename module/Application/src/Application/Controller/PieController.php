<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;

use HighchartsPHP\Highcharts as Highchart;
use HighchartsPHP\HighchartsJsExpr as HighchartJsExpr;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Application\Form\Form\Month as MonthForm;
use Application\Form\Validator\Month as MonthValidator;

class PieController extends AbstractActionController
{
    /** @var array */
    protected $activeRecords = array();

    /** @var integer */
    protected $userId;

    /** @var array */
    protected $transactionsData;

    /** @var array */
    protected $sortedGroupsData;

    /** @var array */
    protected $groupedData;

    /** @var Highchart */
    protected $chartData;

    /** @var \Application\Form\Month */
    protected $monthForm;

    /** @var \Application\Form\Validator\Month */
    protected $monthValidator;

    /**
     * @return array
     */
    public function indexAction()
    {
        $this->getViewHelperPlugin('inlineScript')->appendScript(
            $this->getChart()->render()
        );

        return array(
            'chartData'  => $this->getChartData(),
            'groupNames' => $this->getSortedGroups(),
            'form' => $this->getMonthForm()
        );
    }

    /**
     * @return array
     */
    private function getGroupedData()
    {
        if (null === $this->groupedData) {
            $this->groupedData = array();
            foreach ($this->getTransactionsData() as $model) {
                $this->groupedData[$model['group_name']][] = $model;
            }
        }
        return $this->groupedData;
    }

    /**
     * @return Highchart
     */
    private function getChartData()
    {
        if (null === $this->chartData) {
            $groupedData = $this->getGroupedData();
            $sortedGroups = $this->getSortedGroups();

            $this->chartData = new Highchart();

            $i = 0;
            foreach ($sortedGroups AS $groupName) {
                $rows = $groupedData[$groupName];

                $data = $categories = array();

                foreach ($rows AS $row) {
                    $data[]       = round((float)$row->getData('price'), 2);
                    $categories[] = $row->getData('item_name');
                }

                $this->chartData[$i]->y                     = array_sum($data);
                $this->chartData[$i]->z                     = 'EUR';
                $this->chartData[$i]->color                 = new HighchartJsExpr('colors[' . $i . ']');
                $this->chartData[$i]->drilldown->name       = $groupName;
                $this->chartData[$i]->drilldown->categories = $categories;
                $this->chartData[$i]->drilldown->data       = $data;
                $this->chartData[$i]->drilldown->color      = new HighchartJsExpr("colors[0]");

                $i++;
            }
        }

        return $this->chartData;
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

    /**
     * @return array
     */
    protected function getSortedGroups()
    {
        if (null === $this->sortedGroupsData) {
            $groups = array();
            foreach ($this->getTransactionsData() AS $row) {
                $groups[$row->getGroupName()] =+  $row->getPrice();
            }

            arsort($groups, SORT_NUMERIC);
            $this->sortedGroupsData = array_keys($groups);
        }

        return $this->sortedGroupsData;
    }

    /**
     * @return array
     */
    protected function getTransactionsData()
    {
        if (null === $this->transactionsData) {
            $select = $this->getTransactionsSelect();
            $select = $this->applyTransactionSelectFilters($select);

            $this->transactionsData = array();

            /** @var $resultSet Zend\Db\ResultSet\HydratingResultSet */
            $resultSet = $this->fetchTransactions($select);
            if ($resultSet->count()) {
                foreach ($resultSet AS $row) {
                    $this->transactionsData[] =  $row;
                }
            }
        }
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
                           ->between('date', date('Y-m-d H:i:s', strtotime('-2 months')), date('Y-m-d H:i:s'));

        $where[] = $this->getWhere()
                        ->expression('t.price > ?', 0);

        $select->where($where);

        $select->limit(50);

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

    /**
     * @return \Application\Form\Month
     */
    public function getMonthForm()
    {
        if (null === $this->monthForm) {
            $this->monthForm = new MonthForm();
        }

        return $this->monthForm;
    }

    /**
     * @return \Application\Form\Validator\Month
     */
    public function getMonthValidator()
    {
        if (null === $this->monthValidator) {
            $this->monthValidator = new MonthValidator();
        }

        return $this->monthValidator;
    }

}

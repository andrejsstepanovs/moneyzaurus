<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;
use Application\Helper\Pie\Helper as PieHelper;
use Application\Helper\Month\Helper as MonthHelper;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Sql\Where;

/**
 * @method \Application\Helper\Pie\Helper getHelper()
 */
class PieController extends AbstractActionController
{
    /** @var array */
    protected $transactionsData;

    /** @var MonthHelper */
    protected $monthHelper;


    /**
     * @return MonthHelper
     */
    private function getMonthHelper()
    {
        if (null === $this->monthHelper) {
            $this->monthHelper = new MonthHelper();
            $this->monthHelper->setRequest($this->getRequest());
        }

        return $this->monthHelper;
    }

    /**
     * @return void
     */
    protected function init()
    {
        $helper = new PieHelper();

        $this->setHelper($helper);
        $helper->setTransactionsData($this->getTransactionsData());
    }

    /**
     * @return array
     */
    public function indexAction()
    {
        $this->getViewHelperPlugin('inlineScript')->appendScript(
            $this->getHelper()->getChart()->render()
        );

        return array(
            'chartData'  => $this->getHelper()->getChartData(),
            'groupNames' => $this->getHelper()->getSortedGroups(false),
            'form' => $this->getForm()
        );
    }

    /**
     * @return \Application\Form\Form\Month
     */
    private function getForm()
    {
        $form = $this->getMonthHelper()->getMonthForm();

        $month = $form->get('month');
        $month->setValue($this->getMonthHelper()->getMonthRequestValue());

        return $form;
    }

    /**
     * @return array
     */
    private function getTransactionsData()
    {
        if (null === $this->transactionsData) {
            /** @var Select $select */
            $select = $this->getTransactionsSelect();
            $select = $this->applyTransactionSelectFilters($select);
            $select->order('price ' . Select::ORDER_DESCENDING);

            $this->transactionsData = array();

            /** @var $resultSet \Zend\Db\ResultSet\HydratingResultSet */
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
     * @return \Zend\Db\Sql\Select
     */
    private function getTransactionsSelect()
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
     * @return \Zend\Db\Sql\Select
     */
    private function applyTransactionSelectFilters(Select $select)
    {
        $where = array();

        $month = $this->getMonthHelper()->getMonthRequestValue();

        $timestamp = strtotime($month . '-01');
        $monthDateTimeFrom = date('Y-m-d H:i:s', $timestamp);
        $monthDateTimeTill = date('Y-m-d', strtotime($month . '-' . date('t', $timestamp))) . ' 23:59:59';

        $where[] = $this->getWhere()
                   ->between('date', $monthDateTimeFrom, $monthDateTimeTill);

        $where[] = $this->getWhere()
                   ->expression('t.price > ?', 0);

        $select->where($where);

        return $select;
    }

    /**
     * @return \Zend\Db\Sql\Where
     */
    private function getWhere()
    {
        return new Where();
    }

    /**
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    private function fetchTransactions(Select $select)
    {
        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable(array('t' => 'transaction'));

        /** @var $transactionsResuls \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResuls = $table->fetch($select);
        return $transactionsResuls;
    }
}

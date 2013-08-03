<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;
use Application\Helper\Pie\Helper;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

/**
 * @method \Application\Helper\Pie\Helper getHelper()
 */
class PieController extends AbstractActionController
{
    /** @var array */
    protected $transactionsData;

    /**
     * @return void
     */
    protected function init()
    {
        $helper = new Helper();
        $helper->setRequest($this->getRequest());
        $this->setHelper($helper);
        $helper->setTransactionsData($this->getTransactionsData());
        $this->setHelper($helper);
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
            'groupNames' => $this->getHelper()->getSortedGroups(),
            'form' => $this->getForm()
        );
    }

    /**
     * @return \Application\Form\Form\Month
     */
    private function getForm()
    {
        $form = $this->getHelper()->getMonthForm();

        $month = $form->get('month');
        $month->setValue($this->getHelper()->getMonthRequestValue());

        return $form;
    }

    /**
     * @return array
     */
    private function getTransactionsData()
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

        $month = $this->getHelper()->getMonthRequestValue();

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

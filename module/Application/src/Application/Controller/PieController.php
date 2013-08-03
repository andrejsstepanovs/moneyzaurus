<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;
use Application\Helper\Pie\Helper;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class PieController extends AbstractActionController
{
    /**
     * @var \Application\Helper\Pie\Helper
     */
    private $helper;

    /** @var array */
    protected $transactionsData;


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
            'form' => $this->getHelper()->getMonthForm()
        );
    }

    /**
     * @return Helper
     */
    private function getHelper()
    {
        if (null === $this->helper) {
            $this->helper = new Helper();
            $this->helper->setTransactionsData($this->getTransactionsData());
        }

        return $this->helper;
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
        return new Where();
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
        $transactionsResuls = $table->fetch($select);
        return $transactionsResuls;
    }
}

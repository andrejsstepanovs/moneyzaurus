<?php
namespace Application\Controller;

use Varient\Controller\AbstractActionController;


class ListController extends AbstractActionController
{
    /** @var array */
    protected $activeRecords = array();

    /** @var integer */
    protected $userId;

    /** @var \Application\Form\Transaction */
    protected $form;

    /** @var \Application\Form\Validator\Transaction */
    protected $validator;

    /** @var array */
    protected $datalist;


    public function indexAction()
    {
        /** @var $params \Zend\Mvc\Controller\Plugin\Params */
        $params   = $this->params();
        $order_by = $params->fromRoute('order_by') ? $params->fromRoute('order_by') : 'transaction_id';
        $order    = $params->fromRoute('order')    ? $params->fromRoute('order')    : \Zend\Db\Sql\Select::ORDER_ASCENDING;

        $transactionsResuls = $this->getTransactions($order_by, $order);

        return array(
            'transactions' => $transactionsResuls,
            'order_by'     => $order_by,
            'order'        => $order,
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

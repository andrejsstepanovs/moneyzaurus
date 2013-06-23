<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;
use Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as PaginatorIterator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;


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
        $order_by = $params->fromRoute('order_by')     ? $params->fromRoute('order_by') : 'transaction_id';
        $order    = $params->fromRoute('order')        ? $params->fromRoute('order')    : \Zend\Db\Sql\Select::ORDER_ASCENDING;
        $page     = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;

        $itemsPerPage = 20;
        $transactionsResuls = $this->getTransactions($page, $itemsPerPage, $order_by, $order);
        $totalItemCount = $this->getTotalCount();

        $transactionsResuls->current();
        $paginator = new Paginator(new PaginatorIterator($transactionsResuls));
        $paginator->setTotalItemCount($totalItemCount)
                  ->setCurrentPageNumber($page)
                  ->setItemCountPerPage($itemsPerPage)
                  ->setPageRange(5);

        return array(
            'transactions' => $transactionsResuls,
            'order_by'     => $order_by,
            'order'        => $order,
            'page'         => $page,
            'paginator'    => $paginator,
        );
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getTransactions($page, $itemsPerPage, $order_by, $order)
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable, array('*', 'total' => new Expression("FOUND_ROWS()")))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item_name' => 'name'))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
               ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency_html' => 'html'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'))
               ->order($order_by . ' ' . $order)
               ->quantifier(new Expression('SQL_CALC_FOUND_ROWS'))
               ->limit($itemsPerPage)
               ->offset($page * $itemsPerPage);

        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResuls \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResuls = $table->fetch($select)->buffer();

        return $transactionsResuls;
    }

    /**
     * @return int FOUND_ROWS()
     */
    protected function getTotalCount()
    {
        $selectTotal = new Select(' ');
        $selectTotal->setSpecification(Select::SELECT, array(
            'SELECT %1$s' => array(
                array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                null
            )
        ));

        $selectTotal->columns(array(
            'total' => new Expression('FOUND_ROWS()')
        ));

        $sql = $this->getTable('transactions')->getTable()->getSql();
        $statement = $sql->prepareStatementForSqlObject($selectTotal);

        $result2 = $statement->execute();
        $row = $result2->current();

        return $row['total'];
    }

}

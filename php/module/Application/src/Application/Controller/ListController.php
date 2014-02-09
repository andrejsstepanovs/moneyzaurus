<?php
namespace Application\Controller;

use Application\Helper\Lister\Helper as ListerHelper;
use Application\Helper\Transaction\Helper as TransactionHelper;
use Application\Form\Form\Transaction as TransactionForm;
use \Zend\Db\Sql\Expression;
use \Zend\Db\Sql\Select;
use \Zend\Db\Sql\Where;
use \Zend\Db\TableGateway\Exception\RuntimeException;

/**
 * @method \Application\Helper\Lister\Helper getHelper()
 */
class ListController extends AbstractActionController
{
    /** @var TransactionForm */
    protected $transactionForm;

    /** @var ListerHelper */
    protected $listerHelper;

    /** @var TransactionHelper */
    protected $transactionHelper;

    /** @var TransactionForm */
    protected $searchForm;

    /** @var array */
    protected $whereFilter;

    /**
     * @return ListerHelper
     */
    protected function getListerHelper()
    {
        if (null === $this->listerHelper) {
            $this->listerHelper = new ListerHelper();
            $this->listerHelper->setParams($this->params());
        }

        return $this->listerHelper;
    }

    /**
     * @return TransactionHelper
     */
    protected function getTransactionHelper()
    {
        if (null === $this->transactionHelper) {
            $this->transactionHelper = new TransactionHelper();
            $this->transactionHelper->setAbstractHelper($this->getHelper());
        }

        return $this->transactionHelper;
    }

    public function ajaxAction()
    {
        $transactionsResults = $this->getTransactions();
        $totalItemCount = $this->getTotalCount();

        /** @var \Db\ActiveRecord $item */
        $rows = array();
        foreach ($transactionsResults as $item) {
            $rows[] = $item->getData();
        }

        $script = null;
        $data = array(
            'success' => 1,
            'data'    => array(
                'count'    => $totalItemCount,
                'order_by' => $this->getListerHelper()->getOrderBy(),
                'order'    => $this->getListerHelper()->getOrder(),
                'rows'     => $rows,
                'columns'  => array( //http://stackoverflow.com/questions/14261115/zf2-use-translator-in-controller
                    'item_name',
                    'group_name',
                    'price',
                    'date',
                    //'id_user',
                )
            ),
            'script'  => $script
        );

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($data));

        return $response;
    }

    public function indexAction()
    {
        return array(
            'form'            => $this->getSearchForm(),
            'transactionForm' => $this->getTransactionForm()
        );
    }

    /**
     * @return \Application\Form\Form\Transaction
     */
    public function getTransactionForm()
    {
        if (null === $this->transactionForm) {
            $this->transactionForm = new TransactionForm();
            //$this->_transactionForm->setAttribute('id', 'editTransactionForm');
            $this->transactionForm->remove('id_user');

            $formElements = $this->transactionForm->getElements();

            /** @var \Zend\Form\Element\Select $currencyElement */
            /** @var \Zend\Form\Element\Select $dateElement */
            $currencyElement = $formElements['currency'];
            $dateElement     = $formElements['date'];

            $currencyElement->setValueOptions($this->getCurrencyValueOptions());
            $dateElement->setValue(date('Y-m-d'));
        }

        return $this->transactionForm;
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getTransactions()
    {
        $orderBy = $this->getListerHelper()->getOrderBy();
        $order   = $this->getListerHelper()->getOrder();

        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item_name' => 'name'))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
               ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency_html' => 'html'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'))
               ->order($orderBy . ' ' . $order)
               ->quantifier(new Expression('SQL_CALC_FOUND_ROWS'))
               ->limit($this->getListerHelper()->getItemsPerPage());

        $where = $this->getWhereFilter();
        if (count($where)) {
            $select->where($where);
        }

        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select)->buffer();

        return $transactionsResults;
    }

    /**
     * @return array
     */
    protected function getWhereFilter()
    {
        if (null === $this->whereFilter) {
            $item   = $this->getListerHelper()->getItem();
            $group  = $this->getListerHelper()->getGroup();
            $date   = $this->getListerHelper()->getDate();
            $idUser = $this->getUserId();
            $price  = $this->getListerHelper()->getPrice();

            $where = array();

            if (!empty($item)) {
                $where[] = $this->getWhere()->like('i.name', $item . '%');
            }

            if (!empty($group)) {
                $where[] = $this->getWhere()->like('g.name', $group . '%');
            }

            if (!empty($price)) {
                $where[] = $this->getWhere()->like('t.price', $price . '%');
            }

            if (!empty($date)) {
                $where[] = $this->getWhere()->like('t.date', $date . '%');
            }

            $where[] = $this->getWhere()->equalTo('t.id_user', $idUser);

            $this->whereFilter = $where;
        }

        return $this->whereFilter;
    }

    /**
     * @return \Zend\Db\Sql\Where
     */
    private function getWhere()
    {
        return new Where();
    }

    /**
     * @return int FOUND_ROWS()
     */
    protected function getTotalCount()
    {
        $selectTotal = new Select(' ');
        $selectTotal->setSpecification(
            Select::SELECT,
            array(
                'SELECT %1$s' => array(
                    array(1 => '%1$s', 2 => '%1$s AS %2$s', 'combinedby' => ', '),
                    null
                )
            )
        );

        $selectTotal->columns(
            array('total' => new Expression('FOUND_ROWS()'))
        );

        $sql = $this->getTable('transactions')->getTable()->getSql();
        $statement = $sql->prepareStatementForSqlObject($selectTotal);

        $result2 = $statement->execute();
        $row = $result2->current();

        return $row['total'];
    }

    /**
     * @return TransactionForm
     */
    protected function getSearchForm()
    {
        if (null === $this->searchForm) {
            $this->searchForm = new TransactionForm();
        }

        return $this->searchForm;
    }

    public function saveAction()
    {
        $transaction = $this->getTransactionHelper()->saveTransaction(
            $this->getUserId(),
            $this->getListerHelper()->getTransactionId(),
            $this->getListerHelper()->getItem(),
            $this->getListerHelper()->getGroup(),
            $this->getListerHelper()->getPrice(),
            $this->getListerHelper()->getCurrencyId(),
            $this->getListerHelper()->getDate()
        );

        $data = array(
            'success' => $transaction->getTransactionId(),
            'error'   => ''
        );

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($data));

        return $response;
    }

    public function deleteAction()
    {
        $transactionId = $this->getListerHelper()->getTransactionId();

        $deleted = false;
        $error = '';
        try {
            $deleted = $this->deleteTransaction($transactionId);
        } catch (\Exception $exc) {
            $error = $exc->getMessage();
        }

        $data = array(
            'success' => $deleted,
            'error'   => $error
        );

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($data));

        return $response;
    }

    /**
     * @param int $transactionId
     *
     * @return bool
     * @throws \Zend\Db\TableGateway\Exception\RuntimeException
     */
    protected function deleteTransaction($transactionId)
    {
        /** @var \Application\Db\Transaction $transaction */
        $transaction = $this->getTable('transaction');
        $transaction->setTransactionId($transactionId);
        $transaction->load();

        if ($transaction->getIdUser() != $this->getUserId()) {
            throw new RuntimeException('It is not allowed to edit other user transactions.');
        }

        return (bool) $transaction->delete();
    }
}

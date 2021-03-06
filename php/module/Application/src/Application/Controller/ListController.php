<?php
namespace Application\Controller;

use Application\Helper\Lister\Helper as ListerHelper;
use Application\Helper\Transaction\Helper as TransactionHelper;
use Application\Form\Form\Transaction as TransactionForm;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Db\TableGateway\Exception\RuntimeException;
use Application\Helper\Connection\Helper as ConnectionHelper;

class ListController extends AbstractActionController
{
    /** @var TransactionForm */
    protected $transactionForm;

    /** @var ListerHelper */
    protected $listerHelper;

    /** @var TransactionHelper */
    protected $transactionHelper;

    /** @var ConnectionHelper */
    protected $connectionHelper;

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
            $this->transactionHelper->setAbstractHelper($this->getAbstractHelper());
            $this->transactionHelper->setParams($this->getParams());
        }

        return $this->transactionHelper;
    }

    /**
     * @return ConnectionHelper
     */
    protected function getConnectionHelper()
    {
        if (null === $this->connectionHelper) {
            $this->connectionHelper = new ConnectionHelper();
            $this->connectionHelper->setAbstractHelper($this->getAbstractHelper());
        }

        return $this->connectionHelper;
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
        $response->setContent(Json::encode($data));

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
               ->columns(array('price' => new Expression('ROUND(price / 100, 2)'), 'transaction_id', 'id_user', 'id_group', 'id_item', 'id_currency', 'date', 'date_created'))
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
        $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());

        //\DEBUG::dump($select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $this
            ->getAbstractHelper()
            ->getModel('transaction')
            ->getTable()
            ->setTable($transactionTable)
            ->fetch($select)
            ->buffer();

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
            $price  = $this->getListerHelper()->getPrice();

            $where = array();

            if (!empty($item)) {
                $where[] = $this->getWhere()->like('i.name', $item . '%');
            }

            if (!empty($group)) {
                $where[] = $this->getWhere()->like('g.name', $group . '%');
            }

            if (!empty($price)) {
                $where[] = $this->getWhere()->like('t.price', $price * 100 . '%');
            }

            if (!empty($date)) {
                $where[] = $this->getWhere()->like('t.date', $date . '%');
            }

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

        $sql = $this->getAbstractHelper()->getModel('transaction')->getTable()->getSql();
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
            'success'     => $transaction->getTransactionId(),
            'error'       => '',
            'transaction' => $transaction->getData()
        );

        $helper = $this->getAbstractHelper();
        $appendData = array(
            'item_name'     => $this->getListerHelper()->getItem(),
            'group_name'    => $this->getListerHelper()->getGroup(),
            'currency_html' => $helper->getModel('currency')->load($transaction->getIdCurrency())->getHtml(),
            'email'         => $helper->getModel('user')->load($this->getUserId())->getEmail(),
        );
        $data['transaction'] = array_merge($data['transaction'], $appendData);

        $response = $this->getResponse();
        $response->setContent(Json::encode($data));

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
        $response->setContent(Json::encode($data));

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
        $transaction = $this->getAbstractHelper()->getModel('transaction');
        $transaction->setTransactionId($transactionId);
        $transaction->load();

        $allowedUsers = array($this->getUserId());
        $connections = $this->getConnectionHelper()->getUserConnections($this->getUserId());
        /** @var \Application\Db\Connection $connection */
        foreach ($connections as $connection) {
            $allowedUsers[] = $connection->getIdUserParent();
        }

        if (!in_array($transaction->getIdUser(), $allowedUsers)) {
            throw new RuntimeException('It is not allowed to edit other user transactions.');
        }

        return (bool) $transaction->delete();
    }
}

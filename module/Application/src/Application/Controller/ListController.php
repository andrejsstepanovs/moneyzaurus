<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;
use Application\Helper\Lister\Helper as ListHelper;
use Application\Form\Form\Transaction as TransactionForm;
use Zend\Paginator\Adapter\Iterator as PaginatorIterator;
use Paginator\Paginator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

/**
 * @method \Application\Helper\Lister\Helper getHelper()
 */
class ListController extends AbstractActionController
{
    /** @var TransactionForm */
    protected $_form;

    /** @var array */
    protected $_whereFilter;

    /**
     * @return void
     */
    protected function init()
    {
        $helper = new ListHelper();
        $helper->setParams($this->params());
        $this->setHelper($helper);
    }

    public function ajaxAction()
    {
        $transactionsResults = $this->getTransactions();
        $totalItemCount = $this->getTotalCount();

        /** @var \Db\Db\ActiveRecord $item */
        $rows = array();
        foreach ($transactionsResults as $item) {
            $rows[] = $item->getData();
        }

        $script = null;
        $data = array(
            'success' => 1,
            'data'    => array(
                'count'    => $totalItemCount,
                'order_by' => $this->getHelper()->getOrderBy(),
                'order'    => $this->getHelper()->getOrder(),
                'rows'     => $rows,
                'columns'  => array( //http://stackoverflow.com/questions/14261115/zf2-use-translator-in-controller
                    'item_name',
                    'group_name',
                    'price',
                    'date',
                    'id_user',
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
            'order_by'     => $this->getHelper()->getOrderBy(),
            'order'        => $this->getHelper()->getOrder(),
            'form'         => $this->getSearchForm(),
        );
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getTransactions()
    {
        $orderBy = $this->getHelper()->getOrderBy();
        $order   = $this->getHelper()->getOrder();

        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable, array('*', 'total' => new Expression("FOUND_ROWS()")))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item_name' => 'name'))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
               ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency_html' => 'html'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'))
               ->order($orderBy . ' ' . $order)
               ->quantifier(new Expression('SQL_CALC_FOUND_ROWS'))
               ->limit($this->getHelper()->getItemsPerPage());

        $where = $this->_getWhereFilter();
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
    protected function _getWhereFilter()
    {
        if (null === $this->_whereFilter) {
            $item   = $this->getHelper()->getItem();
            $group  = $this->getHelper()->getGroup();
            $date   = $this->getHelper()->getDate();
            $idUser = $this->getHelper()->getIdUser();
            $price  = $this->getHelper()->getPrice();

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

            if (!empty($idUser)) {
                $where[] = $this->getWhere()->equalTo('t.id_user', $idUser);
            }

            $this->_whereFilter = $where;
        }

        return $this->_whereFilter;
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

    /**
     * @return TransactionForm
     */
    protected function getSearchForm()
    {
        if (null === $this->_form) {
            $this->_form = new TransactionForm();
        }
        return $this->_form;
    }

}

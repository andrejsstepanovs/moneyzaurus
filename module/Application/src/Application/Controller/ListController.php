<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;
use Application\Helper\Lister\Helper as ListHelper;
use Application\Form\Form\Transaction as TransactionForm;
use Zend\Paginator\Adapter\Iterator as PaginatorIterator;
use Paginator\Paginator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

/**
 * @method \Application\Helper\Lister\Helper getHelper()
 */
class ListController extends AbstractActionController
{
    /** @var TransactionForm */
    protected $form;

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
                'page'     => $this->getHelper()->getPage(),
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
        $transactionsResults = $this->getTransactions();
        $totalItemCount = $this->getTotalCount();

        $transactionsResults->current();
        $paginator = new Paginator(new PaginatorIterator($transactionsResults));
        $paginator->setTotalItemCount($totalItemCount)
                  ->setCurrentPageNumber($this->getHelper()->getPage())
                  ->setItemCountPerPage($this->getHelper()->getItemsPerPage())
                  ->setPageRange(5);

        return array(
            'transactions' => $transactionsResults,
            'order_by'     => $this->getHelper()->getOrderBy(),
            'order'        => $this->getHelper()->getOrder(),
            'page'         => $this->getHelper()->getPage(),
            'paginator'    => $paginator,
            'form'         => $this->getSearchForm(),
        );
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getTransactions()
    {
        $order_by     = $this->getHelper()->getOrderBy();
        $order        = $this->getHelper()->getOrder();
        $page         = $this->getHelper()->getPage();
        $itemsPerPage = $this->getHelper()->getItemsPerPage();

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

    /**
     * @return TransactionForm
     */
    protected function getSearchForm()
    {
        if (null === $this->form) {
            $this->form = new TransactionForm();
        }
        return $this->form;
    }

}

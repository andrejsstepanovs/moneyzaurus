<?php
namespace Application\Controller;

use \Zend\Db\Sql\Select;
use Application\Controller\AbstractActionController;
use Zend\Db\Sql\Where;
use Zend\View\Model\ViewModel;


class DataController extends AbstractActionController
{
    /** @var string */
    protected $_filename;

    /**
     * @return string
     */
    protected function _getFileName()
    {
        if (null === $this->_filename) {
            $this->_filename = 'moneyzaurus_' . date('Y-m-d') . '.csv';
        }
        return $this->_filename;
    }

    protected function _setHeader()
    {
        header('Content-Disposition: attachment; filename=' . $this->_getFileName());
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Description: File Transfer');
        //header("Content-Length: " . filesize($filename)); // unknown
    }

    /**
     * Shows user profile.
     *
     * @return array
     */
    public function indexAction()
    {

    }

    public function downloadAction()
    {
        $this->_setHeader();

        $columns = array('id', 'price', 'date', 'created', 'item', 'group', 'currency', 'email');
        $transactions = $this->_getTransactions();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('columns', $columns);
        $viewModel->setVariable('transactions', $transactions);
        return $viewModel;
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function _getTransactions()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->columns(array('id' => 'transaction_id', 'price', 'date', 'created' => 'date_created'))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item' => 'name'))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group' => 'name'))
               ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency' => 'currency_id'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'))
               ->order('t.date ' . Select::ORDER_DESCENDING);

        $where = array(
            $this->getWhere()->equalTo('t.id_user', $this->getUserId())
        );
        $select->where($where);

        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select)->buffer();
        return $transactionsResults;
    }


    /**
     * @return \Zend\Db\Sql\Where
     */
    private function getWhere()
    {
        return new Where();
    }

}
<?php

namespace Application\Helper\Transaction;

use Application\Helper\AbstractHelper;
use Db\Exception\ModelNotFoundException;
use Zend\Db\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Request;
use Application\Helper\Transaction\Helper as TransactionHelper;
use Zend\Mvc\Controller\Plugin\Params as PluginParams;
use Application\Db\Transaction as DbTransaction;

/**
 * @method Request getRequest()
 * @method TransactionHelper setParams(PluginParams $params)
 * @method TransactionHelper setAbstractHelper(AbstractHelper $abstractHelper)
 * @method TransactionHelper setUserId(int $userId)
 * @method PluginParams getParams()
 * @method AbstractHelper getAbstractHelper()
 * @method int getUserId()
 */
class Helper extends AbstractHelper
{
    /** @var array */
    private $whereFilter;

    /**
     * @return string
     */
    public function getPredict()
    {
        $params = $this->getParams();
        $predict = $params->fromQuery('predict');

        return $predict;
    }

    /**
     * @return string
     */
    public function getItem()
    {
        $params = $this->getParams();
        $item = $params->fromQuery('item');

        return $item;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        $params = $this->getParams();
        $group = $params->fromQuery('group');

        return $group;
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return 'transaction_id';
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return Select::ORDER_DESCENDING;
    }

    /**
     * @param  int    $userId
     * @param  int    $transactionId
     * @param  string $itemName
     * @param  string $groupName
     * @param  float  $price
     * @param  string $currencyId
     * @param  string $date
     *
     * @return DbTransaction transaction
     */
    public function saveTransaction(
        $userId,
        $transactionId,
        $itemName,
        $groupName,
        $price,
        $currencyId,
        $date
    ) {
        if ($transactionId == 0) {
            $transactionId = null;
        }

        /** @var \Application\Db\Currency $currency*/
        $currency = $this->getAbstractHelper()->getTable('currency')
                         ->setId($currencyId)
                         ->load();

        /** @var \Application\Db\Item $item */
        $item = $this->getAbstractHelper()->getTable('item');
        try {
            $item->setName($itemName)
                 ->setIdUser($userId)
                 ->load();
        } catch (ModelNotFoundException $exc) {
            $item->save();
        }

        /** @var \Application\Db\Group $group */
        $group = $this->getAbstractHelper()->getTable('group');
        try {
            $group->setName($groupName)
                  ->setIdUser($userId)
                  ->load();
        } catch (ModelNotFoundException $exc) {
            $group->save();
        }

        /** @var \Application\Db\Transaction $transaction */
        $transaction = $this->getAbstractHelper()->getTable('transaction');
        return $transaction
            ->setTransactionId($transactionId)
            ->setPrice($price)
            ->setDate($date)
            ->setIdUser($userId)
            ->setIdItem($item->getId())
            ->setIdGroup($group->getId())
            ->setIdCurrency($currency->getId())
            ->save();
    }


    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getPriceTransactions()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->columns(array('price', 'day_of_the_week' => new Expression('DAYOFWEEK(t.date)')))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array())
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array())
               ->order($this->getOrderBy() . ' ' . $this->getOrder())
            //->limit(100)
        ;

        $where = $this->getWhereFilter();
        if (count($where)) {
            $select->where($where);
        }

        $select = $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());

        //\DEBUG::dump($select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $transactionsTable = $this->getTable('transactions');
        $table = $transactionsTable->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select)->buffer();

        return $transactionsResults;
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getGroupTransactions()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->columns(array('times_used' => new Expression("COUNT(*)")))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array())
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
               ->group('g.name')
               ->order(new Expression("COUNT(*) DESC"))
               ->limit(5);

        $where = $this->getWhereFilter();
        if (count($where)) {
            $select->where($where);
        }
        $select = $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());

        //\DEBUG::dump(@$select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $transactions = $this->getAbstractHelper()->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select)->buffer();

        return $transactionsResults;
    }

    /**
     * @param string $tableName
     * @param string $column
     *
     * @return array
     */
    public function getDistinctTransactionValues($tableName, $column)
    {
        /** @var \Db\AbstractTable $table */
        /** @var \Zend\Db\ResultSet\HydratingResultSet $results */
        $table = $this->getAbstractHelper()->getTable($tableName)->getTable();
        $results = $table->fetchUniqeColum(
            $column,
            array(
                $this->getWhere()->equalTo(
                    'id_user',
                    $this->getUserId()
                )
            )
        );

        $dataValues = array();
        /** @var \Db\AbstractModel $model */
        foreach ($results as $model) {
            $dataValues[] = $model->getData($column);
        }

        return $dataValues;
    }

    /**
     * @return array
     */
    private function getWhereFilter()
    {
        if (null === $this->whereFilter) {
            $item   = $this->getItem();
            $group  = $this->getGroup();

            $where = array();

            if (!empty($item)) {
                $where[] = $this->getWhere()->equalTo('i.name', $item);
            }

            if (!empty($group)) {
                $where[] = $this->getWhere()->equalTo('g.name', $group);
            }

            //$where[] = $this->getWhere()->greaterThan('t.date', date('Y-m-d H:i:s', strtotime('-1 year')));

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

}

<?php

namespace Application\Helper\Connection;

use Application\Helper\AbstractHelper;
use Application\Helper\Transaction\Helper as TransactionHelper;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

/**
 * Class Helper
 *
 * @package Application\Helper\Connection
 *
 * @method TransactionHelper setAbstractHelper(AbstractHelper $abstractHelper)
 * @method AbstractHelper getAbstractHelper()
 */
class Helper extends AbstractHelper
{
    /** @var array */
    protected $whereFilter;

    /**
     * @param int $parentUserId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getUserConnections($parentUserId)
    {
        $connectionTable = array('c' => 'connection');

        $select = new Select();
        $select->from($connectionTable)
               ->join(array('u' => 'user'), 'c.id_user = u.user_id', array('email'));

        $where = $this->getWhereFilter($parentUserId);
        if (count($where)) {
            $select->where($where);
        }

        //\DEBUG::dump($select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $connectionsTable = $this->getAbstractHelper()->getTable('connection');
        $table = $connectionsTable->getTable();
        $table->setTable($connectionTable);

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select)->buffer();

        return $transactionsResults;
    }

    /**
     * @param int $userId
     *
     * @return array
     */
    protected function getWhereFilter($userId)
    {
        if (null === $this->whereFilter) {
            $where = array();
            $where[] = $this->getWhere()->equalTo('c.id_user', $userId);

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
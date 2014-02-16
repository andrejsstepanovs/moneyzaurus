<?php

namespace Application\Helper;

use Db\AbstractModel;
use Db\ActiveRecord;
use Application\Db\Transaction;
use Application\Db\User;
use Application\Db\Item;
use Application\Db\Group;
use Application\Db\Currency;
use Application\Db\Connection;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql;

/**
 * Class AbstractHelper
 *
 * @package Application\Helper
 */
class AbstractHelper extends AbstractModel
{
    /**
     * @var array
     */
    protected $activeRecords;

    /**
     * @param  string           $table
     * @return \Db\ActiveRecord
     */
    public function getTable($table = null)
    {
        $key = !$table ? 'null' : $table;
        if (!isset($this->activeRecords[$table])) {

            switch ($table) {
                case 'transaction':
                    $activeRecord = new Transaction;
                    break;
                case 'user':
                    $activeRecord = new User;
                    break;
                case 'item':
                    $activeRecord = new Item;
                    break;
                case 'group':
                    $activeRecord = new Group;
                    break;
                case 'currency':
                    $activeRecord = new Currency;
                    break;
                case 'connection':
                    $activeRecord = new Connection;
                    break;
                default:
                    $activeRecord = new ActiveRecord($table);
                    break;
            }

            $this->activeRecords[$key] = $activeRecord;
        }

        return $this->activeRecords[$key];
    }


    /**
     * @param Select $select
     * @param int    $userId
     *
     * @return Select
     */
    public function addTransactionUserFilter(Select $select, $userId)
    {
        $where = new Where();
        $where
            ->equalTo('t.id_user', $userId)
            ->or
            ->equalTo('uc.id_user_parent', $userId)
            ->or
            ->equalTo('uc.id_user', $userId);

        $select->join(
            array('uc' => 'connection'),
            new Expression(
                '(uc.id_user = t.id_user OR t.id_user = uc.id_user_parent) '
                . 'AND uc.state = "' . Connection::STATE_ACCEPTED . '"'
            ),
            array(),
            Select::JOIN_LEFT
        );

        $select->where->addPredicate($where);

        return $select;
    }

}

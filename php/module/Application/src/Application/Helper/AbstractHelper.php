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
use Application\Cache\Manager as CacheManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractHelper
 *
 * @package Application\Helper
 * @method ServiceManager getServiceLocator()
 * @method ServiceManager setServiceLocator(ServiceManager $serviceManager)
 * @method AbstractHelper setUserId(int $userId)
 * @method int            getUserId()
 */
class AbstractHelper extends AbstractModel
{
    /** @var array */
    protected $activeRecords;

    /** @var CacheManager */
    protected $cacheManager;

    /**
     * @param string $table
     *
     * @return \Db\ActiveRecord
     * @throws \InvalidArgumentException
     */
    public function getModel($table)
    {
        $key = $table;
        if (!isset($this->activeRecords[$key])) {

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
                    throw new \InvalidArgumentException('Model with name "' . $table . '" not found');
                    break;
            }

            $activeRecord->getTable()->getTable()->setTable($table);

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

    /**
     * @return CacheManager
     */
    public function getCacheManager()
    {
        if (null === $this->cacheManager) {
            /** @var CacheManager $cacheManager */
            $cacheManager = $this->getServiceLocator()->get('CacheManager');
            $cacheManager->setUserId($this->getUserId());
            $this->cacheManager = $cacheManager;
        }

        return $this->cacheManager;
    }
}

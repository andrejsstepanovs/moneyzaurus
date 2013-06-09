<?php

namespace InstallScripts\Model;

use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;

use Varient\Database\ActiveRecord\ActiveRecord;


class Installer implements AdapterAwareInterface
{
    /** @var \Zend\Db\Adapter\Adapter */
    private $adapter;

    /** @var \Exception */
    private $exceptions = array();


    /** @var array */
    protected $activeRecords;


    /**
     * @param null|\Zend\Db\Adapter\Adapter $adapter
     */
    public function __construct($adapter = null)
    {
        if ($adapter) {
            $this->setDbAdapter($adapter);
        }
    }

    /**
     * Set db adapter
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return AdapterAwareInterface
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    protected function getDbAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param string $sqlQuery
     * @return boolean
     */
    public function executeQuery($sqlQuery)
    {
        try {
            $this->adapter->query($sqlQuery)->execute();
            return true;

        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $exc) {
            $this->setException($exc);
        } catch (Exception $exc) {
            $this->setException($exc);
        }

        return false;
    }

    public function setException($exc)
    {
        $this->exceptions[] = $exc;
        return $this;
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }

    public function special()
    {
        $transactions = new ActiveRecord('transactions', null, 'budget');

        $data = $transactions->getTable()->fetchAll();

        foreach ($data AS $row) {

            if($row->getData('user_id') == 1 || $row->getData('user_id') == 86) {


                $this->saveTransaction(
                        $row['item'],
                        $row['group'],
                        $row['price'],
                        $row['currency'],
                        $row['date_transaction'],
                        $row['date_created'],
                        $row['user_id']
                );

            }
        }

    }



    /**
     * @param string $item
     * @param string $group
     * @param float $price
     * @param string $currency
     * @param date $date
     * @return \Varient\Database\ActiveRecord\ActiveRecord transaction
     */
    protected function saveTransaction(
            $itemName,
            $groupName,
            $price,
            $currencyId,
            $date,
            $date_created,
            $userId
    ) {
        $currency = $this->getTable('currency')
                         ->setId($currencyId)
                         ->load();

        $item = $this->getTable('item');
        try {
            $item->setName($itemName)
                 ->setIdUser($userId)
                 ->load();
        } catch (\Varient\Database\Exception\ModelNotFoundException $exc) {
            $item->save();
        }

        $group = $this->getTable('group');
        try {
            $group->setName($groupName)
                  ->setIdUser($userId)
                  ->load();
        } catch (\Varient\Database\Exception\ModelNotFoundException $exc) {
            $group->save();
        }

        return $this->getTable('transaction')
                    ->setPrice($price)
                    ->setDate($date)
                    ->setDateCreated($date_created)
                    ->setIdUser($userId)
                    ->setIdItem($item->getId())
                    ->setIdGroup($group->getId())
                    ->setIdCurrency($currency->getId())
                    ->save();
    }


    /**
     * @param string $table
     * @return \Varient\Database\ActiveRecord\ActiveRecord
     */
    protected function getTable($table = null)
    {
            return new ActiveRecord($table, null, 'moneyzaurus');
        $key = !$table ? 'null' : $table;
        if (!isset($this->activeRecords[$table])) {
        }

        return $this->activeRecords[$key];
    }
}
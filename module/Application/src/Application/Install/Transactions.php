<?php

namespace Application\Install;

use InstallScripts\Script;
use Db\Db\ActiveRecord;


class Transactions extends Script
{
    protected $activeRecords = array();


    public function __construct()
    {
        set_time_limit(0);
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return array(
            '0.0.1' => 'MoveDatabase',
        );
    }

    /**
     * Move old db values to new db structure
     */
    public function MoveDatabase()
    {
        $transactions = $this->_getTable('transactions', 'budget');

        $data = $transactions->getTable()->fetchAll();


        foreach ($data AS $row) {

            if($row->getData('user_id') == 1 || $row->getData('user_id') == 86) {

                $this->_saveTransaction(
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
     * @return \Db\Db\ActiveRecord transaction
     */
    protected function _saveTransaction(
            $itemName,
            $groupName,
            $price,
            $currencyId,
            $date,
            $date_created,
            $userId
    ) {
        $currency = $this->_getTable('currency')
                         ->setId($currencyId)
                         ->load();

        $item = $this->_getTable('item');
        try {
            $item->setName($itemName)
                 ->setIdUser($userId)
                 ->load();
        } catch (\Db\Db\Exception\ModelNotFoundException $exc) {
            $item->save();
        }

        $group = $this->_getTable('group');
        try {
            $group->setName($groupName)
                  ->setIdUser($userId)
                  ->load();
        } catch (\Db\Db\Exception\ModelNotFoundException $exc) {
            $group->save();
        }

        return $this->_getTable('transaction')
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
     * @return \Db\Db\ActiveRecord
     */
    protected function _getTable($table = null, $schema = 'moneyzaurus', $clear = true)
    {
        $key = !$table ? 'null'.$schema : $table.$schema;
        if (!isset($this->activeRecords[$key])) {
            $this->activeRecords[$key] = new ActiveRecord($table, null, $schema);
        }

        if ($clear) {
            $this->activeRecords[$key]->clear();
        }

        return $this->activeRecords[$key];

    }

}

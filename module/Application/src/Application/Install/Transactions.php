<?php

namespace Application\Install;

use InstallScripts\Bundle\Bundle;
use Varient\Database\ActiveRecord\ActiveRecord;


class Transactions extends Bundle
{
    protected $activeRecords = array();


    /**
     * @return array
     */
    public function getVersions()
    {
        return array(
            '0.0.1' => 'MoveDatabase',
        );
    }

    public function MoveDatabase()
    {
        $transactions = $this->getTable('transactions', 'budget');

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
    protected function getTable($table = null, $schema = 'moneyzaurus')
    {
        $key = !$table ? 'null'.$schema : $table.$schema;
        if (!isset($this->activeRecords[$key])) {
            $this->activeRecords[$key] = new ActiveRecord($table, null, $schema);
        }

        return $this->activeRecords[$key];
    }

}

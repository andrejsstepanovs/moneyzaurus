<?php

namespace Application\Install;

use InstallScripts\Bundle\Bundle;
use Varient\Database\ActiveRecord\ActiveRecord;


class Transactions extends Bundle
{

    /**
     * @return array
     */
    public function getVersions()
    {
        return array(
            '0.0.3' => 'Install',
            '0.0.1' => 'Install',
            '0.1.0' => 'Install',
            '0.0.2' => 'Install',
        );
    }

    public function Install()
    {
        return true;
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

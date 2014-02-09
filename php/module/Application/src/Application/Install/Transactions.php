<?php

namespace Application\Install;

use InstallScripts\Script;
use Db\ActiveRecord;

/**
 * Class Transactions
 *
 * @package Application\Install
 */
class Transactions extends Script
{
    /** @var array */
    protected $activeRecords = array();

    /**
     * init
     */
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
        /** @var \Application\Db\Transaction $transactions */
        $transactions = $this->getTable('transactions');

        $data = $transactions->getTable()->fetchAll();

        /** @var \Application\Db\Transaction $row */
        foreach ($data as $row) {

            if ($row->getData('user_id') == 1 || $row->getData('user_id') == 86 || $row->getData('user_id') == 160) {

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

        return true;
    }

    /**
     * @param string $item
     * @param string $group
     * @param float  $price
     * @param string $currency
     * @param string $date
     *
     * @return \Db\ActiveRecord transaction
     */
    protected function saveTransaction(
        $itemName,
        $groupName,
        $price,
        $currencyId,
        $date,
        $dateCreated,
        $userId
    ) {
        /** @var \Application\Db\Currency $currency */
        $currency = $this->getTable('currency');
        $currency->setCurrencyId($currencyId)->load();

        /** @var \Application\Db\Item $item */
        $item = $this->getTable('item');
        try {
            $item->setName($itemName)
                 ->setIdUser($userId)
                 ->load();
        } catch (\Db\Exception\ModelNotFoundException $exc) {
            $item->save();
        }

        /** @var \Application\Db\Group $group */
        $group = $this->getTable('group');
        try {
            $group->setName($groupName)
                  ->setIdUser($userId)
                  ->load();
        } catch (\Db\Exception\ModelNotFoundException $exc) {
            $group->save();
        }

        /** @var \Application\Db\Transaction $transaction */
        $transaction = $this->getTable('transaction');
        return $transaction
                    ->setPrice($price)
                    ->setDate($date)
                    ->setDateCreated($dateCreated)
                    ->setIdUser($userId)
                    ->setIdItem($item->getId())
                    ->setIdGroup($group->getId())
                    ->setIdCurrency($currency->getId())
                    ->save();
    }

    /**
     * @param  string           $table
     * @return \Db\ActiveRecord
     */
    protected function getTable($table = null, $schema = null, $clear = true)
    {
        $key = !$table ? 'null' . $schema : $table . $schema;
        if (!isset($this->activeRecords[$key])) {
            $this->activeRecords[$key] = new ActiveRecord($table, null, $schema);
        }

        if ($clear) {
            $this->activeRecords[$key]->clear();
        }

        return $this->activeRecords[$key];
    }
}

<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Varient\Database\ActiveRecord\ActiveRecord;
use Application\Exception;

class NewController extends AbstractActionController
{
    /** @var array */
    protected $activeRecords = array();

    /** @var integer */
    protected $userId;


    public function indexAction()
    {

    }

    public function createAction()
    {
        $data = array(
            'item'     => 'Maize',
            'group'    => 'Pārtika',
            'price'    => '0.10',
            'date'     => '2012-01-01',
            'currency' => 'EUR',
        );


        $transaction = $this->saveTransaction(
            $data['item'],
            $data['group'],
            $data['price'],
            $data['currency'],
            $data['date']
        );

        return array(
            'transaction' => $transaction,
        );
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
            $date
    ) {
        $currency = $this->getTable('currency')
                         ->setId($currencyId)
                         ->load();

        $item = $this->getTable('item');
        try {
            $item->setName($itemName)
                 ->setIdUser($this->getUserId())
                 ->load();
        } catch (\Varient\Database\Exception\ModelNotFoundException $exc) {
            $item->save();
        }

        $group = $this->getTable('group');
        try {
            $group->setName($groupName)
                  ->setIdUser($this->getUserId())
                  ->load();
        } catch (\Varient\Database\Exception\ModelNotFoundException $exc) {
            $group->save();
        }

        return $this->getTable('transaction')
                    ->setPrice($price)
                    ->setDate($date)
                    ->setIdUser($this->getUserId())
                    ->setIdItem($item->getId())
                    ->setIdGroup($group->getId())
                    ->setIdCurrency($currency->getId())
                    ->save();
    }

    /**
     * @param string $table
     * @return \Varient\Database\ActiveRecord\ActiveRecord
     */
    protected function getTable($table)
    {
        if (!isset($this->activeRecords[$table])) {
            $this->activeRecords[$table] = new ActiveRecord($table);
        }
        return $this->activeRecords[$table];
    }

    /**
     * @return integer
     */
    protected function getUserId()
    {
        if (null === $this->userId) {

            $this->userId = 1;
//            $this->userId = $this->zfcUserAuthentication()
//                                 ->getIdentity()
//                                 ->getId();

            if (empty($this->userId)) {
                throw new Exception\UserNotFoundException(
                    'User not found'
                );
            }
        }
        return $this->userId;
    }

}

<?php

namespace Application\Helper\Transaction;

use Application\Helper\AbstractHelper;
use \Db\Exception\ModelNotFoundException;

/**
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @method \Application\Helper\Transaction\Helper setParams(\Zend\Mvc\Controller\Plugin\Params $params)
 * @method \Application\Helper\Transaction\Helper setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
 * @method \Zend\Mvc\Controller\Plugin\Params getParams()
 */
class Helper extends AbstractHelper
{
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
        return \Zend\Db\Sql\Select::ORDER_DESCENDING;
    }

    /**
     * @param  int              $transactionId
     * @param  string           $item
     * @param  string           $group
     * @param  float            $price
     * @param  string           $currency
     * @param  string           $date
     *
     * @return \Db\ActiveRecord transaction
     */
    public function saveTransaction(
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
        $currency = $this->getTable('currency')
                         ->setId($currencyId)
                         ->load();

        /** @var \Application\Db\Item $item */
        $item = $this->getTable('item');
        try {
            $item->setName($itemName)
                 ->setIdUser($this->getUserId())
                 ->load();
        } catch (ModelNotFoundException $exc) {
            $item->save();
        }

        /** @var \Application\Db\Group $group */
        $group = $this->getTable('group');
        try {
            $group->setName($groupName)
                  ->setIdUser($this->getUserId())
                  ->load();
        } catch (ModelNotFoundException $exc) {
            $group->save();
        }

        /** @var \Application\Db\Transaction $transaction */
        $transaction = $this->getTable('transaction');
        return $transaction
            ->setTransactionId($transactionId)
            ->setPrice($price)
            ->setDate($date)
            ->setIdUser($this->getUserId())
            ->setIdItem($item->getId())
            ->setIdGroup($group->getId())
            ->setIdCurrency($currency->getId())
            ->save();
    }
}

<?php

namespace Application\Helper\Transaction;

use Application\Helper\AbstractHelper;
use \Db\Exception\ModelNotFoundException;

/**
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @method \Application\Helper\Transaction\Helper setParams(\Zend\Mvc\Controller\Plugin\Params $params)
 * @method \Application\Helper\Transaction\Helper setAbstractHelper(\Application\Helper\AbstractHelper $abstractHelper)
 * @method \Zend\Mvc\Controller\Plugin\Params getParams()
 * @method \Application\Helper\AbstractHelper getAbstractHelper()
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
     * @param  int    $userId
     * @param  int    $transactionId
     * @param  string $item
     * @param  string $group
     * @param  float  $price
     * @param  string $currency
     * @param  string $date
     *
     * @return \Application\Db\Transaction transaction
     */
    public function saveTransaction(
        $userId,
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
        $currency = $this->getAbstractHelper()->getTable('currency')
                         ->setId($currencyId)
                         ->load();

        /** @var \Application\Db\Item $item */
        $item = $this->getAbstractHelper()->getTable('item');
        try {
            $item->setName($itemName)
                 ->setIdUser($userId)
                 ->load();
        } catch (ModelNotFoundException $exc) {
            $item->save();
        }

        /** @var \Application\Db\Group $group */
        $group = $this->getAbstractHelper()->getTable('group');
        try {
            $group->setName($groupName)
                  ->setIdUser($userId)
                  ->load();
        } catch (ModelNotFoundException $exc) {
            $group->save();
        }

        /** @var \Application\Db\Transaction $transaction */
        $transaction = $this->getAbstractHelper()->getTable('transaction');
        return $transaction
            ->setTransactionId($transactionId)
            ->setPrice($price)
            ->setDate($date)
            ->setIdUser($userId)
            ->setIdItem($item->getId())
            ->setIdGroup($group->getId())
            ->setIdCurrency($currency->getId())
            ->save();
    }
}
